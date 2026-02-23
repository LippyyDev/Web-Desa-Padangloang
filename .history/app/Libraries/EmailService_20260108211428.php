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
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4;'>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
                <tr>
                    <td align='center' style='padding: 20px 0;'>
                        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' style='max-width: 600px; background-color: white; border-radius: 5px; overflow: hidden;'>
                            <tr>
                                <td style='background-color: #007bff; padding: 20px; text-align: center;'>
                                    <img src='{$headerImageUrl}' alt='Desa Padang Loang' width='600' style='width: 100%; max-width: 600px; height: auto; display: block; border: 0; outline: none; text-decoration: none;'>
                                </td>
                            </tr>
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 30px;'>
                                    <p style='margin: 0 0 15px 0;'>Halo <strong>{$name}</strong>,</p>
                                    <p style='margin: 0 0 20px 0;'>Terima kasih telah mendaftar di Website Padang Loang. Untuk menyelesaikan proses registrasi, silakan gunakan salah satu metode verifikasi berikut:</p>
                                    
                                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: white; border: 2px dashed #007bff; margin: 20px 0;'>
                                        <tr>
                                            <td style='padding: 20px; text-align: center;'>
                                                <p style='margin: 0 0 10px 0; font-weight: bold;'>Kode OTP Anda:</p>
                                                <div style='font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 5px; margin: 10px 0;'>{$otp}</div>
                                                <p style='margin: 10px 0 0 0; font-size: 12px; color: #666;'>Masukkan kode ini di halaman verifikasi</p>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <p style='text-align: center; margin: 20px 0; color: #666; font-weight: bold;'>ATAU</p>
                                    
                                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: white; border: 2px solid #007bff; margin: 20px 0;'>
                                        <tr>
                                            <td style='padding: 20px; text-align: center;'>
                                                <p style='margin: 0 0 15px 0; font-weight: bold;'>Klik tombol di bawah untuk verifikasi:</p>
                                                <a href='{$verificationLink}' style='display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0;'>Verifikasi Akun</a>
                                                <p style='margin: 15px 0 0 0; font-size: 12px; color: #666;'>Atau salin link berikut:<br><a href='{$verificationLink}' style='color: #007bff; word-break: break-all;'>{$verificationLink}</a></p>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <p style='margin: 20px 0 15px 0;'><strong>Catatan:</strong> Kode OTP dan link verifikasi berlaku selama <strong>1 jam</strong>. Jangan bagikan kode atau link ini kepada siapa pun.</p>
                                    <p style='margin: 0;'>Jika Anda tidak melakukan registrasi, abaikan email ini.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style='text-align: center; padding: 20px; color: #666; font-size: 12px; background-color: #f8f9fa;'>
                                    <p style='margin: 0 0 5px 0;'>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                                    <p style='margin: 0;'>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }

    /**
     * Template HTML untuk email OTP Reset Password
     */
    protected function getOtpResetTemplate(string $name, string $otp): string
    {
        $headerImageUrl = 'https://image2url.com/r2/bucket3/images/1767877728084-f96e49d3-6eda-406d-875b-ce1d2ce76a27.png';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4;'>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
                <tr>
                    <td align='center' style='padding: 20px 0;'>
                        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' style='max-width: 600px; background-color: white; border-radius: 5px; overflow: hidden;'>
                            <tr>
                                <td style='background-color: #dc3545; padding: 20px; text-align: center;'>
                                    <img src='{$headerImageUrl}' alt='Desa Padang Loang' width='600' style='width: 100%; max-width: 600px; height: auto; display: block; border: 0; outline: none; text-decoration: none;'>
                                </td>
                            </tr>
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 30px;'>
                                    <p style='margin: 0 0 15px 0;'>Halo <strong>{$name}</strong>,</p>
                                    <p style='margin: 0 0 20px 0;'>Kami menerima permintaan untuk mereset password akun Anda. Gunakan kode OTP berikut untuk melanjutkan:</p>
                                    
                                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: white; border: 2px dashed #dc3545; margin: 20px 0;'>
                                        <tr>
                                            <td style='padding: 20px; text-align: center;'>
                                                <div style='font-size: 32px; font-weight: bold; color: #dc3545; letter-spacing: 5px;'>{$otp}</div>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <p style='margin: 20px 0 15px 0;'>Kode OTP ini berlaku selama <strong>1 jam</strong>. Jangan bagikan kode ini kepada siapa pun.</p>
                                    <p style='margin: 0;'>Jika Anda tidak meminta reset password, abaikan email ini dan password Anda tidak akan berubah.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style='text-align: center; padding: 20px; color: #666; font-size: 12px; background-color: #f8f9fa;'>
                                    <p style='margin: 0 0 5px 0;'>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                                    <p style='margin: 0;'>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
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
        
        $actionButton = '';
        if ($actionUrl) {
            $actionButton = "
                                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='margin: 30px 0;'>
                                        <tr>
                                            <td align='center'>
                                                <a href='{$actionUrl}' style='display: inline-block; background-color: {$color['bg']}; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Lihat Detail</a>
                                            </td>
                                        </tr>
                                    </table>";
        }
        
        $letterInfo = '';
        if ($letterTitle || $letterType) {
            $letterInfo = "
            <div style='background-color: white; border: 1px solid #dee2e6; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                <h4 style='margin-top: 0; margin-bottom: 15px; color: {$color['bg']};'>Detail Surat</h4>";
            
            if ($letterTitle) {
                $letterInfo .= "
                <p style='margin: 8px 0;'><strong>Judul Surat:</strong> {$letterTitle}</p>";
            }
            
            if ($letterType) {
                $letterInfo .= "
                <p style='margin: 8px 0;'><strong>Tipe Surat:</strong> {$letterType}</p>";
            }
            
            $letterInfo .= "
            </div>";
        }
        
        $headerImageUrl = 'https://image2url.com/r2/bucket3/images/1767877728084-f96e49d3-6eda-406d-875b-ce1d2ce76a27.png';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 0; }
                .header { background-color: {$color['bg']}; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .header img { max-width: 100%; height: auto; display: block; margin: 0 auto; border: 0; }
                .content { background-color: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
                .message-box { background-color: white; border-left: 4px solid {$color['border']}; padding: 20px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header' style='background-color: {$color['bg']}; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;'>
                    <img src='{$headerImageUrl}' alt='Desa Padang Loang' style='max-width: 100%; width: auto; height: auto; display: block; margin: 0 auto; border: 0; outline: none; text-decoration: none;'>
                </div>
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 30px;'>
                                    <p style='margin: 0 0 15px 0;'>Halo <strong>{$name}</strong>,</p>
                                    
                                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: white; border-left: 4px solid {$color['border']}; margin: 20px 0;'>
                                        <tr>
                                            <td style='padding: 20px;'>
                                                <h3 style='margin: 0 0 10px 0; color: {$color['bg']}; font-size: 18px;'>{$title}</h3>
                                                <p style='margin: 0;'>{$message}</p>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    {$letterInfo}
                                    
                                    {$actionButton}
                                    
                                    <p style='margin-top: 20px; color: #666; font-size: 14px;'>Email ini dikirim secara otomatis dari sistem Website Padang Loang.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style='text-align: center; padding: 20px; color: #666; font-size: 12px; background-color: #f8f9fa;'>
                                    <p style='margin: 0 0 5px 0;'>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                                    <p style='margin: 0;'>&copy; " . date('Y') . " Website Padang Loang. All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }
}

