<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\EmailQueueProcessor;

class EmailQueueController extends BaseController
{
    /**
     * Endpoint untuk memproses email queue
     * Dipanggil via AJAX, cron job, atau CLI
     * 
     * Kompatibel dengan:
     * - PHP-FPM (fastcgi_finish_request)
     * - LiteSpeed (litespeed_finish_request) — umum di shared hosting Indonesia
     * - Apache mod_php (Connection: close fallback)
     */
    public function process()
    {
        // Skip untuk non-AJAX request (kecuali dari cron/CLI)
        if (!$this->request->isAJAX() && !is_cli()) {
            // Untuk keamanan, bisa tambahkan token check di sini
            // return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        // Tutup session agar tidak memblokir request lain dari user yang sama
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        
        // Siapkan response JSON
        $responseBody = json_encode(['success' => true, 'message' => 'Queue processing started']);
        
        // Kirim response ke browser dan putus koneksi
        $this->finishBrowserConnection($responseBody);
        
        // Lanjutkan proses email di background (browser sudah tidak menunggu)
        @ignore_user_abort(true);
        @set_time_limit(120);
        
        try {
            $processor = new EmailQueueProcessor();
            $processor->process(5);
        } catch (\Exception $e) {
            log_message('error', 'EmailQueueController background error: ' . $e->getMessage());
        }
        
        exit;
    }
    
    /**
     * Putus koneksi ke browser setelah mengirim response.
     * Kompatibel dengan PHP-FPM, LiteSpeed, dan Apache mod_php.
     */
    private function finishBrowserConnection(string $responseBody): void
    {
        // Set headers
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            header('Connection: close');
            header('Content-Length: ' . strlen($responseBody));
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }
        
        // Flush semua output buffer dari CodeIgniter (bisa multi-level)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Kirim response body
        echo $responseBody;
        flush();
        
        // Putus koneksi berdasarkan environment server
        if (function_exists('fastcgi_finish_request')) {
            // PHP-FPM (Nginx + PHP-FPM)
            fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            // LiteSpeed — umum di shared hosting (Niagahoster, Hostinger, dll)
            litespeed_finish_request();
        }
        // Untuk Apache mod_php, header Connection: close + Content-Length 
        // sudah cukup untuk menutup koneksi browser
    }
}
