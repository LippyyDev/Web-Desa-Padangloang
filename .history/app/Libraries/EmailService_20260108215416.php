<?php

namespace App\Libraries;

use App\Models\EmailQueueModel;
use Config\Email as EmailConfig;

class EmailService
{
    protected $config;
    protected $mailer;
    protected $queueModel;

    public function __construct()
    {
        // Pastikan autoload dimuat sebelum menggunakan PHPMailer
        $this->ensureAutoload();
        
        $this->config = config('Email');
        $this->queueModel = new EmailQueueModel();
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $this->configure();
    }
    
    /**
     * Menambahkan email ke queue
     */
    protected function queueEmail(string $toEmail, string $subject, string $body): bool
    {
        try {
            $this->queueModel->insert([
                'recipient'   => $toEmail,
                'subject'     => $subject,
                'body'        => $body,
                'is_sent'     => EmailQueueModel::STATUS_PENDING,
                'fail_count' => 0,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            
            log_message('info', "Email queued: {$toEmail} - {$subject}");
            return true;
        } catch (\Exception $e) {
            log_message('error', "Failed to queue email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Memastikan vendor autoload dimuat
     */
    protected function ensureAutoload(): void
    {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Coba beberapa path yang mungkin
            $autoloadPaths = [
                defined('COMPOSER_PATH') && file_exists(COMPOSER_PATH) ? COMPOSER_PATH : null,
                defined('ROOTPATH') && file_exists(ROOTPATH . 'vendor/autoload.php') ? ROOTPATH . 'vendor/autoload.php' : null,
                __DIR__ . '/../../vendor/autoload.php',  // Relatif dari Libraries
            ];
            
            foreach ($autoloadPaths as $path) {
                if ($path && file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
            
            // Jika masih belum ter-load, coba load langsung dari vendor
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
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config->SMTPHost;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config->SMTPUser;
            $this->mailer->Password   = $this->config->SMTPPass;
            $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = $this->config->SMTPPort;
            $this->mailer->CharSet    = $this->config->charset;

            // Sender
            $this->mailer->setFrom($this->config->fromEmail, $this->config->fromName);
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            log_message('error', 'EmailService configuration error: ' . $this->mailer->ErrorInfo);
        }
    }

    /**
     * Mengirim email OTP dan link untuk verifikasi registrasi
     */
    public function sendOtpRegister(string $toEmail, string $toName, string $otp, string $verificationLink): bool
    {
        $subject = 'Verifikasi Akun - Kode OTP';
        $body = $this->getOtpRegisterTemplate($toName, $otp, $verificationLink);
        
        // Tambahkan ke queue
        return $this->queueEmail($toEmail, $subject, $body);
    }

    /**
     * Mengirim email OTP untuk reset password
     */
    public function sendOtpReset(string $toEmail, string $toName, string $otp): bool
    {
        $subject = 'Reset Password - Kode OTP';
        $body = $this->getOtpResetTemplate($toName, $otp);
        
        // Tambahkan ke queue
        return $this->queueEmail($toEmail, $subject, $body);
    }

    /**
     * Mengirim email notifikasi
     */
    public function sendNotification(string $toEmail, string $toName, string $title, string $message, string $type = 'info', string $actionUrl = null, string $letterTitle = null, string $letterType = null): bool
    {
        $subject = 'Notifikasi - ' . $title;
        $body = $this->getNotificationTemplate($toName, $title, $message, $type, $actionUrl, $letterTitle, $letterType);
        
        // Tambahkan ke queue
        return $this->queueEmail($toEmail, $subject, $body);
    }

    /**
     * Template HTML untuk email OTP Register dengan link verifikasi
     */
    protected function getOtpRegisterTemplate(string $name, string $otp, string $verificationLink): string
    {
        $headerImageUrl = 'https://image2url.com/r2/bucket3/images/1767877728084-f96e49d3-6eda-406d-875b-ce1d2ce76a27.png';
        $nameEscaped = htmlspecialchars($name);
        $otpEscaped = htmlspecialchars($otp);
        $linkEscaped = htmlspecialchars($verificationLink);
        
        return "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta name='viewport' content='width=device-width,initial-scale=1.0'><link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet'></head><body style='margin:0;padding:0;background:#f5f7fb;font-family:Poppins,Arial,sans-serif;color:#1f2933'><table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' width='100%' style='background:#f5f7fb;padding:24px 0'><tr><td align='center'><table role='presentation' cellspacing='0' cellpadding='0' border='0' width='560' style='background:#ffffff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.05);border:1px solid #e5e7eb;overflow:hidden'><tr><td style='padding:24px 24px 8px 24px;text-align:center'><img src='{$headerImageUrl}' alt='Desa Padang Loang' style='max-width:100%;height:auto;display:block;margin:0 auto 12px auto'></td></tr><tr><td style='padding:0 24px 8px 24px'><h1 style='margin:0;font-size:22px;font-weight:600;color:#111827'>Halo {$nameEscaped},</h1></td></tr><tr><td style='padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:#4b5563'>Terima kasih telah mendaftar di Website Padang Loang. Untuk menyelesaikan proses registrasi, silakan gunakan kode OTP berikut:</td></tr><tr><td style='padding:0 24px 16px 24px;text-align:center'><div style='display:inline-block;background:#f3f4f6;border:1px dashed #cbd5e1;border-radius:10px;padding:18px 24px;font-size:24px;letter-spacing:6px;font-weight:600;color:#111827'>{$otpEscaped}</div></td></tr><tr><td style='padding:0 24px 16px 24px;text-align:center'><a href='{$linkEscaped}' style='display:inline-block;background:#5f2eea;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:600;font-size:14px'>Verifikasi Akun</a><div style='margin-top:12px;font-size:13px;color:#6b7280;word-break:break-all'>Atau salin tautan ini:<br>{$linkEscaped}</div></td></tr><tr><td style='padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:#4b5563;text-align:center'>Kode OTP dan link verifikasi berlaku selama <strong>1 jam</strong>. Jangan bagikan kode atau link ini kepada siapa pun.</td></tr><tr><td style='padding:14px 24px 18px 24px;font-size:12px;line-height:1.6;color:#6b7280;text-align:center'>Jika Anda tidak melakukan registrasi, abaikan email ini dengan aman.</td></tr><tr><td style='padding:0 24px 24px 24px;font-size:13px;color:#9ca3af;text-align:center'>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</td></tr></table></td></tr></table></body></html>";
    }

    /**
     * Template HTML untuk email OTP Reset Password
     */
    protected function getOtpResetTemplate(string $name, string $otp): string
    {
        $headerImageUrl = 'https://image2url.com/r2/bucket3/images/1767877728084-f96e49d3-6eda-406d-875b-ce1d2ce76a27.png';
        $nameEscaped = htmlspecialchars($name);
        $otpEscaped = htmlspecialchars($otp);
        
        return "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta name='viewport' content='width=device-width,initial-scale=1.0'><link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet'></head><body style='margin:0;padding:0;background:#f5f7fb;font-family:Poppins,Arial,sans-serif;color:#1f2933'><table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' width='100%' style='background:#f5f7fb;padding:24px 0'><tr><td align='center'><table role='presentation' cellspacing='0' cellpadding='0' border='0' width='560' style='background:#ffffff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.05);border:1px solid #e5e7eb;overflow:hidden'><tr><td style='padding:24px 24px 8px 24px;text-align:center'><img src='{$headerImageUrl}' alt='Desa Padang Loang' style='max-width:100%;height:auto;display:block;margin:0 auto 12px auto'></td></tr><tr><td style='padding:0 24px 8px 24px'><h1 style='margin:0;font-size:22px;font-weight:600;color:#111827'>Halo {$nameEscaped},</h1></td></tr><tr><td style='padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:#4b5563'>Kami menerima permintaan untuk mengatur ulang password akun Anda. Gunakan kode berikut untuk melanjutkan.</td></tr><tr><td style='padding:0 24px 16px 24px;text-align:center'><div style='display:inline-block;background:#f3f4f6;border:1px dashed #cbd5e1;border-radius:10px;padding:18px 24px;font-size:24px;letter-spacing:6px;font-weight:600;color:#111827'>{$otpEscaped}</div></td></tr><tr><td style='padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:#4b5563;text-align:center'>Kode OTP berlaku selama <strong>1 jam</strong>. Jangan bagikan kode ini kepada siapa pun.</td></tr><tr><td style='padding:14px 24px 18px 24px;font-size:12px;line-height:1.6;color:#6b7280;text-align:center'>Jika Anda tidak meminta reset password, abaikan email ini dengan aman dan password Anda tidak akan berubah.</td></tr><tr><td style='padding:0 24px 24px 24px;font-size:13px;color:#9ca3af;text-align:center'>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</td></tr></table></td></tr></table></body></html>";
    }

    /**
     * Template HTML untuk email notifikasi
     */
    protected function getNotificationTemplate(string $name, string $title, string $message, string $type = 'info', string $actionUrl = null, string $letterTitle = null, string $letterType = null): string
    {
        // Tentukan warna berdasarkan type
        $colors = [
            'info' => ['bg' => '#17a2b8', 'border' => '#17a2b8'],
            'success' => ['bg' => '#28a745', 'border' => '#28a745'],
            'warning' => ['bg' => '#ffc107', 'border' => '#ffc107'],
            'danger' => ['bg' => '#dc3545', 'border' => '#dc3545'],
            'new_letter' => ['bg' => '#007bff', 'border' => '#007bff'],
            'letter_read' => ['bg' => '#17a2b8', 'border' => '#17a2b8'],
            'letter_replied' => ['bg' => '#28a745', 'border' => '#28a745'],
        ];
        
        $color = $colors[$type] ?? $colors['info'];
        $headerImageUrl = 'https://image2url.com/r2/bucket3/images/1767877728084-f96e49d3-6eda-406d-875b-ce1d2ce76a27.png';
        
        // Build letter info section
        $letterInfo = '';
        if ($letterTitle || $letterType) {
            $letterInfo = "<table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: white; border: 1px solid #dee2e6; margin: 20px 0;'><tr><td style='padding: 15px;'><h4 style='margin: 0 0 15px 0; color: {$color['bg']}; font-size: 16px;'>Detail Surat</h4>";
            if ($letterTitle) {
                $letterInfo .= "<p style='margin: 8px 0;'><strong>Judul Surat:</strong> " . htmlspecialchars($letterTitle) . "</p>";
            }
            if ($letterType) {
                $letterInfo .= "<p style='margin: 8px 0;'><strong>Tipe Surat:</strong> " . htmlspecialchars($letterType) . "</p>";
            }
            $letterInfo .= "</td></tr></table>";
        }
        
        // Build action button
        $actionButton = '';
        if ($actionUrl) {
            $actionButton = "<table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='margin: 30px 0 20px 0;'><tr><td align='center'><a href='" . htmlspecialchars($actionUrl) . "' style='display: inline-block; background-color: {$color['bg']}; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Lihat Detail</a></td></tr></table>";
        }
        
        // Compact HTML - single line untuk menghindari clipping
        return "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'></head><body style='margin:0;padding:0;font-family:Arial,sans-serif;line-height:1.6;color:#333;background-color:#f4f4f4'><table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color:#f4f4f4'><tr><td align='center' style='padding:20px 0'><table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' style='max-width:600px;background-color:white;border-radius:5px;overflow:hidden'><tr><td style='background-color:{$color['bg']};padding:0;text-align:center'><img src='{$headerImageUrl}' alt='Desa Padang Loang' width='600' style='width:100%;max-width:600px;height:auto;display:block;border:0;outline:none;text-decoration:none'></td></tr><tr><td style='background-color:#f8f9fa;padding:30px'><p style='margin:0 0 15px 0'>Halo <strong>" . htmlspecialchars($name) . "</strong>,</p><table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color:white;border-left:4px solid {$color['border']};margin:20px 0'><tr><td style='padding:20px'><h3 style='margin:0 0 10px 0;color:{$color['bg']};font-size:18px;font-weight:bold'>" . htmlspecialchars($title) . "</h3><p style='margin:0'>" . htmlspecialchars($message) . "</p></td></tr></table>{$letterInfo}{$actionButton}<p style='margin-top:20px;color:#666;font-size:14px'>Email ini dikirim secara otomatis dari sistem Website Padang Loang.</p></td></tr><tr><td style='text-align:center;padding:20px;color:#666;font-size:12px;background-color:#f8f9fa'><p style='margin:0 0 5px 0'>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p><p style='margin:0'>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</p></td></tr></table></td></tr></table></body></html>";
    }
}

