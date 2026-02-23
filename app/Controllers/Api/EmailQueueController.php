<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\EmailQueueProcessor;

class EmailQueueController extends BaseController
{
    /**
     * Endpoint untuk memproses email queue
     * Dipanggil via AJAX atau cron job
     */
    public function process()
    {
        // Skip untuk non-AJAX request (kecuali dari cron/CLI)
        if (!$this->request->isAJAX() && !is_cli()) {
            // Untuk keamanan, bisa tambahkan token check di sini
            // return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        try {
            $processor = new EmailQueueProcessor();
            $processed = $processor->process(10);
            
            return $this->response->setJSON([
                'success' => true,
                'processed' => $processed,
                'message' => "Processed {$processed} emails"
            ]);
        } catch (\Exception $e) {
            log_message('error', 'EmailQueueController error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}

