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

    protected function getOtpResetTemplate(string $name, string $otp): string
    {
        $headerImageUrl = 'https://image2url.com/r2/bucket3/images/1767877728084-f96e49d3-6eda-406d-875b-ce1d2ce76a27.png';
        $nameEscaped = htmlspecialchars($name);
        $otpEscaped = htmlspecialchars($otp);
        $textColor = '#374151';
        $headingColor = '#111827';
        
        return "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta name='viewport' content='width=device-width,initial-scale=1.0'><link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap' rel='stylesheet'></head><body style='margin:0;padding:0;background:#f9fafb;font-family:Inter,Arial,sans-serif;color:{$textColor}'><table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' width='100%' style='background:#f9fafb;padding:32px 0'><tr><td align='center'><table role='presentation' cellspacing='0' cellpadding='0' border='0' width='520' style='background:#ffffff;border-radius:8px;border:1px solid #e5e7eb;overflow:hidden'><tr><td style='padding:32px 24px 16px 24px;text-align:center'><img src='{$headerImageUrl}' alt='Desa Padang Loang' style='max-width:160px;height:auto;display:block;margin:0 auto'></td></tr><tr><td style='padding:0 24px 16px 24px'><h1 style='margin:0;font-size:18px;font-weight:600;color:{$headingColor}'>Hai {$nameEscaped}, semoga harimu menyenangkan! ✨</h1></td></tr><tr><td style='padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:{$textColor}'>Kami menerima permintaan untuk mengatur ulang password akun Anda. Gunakan kode berikut untuk melanjutkan.</td></tr><tr><td style='padding:0 24px 16px 24px;text-align:center'><div style='display:inline-block;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:16px 24px;font-size:24px;letter-spacing:6px;font-weight:600;color:{$headingColor}'>{$otpEscaped}</div></td></tr><tr><td style='padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:{$textColor};text-align:center'>Kode OTP berlaku selama <strong>1 jam</strong>. Jangan bagikan kode ini kepada siapa pun.</td></tr><tr><td style='padding:16px 24px 24px 24px;font-size:12px;line-height:1.5;color:#6b7280;text-align:center;border-top:1px solid #f3f4f6'>Jika Anda tidak meminta reset password, abaikan email ini dengan aman.<br>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</td></tr></table></td></tr></table></body></html>";
    }

    protected function getNotificationTemplate(string $name, string $title, string $message, string $type = 'info', string $actionUrl = null, string $letterTitle = null, string $letterType = null): string
    {
        $messageEscaped = $message; // Assumes caller provides safe HTML
        $titleEscaped = htmlspecialchars($title);
        $nameEscaped = htmlspecialchars($name);
        
        $greetings = [
            'new_letter'      => "Halo {$nameEscaped}, ada warga yang mengajukan surat baru nih!",
            'letter_read'     => "Halo {$nameEscaped}, surat kamu sudah dibaca dan sedang diproses!",
            'letter_accepted' => "Kabar gembira {$nameEscaped}, pengajuan surat kamu sudah disetujui!",
            'letter_rejected' => "Mohon maaf {$nameEscaped}, pengajuan surat kamu terpaksa kami tolak.",
            'letter_replied'  => "Hai {$nameEscaped}, ada balasan baru dari admin/staf nih!",
            'reply'           => "Hai {$nameEscaped}, ada pesan balasan baru di surat kamu!"
        ];
        $greetingText = $greetings[$type] ?? "Hai {$nameEscaped}, semoga kabarmu selalu baik!";
        
        $headerImageUrl = 'https://image2url.com/r2/bucket3/images/1767877728084-f96e49d3-6eda-406d-875b-ce1d2ce76a27.png';
        
        $primaryColor = '#2563eb';
        $textColor = '#374151';
        $headingColor = '#111827';
        
        $letterInfo = '';
        if ($letterTitle || $letterType) {
            $letterTitleEscaped = htmlspecialchars($letterTitle);
            $letterTypeEscaped = htmlspecialchars($letterType);
            $letterInfo = "<tr><td style='padding:0 24px 16px 24px'><div style='background:#ffffff;border:1px solid #e5e7eb;border-radius:6px;padding:16px'><h3 style='margin:0 0 12px 0;font-size:15px;font-weight:600;color:{$headingColor}'>Detail Surat</h3>";
            if ($letterTitle) {
                $letterInfo .= "<p style='margin:8px 0;font-size:14px;color:{$textColor}'><strong>Judul Surat:</strong> {$letterTitleEscaped}</p>";
            }
            if ($letterType) {
                $letterInfo .= "<p style='margin:8px 0;font-size:14px;color:{$textColor}'><strong>Tipe Surat:</strong> {$letterTypeEscaped}</p>";
            }
            $letterInfo .= "</div></td></tr>";
        }
        
        $actionButton = '';
        if ($actionUrl) {
            $actionUrlEscaped = htmlspecialchars($actionUrl);
            $actionButton = "<tr><td style='padding:8px 24px 24px 24px;text-align:center'><a href='{$actionUrlEscaped}' style='display:inline-block;background:{$primaryColor};color:#ffffff;text-decoration:none;padding:10px 24px;border-radius:6px;font-weight:600;font-size:14px'>Lihat Detail</a></td></tr>";
        }
        
        return "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta name='viewport' content='width=device-width,initial-scale=1.0'><link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap' rel='stylesheet'></head><body style='margin:0;padding:0;background:#f9fafb;font-family:Inter,Arial,sans-serif;color:{$textColor}'><table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' width='100%' style='background:#f9fafb;padding:32px 0'><tr><td align='center'><table role='presentation' cellspacing='0' cellpadding='0' border='0' width='520' style='background:#ffffff;border-radius:8px;border:1px solid #e5e7eb;overflow:hidden'><tr><td style='padding:32px 24px 16px 24px;text-align:center'><img src='{$headerImageUrl}' alt='Desa Padang Loang' style='max-width:160px;height:auto;display:block;margin:0 auto'></td></tr><tr><td style='padding:0 24px 16px 24px'><h1 style='margin:0;font-size:18px;font-weight:600;color:{$headingColor}'>{$greetingText}</h1></td></tr><tr><td style='padding:0 24px 16px 24px'><div style='background:#ffffff;border:1px solid #e5e7eb;border-radius:6px;padding:16px'><h2 style='margin:0 0 8px 0;font-size:16px;font-weight:600;color:{$headingColor}'>{$titleEscaped}</h2><p style='margin:0;font-size:14px;line-height:1.6;color:{$textColor}'>{$messageEscaped}</p></div></td></tr>{$letterInfo}{$actionButton}<tr><td style='padding:16px 24px 24px 24px;font-size:12px;line-height:1.5;color:#6b7280;text-align:center;border-top:1px solid #f3f4f6'>Pesan ini dikirim secara otomatis oleh sistem pelayanan Website Padang Loang.<br>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</td></tr></table></td></tr></table></body></html>";
    }
}

