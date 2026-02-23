<?php

namespace App\Libraries;

// Pastikan vendor autoload dimuat
$vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// Fallback: load PHPMailer langsung jika autoload gagal
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $phpmailerPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    $exceptionPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    $smtpPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    
    if (file_exists($phpmailerPath)) {
        require_once $exceptionPath;
        require_once $smtpPath;
        require_once $phpmailerPath;
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailService
{
    private $mail;
    private $fromEmail;
    private $fromName;
    private $smtpHost;
    private $smtpUser;
    private $smtpPass;
    private $smtpPort;
    private $smtpSecure;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        
        // Konfigurasi dari Config/Email
        $emailConfig = config('Email');
        $this->fromEmail = $emailConfig->fromEmail ?: 'websitepadangloang@gmail.com';
        $this->fromName = $emailConfig->fromName ?: 'Website Padang Loang';
        $this->smtpHost = $emailConfig->SMTPHost ?: 'smtp.gmail.com';
        $this->smtpUser = $emailConfig->SMTPUser ?: 'websitepadangloang@gmail.com';
        $this->smtpPass = $emailConfig->SMTPPass ?: 'yxzwawrscjqzsesj';
        $this->smtpPort = $emailConfig->SMTPPort ?: 587;
        $this->smtpSecure = $emailConfig->SMTPCrypto ?: PHPMailer::ENCRYPTION_STARTTLS;

        // Konfigurasi SMTP
        $this->mail->isSMTP();
        $this->mail->Host = $this->smtpHost;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->smtpUser;
        $this->mail->Password = $this->smtpPass;
        $this->mail->SMTPSecure = $this->smtpSecure;
        $this->mail->Port = $this->smtpPort;
        $this->mail->CharSet = 'UTF-8';
        
        // Set pengirim default
        $this->mail->setFrom($this->fromEmail, $this->fromName);
    }

    /**
     * Mengirim email
     *
     * @param string $to Email penerima
     * @param string $subject Subjek email
     * @param string $body Isi email (HTML atau plain text)
     * @param string $toName Nama penerima (opsional)
     * @param bool $isHTML Apakah body adalah HTML
     * @return bool
     */
    public function send(string $to, string $subject, string $body, string $toName = '', bool $isHTML = true): bool
    {
        try {
            // Reset recipients
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Set penerima
            $this->mail->addAddress($to, $toName);
            
            // Set format email
            $this->mail->isHTML($isHTML);
            
            // Set subjek dan body
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            
            // Jika bukan HTML, set AltBody
            if ($isHTML) {
                $this->mail->AltBody = strip_tags($body);
            }
            
            // Kirim email
            $this->mail->send();
            
            return true;
        } catch (Exception $e) {
            log_message('error', 'PHPMailer Error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Mengirim email dengan template OTP
     *
     * @param string $to Email penerima
     * @param string $otp Kode OTP
     * @param string $type Tipe OTP (register/reset)
     * @return bool
     */
    public function sendOtp(string $to, string $otp, string $type = 'register'): bool
    {
        $subject = $type === 'register' 
            ? 'Kode Verifikasi - Website Padang Loang' 
            : 'Kode Reset Password - Website Padang Loang';
        
        $title = $type === 'register' 
            ? 'Kode Verifikasi Akun' 
            : 'Kode Reset Password';
        
        $message = $type === 'register'
            ? 'Gunakan kode berikut untuk memverifikasi akun Anda:'
            : 'Gunakan kode berikut untuk mereset password Anda:';
        
        $body = $this->getOtpTemplate($otp, $title, $message);
        
        return $this->send($to, $subject, $body);
    }

    /**
     * Template HTML untuk email OTP
     *
     * @param string $otp Kode OTP
     * @param string $title Judul email
     * @param string $message Pesan
     * @return string
     */
    private function getOtpTemplate(string $otp, string $title, string $message): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($title) . '</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background-color: #f4f4f4; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                <h2 style="color: #2c3e50; margin-top: 0;">' . htmlspecialchars($title) . '</h2>
                <p style="font-size: 16px;">' . htmlspecialchars($message) . '</p>
                <div style="background-color: #fff; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0;">
                    <h1 style="color: #3498db; font-size: 32px; letter-spacing: 5px; margin: 0;">' . htmlspecialchars($otp) . '</h1>
                </div>
                <p style="font-size: 14px; color: #666;">Kode ini berlaku selama 1 jam. Jangan bagikan kode ini kepada siapapun.</p>
            </div>
            <div style="text-align: center; color: #999; font-size: 12px; margin-top: 20px;">
                <p>Email ini dikirim dari Website Padang Loang</p>
            </div>
        </body>
        </html>';
    }
}

