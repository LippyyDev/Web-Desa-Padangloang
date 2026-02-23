<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\EmailQueueProcessor;

class EmailQueuePumpFilter implements FilterInterface
{
    protected $throttleSeconds = 1; // Throttle 1 detik (sangat cepat)
    protected $cacheKey = 'email_queue_last_process';

    public function before(RequestInterface $request, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa di before request
    }
    
    /**
     * Fallback: simpan last process time ke file jika cache gagal
     */
    protected function saveLastProcessTime(): void
    {
        $file = WRITEPATH . 'cache/email_queue_last_process.txt';
        @file_put_contents($file, time());
    }
    
    /**
     * Fallback: baca last process time dari file jika cache gagal
     */
    protected function getLastProcessTimeFromFile(): ?int
    {
        $file = WRITEPATH . 'cache/email_queue_last_process.txt';
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            return $content ? (int) $content : null;
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Skip untuk CLI requests
        if (is_cli()) {
            return;
        }

        // Skip untuk request tertentu yang tidak perlu (assets, dll)
        $uri = $request->getUri()->getPath();
        if (strpos($uri, '/assets/') === 0 || strpos($uri, '/vendor/') === 0 || strpos($uri, '/favicon.ico') === 0) {
            return;
        }

        // Skip AJAX/XHR requests (DataTables, fetch, dll)
        // Agar DataTables tidak ikut memblokir karena SMTP processing
        if (strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
            return;
        }

        // Skip GET requests — hanya pump email saat POST (kirim surat, balas, dll)
        if (strtoupper($request->getMethod()) === 'GET') {
            return;
        }

        // Proses email di background menggunakan register_shutdown_function
        // Ini memastikan response dikirim dulu sebelum memproses email
        register_shutdown_function(function() {
            // Abaikan jika user disconnect
            ignore_user_abort(true);
            set_time_limit(30); // Set timeout 30 detik untuk proses email
            
            // Kirim response dulu (non-blocking) jika FastCGI tersedia
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            // Cek apakah sudah waktunya untuk memproses queue
            try {
                // Gunakan file sebagai fallback jika cache tidak tersedia
                $lastProcess = null;
                try {
                    $cache = \Config\Services::cache();
                    $lastProcess = $cache->get($this->cacheKey);
                } catch (\Exception $e) {
                    // Jika cache gagal, gunakan file
                    $lastProcess = $this->getLastProcessTimeFromFile();
                }
                
                $shouldProcess = false;
                if ($lastProcess === null) {
                    $shouldProcess = true;
                } else {
                    $timeDiff = time() - $lastProcess;
                    if ($timeDiff >= $this->throttleSeconds) {
                        $shouldProcess = true;
                    }
                }

                if ($shouldProcess) {
                    $processor = new EmailQueueProcessor();
                    $processed = $processor->process(10);
                    
                    if ($processed > 0) {
                        log_message('info', "EmailQueuePumpFilter: Processed {$processed} emails");
                    }
                    
                    // Update last process time
                    $currentTime = time();
                    try {
                        $cache = \Config\Services::cache();
                        $cache->save($this->cacheKey, $currentTime, $this->throttleSeconds * 2);
                    } catch (\Exception $e) {
                        // Jika cache gagal, gunakan file sebagai fallback
                        $this->saveLastProcessTime();
                    }
                } else {
                    // Cek apakah ada email pending yang sudah lama, jika ada paksa proses
                    $queueModel = new \App\Models\EmailQueueModel();
                    $pendingCount = $queueModel->where('is_sent', \App\Models\EmailQueueModel::STATUS_PENDING)->countAllResults();
                    
                    if ($pendingCount > 0) {
                        // Jika ada email pending lebih dari 2 detik, paksa proses
                        $oldestPending = $queueModel
                            ->where('is_sent', \App\Models\EmailQueueModel::STATUS_PENDING)
                            ->orderBy('created_at', 'ASC')
                            ->first();
                        
                        if ($oldestPending) {
                            $createdTime = strtotime($oldestPending['created_at']);
                            $age = time() - $createdTime;
                            
                            if ($age >= 2) {
                                log_message('info', "EmailQueuePumpFilter: Force processing old pending email (age: {$age}s)");
                                $processor = new EmailQueueProcessor();
                                $processed = $processor->process(1);
                                
                                if ($processed > 0) {
                                    log_message('info', "EmailQueuePumpFilter: Force processed {$processed} emails");
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'EmailQueuePumpFilter error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            }
        });
    }
}

