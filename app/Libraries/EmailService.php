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
        $this->config = config('Email');
        $this->queueModel = new EmailQueueModel();
        // Tidak perlu inisialisasi PHPMailer di sini.
        // EmailService hanya menulis ke email_queue (DB).
        // PHPMailer digunakan oleh EmailQueueProcessor saat benar-benar mengirim.
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
            'info' => ['bg' => '#5f2eea', 'border' => '#5f2eea'],
            'success' => ['bg' => '#10b981', 'border' => '#10b981'],
            'warning' => ['bg' => '#f59e0b', 'border' => '#f59e0b'],
            'danger' => ['bg' => '#ef4444', 'border' => '#ef4444'],
            'new_letter' => ['bg' => '#3b82f6', 'border' => '#3b82f6'],
            'letter_read' => ['bg' => '#06b6d4', 'border' => '#06b6d4'],
            'letter_replied' => ['bg' => '#10b981', 'border' => '#10b981'],
        ];
        
        $color = $colors[$type] ?? $colors['info'];
        $headerImageUrl = 'https://image2url.com/r2/bucket3/images/1767877728084-f96e49d3-6eda-406d-875b-ce1d2ce76a27.png';
        $nameEscaped = htmlspecialchars($name);
        $titleEscaped = htmlspecialchars($title);
        $messageEscaped = htmlspecialchars($message);
        
        // Build letter info section
        $letterInfo = '';
        if ($letterTitle || $letterType) {
            $letterTitleEscaped = htmlspecialchars($letterTitle);
            $letterTypeEscaped = htmlspecialchars($letterType);
            $letterInfo = "<tr><td style='padding:0 24px 16px 24px'><div style='background:#f9fafb;border-left:4px solid {$color['border']};border-radius:8px;padding:16px'><h3 style='margin:0 0 12px 0;font-size:16px;font-weight:600;color:{$color['bg']}'>Detail Surat</h3>";
            if ($letterTitle) {
                $letterInfo .= "<p style='margin:8px 0;font-size:14px;color:#4b5563'><strong>Judul Surat:</strong> {$letterTitleEscaped}</p>";
            }
            if ($letterType) {
                $letterInfo .= "<p style='margin:8px 0;font-size:14px;color:#4b5563'><strong>Tipe Surat:</strong> {$letterTypeEscaped}</p>";
            }
            $letterInfo .= "</div></td></tr>";
        }
        
        // Build action button
        $actionButton = '';
        if ($actionUrl) {
            $actionUrlEscaped = htmlspecialchars($actionUrl);
            $actionButton = "<tr><td style='padding:0 24px 16px 24px;text-align:center'><a href='{$actionUrlEscaped}' style='display:inline-block;background:{$color['bg']};color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:600;font-size:14px'>Lihat Detail</a></td></tr>";
        }
        
        return "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta name='viewport' content='width=device-width,initial-scale=1.0'><link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet'></head><body style='margin:0;padding:0;background:#f5f7fb;font-family:Poppins,Arial,sans-serif;color:#1f2933'><table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' width='100%' style='background:#f5f7fb;padding:24px 0'><tr><td align='center'><table role='presentation' cellspacing='0' cellpadding='0' border='0' width='560' style='background:#ffffff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.05);border:1px solid #e5e7eb;overflow:hidden'><tr><td style='padding:24px 24px 8px 24px;text-align:center'><img src='{$headerImageUrl}' alt='Desa Padang Loang' style='max-width:100%;height:auto;display:block;margin:0 auto 12px auto'></td></tr><tr><td style='padding:0 24px 8px 24px'><h1 style='margin:0;font-size:22px;font-weight:600;color:#111827'>Halo {$nameEscaped},</h1></td></tr><tr><td style='padding:0 24px 16px 24px'><div style='background:#f9fafb;border-left:4px solid {$color['border']};border-radius:8px;padding:16px'><h2 style='margin:0 0 8px 0;font-size:18px;font-weight:600;color:{$color['bg']}'>{$titleEscaped}</h2><p style='margin:0;font-size:14px;line-height:1.6;color:#4b5563'>{$messageEscaped}</p></div></td></tr>{$letterInfo}{$actionButton}<tr><td style='padding:14px 24px 18px 24px;font-size:12px;line-height:1.6;color:#6b7280;text-align:center'>Email ini dikirim secara otomatis dari sistem Website Padang Loang.</td></tr><tr><td style='padding:0 24px 24px 24px;font-size:13px;color:#9ca3af;text-align:center'>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</td></tr></table></td></tr></table></body></html>";
    }
}

