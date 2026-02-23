<?php

namespace App\Libraries;

use App\Models\EmailQueueModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Config\Email as EmailConfig;

class EmailQueueProcessor
{
    protected $config;
    protected $mailer;
    protected $queueModel;
    protected $purgeAfterSeconds = 3600; // 1 jam

    public function __construct()
    {
        $this->ensureAutoload();
        $this->config = config('Email');
        $this->queueModel = new EmailQueueModel();
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $this->configure();
    }

    protected function ensureAutoload(): void
    {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $autoloadPaths = [
                defined('COMPOSER_PATH') && file_exists(COMPOSER_PATH) ? COMPOSER_PATH : null,
                defined('ROOTPATH') && file_exists(ROOTPATH . 'vendor/autoload.php') ? ROOTPATH . 'vendor/autoload.php' : null,
                __DIR__ . '/../../vendor/autoload.php',
            ];
            
            foreach ($autoloadPaths as $path) {
                if ($path && file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
            
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $phpmailerPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
                if (file_exists($phpmailerPath)) {
                    require_once $phpmailerPath;
                    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
                    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
                }
            }
        }
    }

    protected function configure()
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config->SMTPHost;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config->SMTPUser;
            $this->mailer->Password   = $this->config->SMTPPass;
            $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = $this->config->SMTPPort;
            $this->mailer->CharSet    = $this->config->charset;
            $this->mailer->setFrom($this->config->fromEmail, $this->config->fromName);
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            log_message('error', 'EmailQueueProcessor configuration error: ' . $this->mailer->ErrorInfo);
        }
    }

    /**
     * Memproses email queue
     * Ambil 10 email pending, kirim, update status
     */
    public function process(int $limit = 10): int
    {
        $processed = 0;
        
        // Re-claim email yang stuck (processing lebih dari 10 menit)
        $this->reclaimStuckEmails();
        
        // Ambil email pending
        $emails = $this->queueModel
            ->where('is_sent', EmailQueueModel::STATUS_PENDING)
            ->where('fail_count <', 5)
            ->orderBy('created_at', 'ASC')
            ->findAll($limit);
        
        if (empty($emails)) {
            return 0;
        }
        
        log_message('info', "EmailQueueProcessor: Found " . count($emails) . " pending emails to process");
        
        foreach ($emails as $email) {
            $token = bin2hex(random_bytes(16));
            
            // Update status menjadi processing
            $this->queueModel->update($email['id'], [
                'is_sent'         => EmailQueueModel::STATUS_PROCESSING,
                'processing_token' => $token,
                'processing_at'    => date('Y-m-d H:i:s'),
            ]);
            
            try {
                // Kirim email
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($email['recipient']);
                $this->mailer->isHTML(true);
                $this->mailer->Subject = $email['subject'];
                $this->mailer->Body = $email['body'];
                $this->mailer->AltBody = strip_tags($email['body']);
                
                $this->mailer->send();
                
                // Update status menjadi sent
                $this->queueModel->update($email['id'], [
                    'is_sent'         => EmailQueueModel::STATUS_SENT,
                    'processing_token' => null,
                    'sent_at'         => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
                
                $processed++;
                log_message('info', "Email queue processed: {$email['recipient']} - {$email['subject']}");
                
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                // Update fail_count dan last_error
                $failCount = (int) $email['fail_count'] + 1;
                $this->queueModel->update($email['id'], [
                    'is_sent'         => EmailQueueModel::STATUS_PENDING,
                    'processing_token' => null,
                    'processing_at'    => null,
                    'fail_count'       => $failCount,
                    'last_error'       => $this->mailer->ErrorInfo,
                    'updated_at'       => date('Y-m-d H:i:s'),
                ]);
                
                log_message('error', "Email queue failed: {$email['recipient']} - {$this->mailer->ErrorInfo}");
            }
        }
        
        // Auto delete email yang sudah terkirim lebih dari 1 jam
        $this->purgeOldSentEmails();
        
        return $processed;
    }

    /**
     * Re-claim email yang stuck (processing lebih dari 10 menit)
     */
    protected function reclaimStuckEmails(): void
    {
        $tenMinutesAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));
        
        $stuckEmails = $this->queueModel
            ->where('is_sent', EmailQueueModel::STATUS_PROCESSING)
            ->where('processing_at <', $tenMinutesAgo)
            ->findAll();
        
        foreach ($stuckEmails as $email) {
            $this->queueModel->update($email['id'], [
                'is_sent'         => EmailQueueModel::STATUS_PENDING,
                'processing_token' => null,
                'processing_at'    => null,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);
            
            log_message('info', "Reclaimed stuck email: ID {$email['id']}");
        }
    }

    /**
     * Hapus email yang sudah terkirim lebih dari 1 jam
     */
    protected function purgeOldSentEmails(): void
    {
        $purgeTime = date('Y-m-d H:i:s', time() - $this->purgeAfterSeconds);
        
        $this->queueModel
            ->where('is_sent', EmailQueueModel::STATUS_SENT)
            ->where('sent_at <', $purgeTime)
            ->delete();
    }
}

