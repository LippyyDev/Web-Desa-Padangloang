<?php

namespace App\Controllers\Guest;

use App\Controllers\BaseController;
use App\Libraries\EmailService;
use App\Models\UserModel;
use App\Models\UserProfileModel;

class AuthController extends BaseController
{
    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function login()
    {
        if ($this->currentUser) {
            return redirect()->to('/dashboard');
        }

        return view('Guest/auth/login', ['hideFooter' => true, 'hideNavbar' => true]);
    }

    public function doLogin()
    {
        $identity = trim($this->request->getPost('identity'));
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->where('email', $identity)
            ->orWhere('username', $identity)
            ->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Kredensial tidak valid.');
        }

        if ($user['status'] !== 'aktif') {
            return redirect()->back()->with('error', 'Akun nonaktif. Hubungi admin.');
        }

        if (!(bool) $user['is_verified']) {
            $otp = $this->generateOtp();
            $verificationToken = $this->generateVerificationToken();
            
            // Simpan data verifikasi di session (tidak perlu tabel)
            $verificationData = [
                'user_id'    => $user['id'],
                'email'      => $user['email'],
                'username'   => $user['username'],
                'otp'        => $otp,
                'token'      => $verificationToken,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            ];
            session()->set('pending_user_verification', $verificationData);
            
            // Buat link verifikasi
            $verificationLink = base_url('/verify/' . $verificationToken);
            
            // Kirim email OTP dan link
            $emailService = new EmailService();
            $emailService->sendOtpRegister($user['email'], $user['username'], $otp, $verificationLink);
            
            session()->setFlashdata('otp_preview', $otp);
            session()->set('pending_verification', $user['email']);

            return redirect()->to('/verify')->with('info', 'Silakan verifikasi akun terlebih dahulu. Kode OTP dan link verifikasi telah dikirim ke email Anda.');
        }

        session()->set('user', [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function register()
    {
        return view('Guest/auth/register', ['hideFooter' => true, 'hideNavbar' => true]);
    }

    public function doRegister()
    {
        $username = trim($this->request->getPost('username'));
        $email    = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');


        // Cek apakah email atau username sudah digunakan
        $userModel = new UserModel();
        $exists    = $userModel->where('email', $email)->orWhere('username', $username)->first();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Username atau email sudah digunakan.');
        }

        // JANGAN SIMPAN KE DATABASE DULU - Simpan data sementara di session
        $otp = $this->generateOtp();
        $verificationToken = $this->generateVerificationToken();
        
        // Simpan data registrasi di session
        $registrationData = [
            'username'      => $username,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'otp'           => $otp,
            'token'         => $verificationToken,
            'expires_at'    => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ];
        
        session()->set('pending_registration', $registrationData);
        
        // Buat link verifikasi (token disimpan di session, tidak di database)
        $verificationLink = base_url('/verify/' . $verificationToken);
        
        // Kirim email dengan OTP dan link
        $emailService = new EmailService();
        $emailSent = $emailService->sendOtpRegister($email, $username, $otp, $verificationLink);

        session()->set('pending_verification', $email);
        session()->setFlashdata('otp_preview', $otp);

        if ($emailSent) {
            return redirect()->to('/verify')->with('info', 'Registrasi berhasil! Silakan cek email Anda untuk kode OTP dan link verifikasi.');
        } else {
            return redirect()->to('/verify')->with('warning', 'Registrasi berhasil, namun gagal mengirim email. Silakan cek kode OTP di bawah ini.');
        }
    }

    public function verify()
    {
        $pendingEmail = session()->get('pending_verification');

        return view('Guest/auth/verify', [
            'pendingEmail' => $pendingEmail,
            'previewOtp'   => session()->getFlashdata('otp_preview'),
        ]);
    }

    public function doVerify()
    {
        $email = trim($this->request->getPost('email'));
        $otp   = trim($this->request->getPost('otp'));

        // Cek apakah ini registrasi baru atau verifikasi user yang sudah ada
        $registrationData = session()->get('pending_registration');
        $userVerificationData = session()->get('pending_user_verification');
        
        // Kasus 1: Registrasi baru
        if ($registrationData) {
            // Cek apakah email cocok
            if ($registrationData['email'] !== $email) {
                return redirect()->back()->with('error', 'Email tidak sesuai dengan data registrasi.');
            }

            // Cek apakah OTP cocok
            if ($registrationData['otp'] !== $otp) {
                return redirect()->back()->with('error', 'OTP tidak valid.');
            }

            // Cek apakah sudah kadaluarsa
            if (strtotime($registrationData['expires_at']) < time()) {
                session()->remove('pending_registration');
                return redirect()->to('/register')->with('error', 'Kode verifikasi sudah kadaluarsa. Silakan daftar ulang.');
            }

            // Verifikasi berhasil - SIMPAN KE DATABASE SEKARANG
            $userModel = new UserModel();
            $userId = $userModel->insert([
                'username'      => $registrationData['username'],
                'email'         => $registrationData['email'],
                'password_hash' => $registrationData['password_hash'],
                'role'          => 'user',
                'status'        => 'aktif',
                'is_verified'   => 1,
            ], true);

            // Buat profile
            $profileModel = new UserProfileModel();
            $profileModel->insert([
                'user_id'      => $userId,
                'nama_lengkap' => $registrationData['username'],
            ]);

            // Bersihkan session
            session()->remove('pending_registration');
            session()->remove('pending_verification');
            
            // Set session user
            $user = $userModel->find($userId);
            session()->set('user', [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ]);

            return redirect()->to('/dashboard')->with('success', 'Akun berhasil diverifikasi dan dibuat.');
        }
        
        // Kasus 2: Verifikasi user yang sudah ada (belum verified)
        if ($userVerificationData) {
            // Cek email
            if ($userVerificationData['email'] !== $email) {
                return redirect()->back()->with('error', 'Email tidak sesuai.');
        }

            // Cek OTP
            if ($userVerificationData['otp'] !== $otp) {
                return redirect()->back()->with('error', 'OTP tidak valid.');
            }

            // Cek kadaluarsa
            if (strtotime($userVerificationData['expires_at']) < time()) {
                session()->remove('pending_user_verification');
                return redirect()->to('/login')->with('error', 'Kode verifikasi sudah kadaluarsa. Silakan login lagi untuk mendapatkan kode baru.');
            }

            // Update user menjadi verified
            $userModel = new UserModel();
            $userModel->update($userVerificationData['user_id'], ['is_verified' => 1]);

            // Bersihkan session
            session()->remove('pending_user_verification');
            session()->remove('pending_verification');
            
            // Set session user
            $user = $userModel->find($userVerificationData['user_id']);
            session()->set('user', [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ]);

            return redirect()->to('/dashboard')->with('success', 'Akun berhasil diverifikasi.');
        }

        // Tidak ada data verifikasi
        return redirect()->back()->with('error', 'Sesi verifikasi tidak ditemukan. Silakan daftar atau login ulang.');
    }
    
    /**
     * Verifikasi via link
     */
    public function verifyByLink(string $token)
    {
        // Ambil data registrasi dari session
        $registrationData = session()->get('pending_registration');
        
        if (!$registrationData) {
            return redirect()->to('/register')->with('error', 'Sesi registrasi tidak ditemukan. Silakan daftar ulang.');
        }

        // Cek apakah token cocok dengan yang di session
        // Tidak perlu cek di database karena token disimpan di session
        if ($registrationData['token'] !== $token) {
            return redirect()->to('/register')->with('error', 'Link verifikasi tidak valid atau sudah kadaluarsa.');
        }

        // Cek apakah sudah kadaluarsa
        if (strtotime($registrationData['expires_at']) < time()) {
            session()->remove('pending_registration');
            return redirect()->to('/register')->with('error', 'Link verifikasi sudah kadaluarsa. Silakan daftar ulang.');
        }

        // Verifikasi berhasil - SIMPAN KE DATABASE SEKARANG
        $userModel = new UserModel();
        $userId = $userModel->insert([
            'username'      => $registrationData['username'],
            'email'         => $registrationData['email'],
            'password_hash' => $registrationData['password_hash'],
            'role'          => 'user',
            'status'        => 'aktif',
            'is_verified'   => 1, // Langsung verified karena sudah verifikasi
        ], true);

        // Buat profile
        $profileModel = new UserProfileModel();
        $profileModel->insert([
            'user_id'      => $userId,
            'nama_lengkap' => $registrationData['username'],
        ]);

        // Tidak perlu update token di database karena token tidak disimpan di database untuk registrasi pending

        // Bersihkan session
        session()->remove('pending_registration');
        session()->remove('pending_verification');
        
        // Set session user
        $user = $userModel->find($userId);
        session()->set('user', [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ]);

        return redirect()->to('/dashboard')->with('success', 'Akun berhasil diverifikasi dan dibuat.');
    }

    public function forgotPassword()
    {
        return view('Guest/auth/forgot', ['hideFooter' => true, 'hideNavbar' => true]);
    }

    public function sendReset()
    {
        $email     = trim($this->request->getPost('email'));
        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Email tidak terdaftar.');
        }

        // Generate OTP untuk reset password - simpan di session (tidak perlu tabel)
        $otp = $this->generateOtp();
        
        // Simpan data reset di session
        $resetData = [
            'user_id'     => $user['id'],
            'email'       => $user['email'],
            'username'    => $user['username'],
            'otp'         => $otp,
            'expires_at'  => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'otp_sent_at' => time(), // Untuk cooldown
        ];
        session()->set('pending_password_reset', $resetData);
        
        // Kirim email OTP
        $emailService = new EmailService();
        $emailSent = $emailService->sendOtpReset($email, $user['username'], $otp);
        
        // HAPUS OTP PREVIEW demi keamanan - user harus cek email
        session()->set('pending_reset', $email);

        if ($emailSent) {
            return redirect()->to('/reset-password');
        } else {
            return redirect()->to('/reset-password')->with('warning', 'Gagal mengirim email. Silakan coba kirim ulang OTP beberapa saat lagi.');
        }
    }

    public function resetPassword()
    {
        return view('Guest/auth/reset', [
            'pendingEmail' => session()->get('pending_reset'),
            'hideFooter'   => true,
            'hideNavbar'   => true,
            // 'previewOtp'   => session()->getFlashdata('otp_preview'), // Dihapus
        ]);
    }

    public function resendResetOtp()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $resetData = session()->get('pending_password_reset');

        if (!$resetData) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesi tidak ditemukan. Silakan request reset password ulang.'
            ]);
        }

        // Cek cooldown (60 detik)
        $lastSent = $resetData['otp_sent_at'] ?? 0;
        $diff = time() - $lastSent;
        
        if ($diff < 60) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Silakan tunggu ' . (60 - $diff) . ' detik lagi.'
            ]);
        }

        // Generate OTP baru
        $otp = $this->generateOtp();
        
        // Update session
        $resetData['otp'] = $otp;
        $resetData['otp_sent_at'] = time();
        $resetData['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        session()->set('pending_password_reset', $resetData);

        // Kirim email
        $emailService = new EmailService();
        $emailSent = $emailService->sendOtpReset($resetData['email'], $resetData['username'], $otp);

        if ($emailSent) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kode OTP baru telah dikirim ke email Anda.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengirim email. Silakan coba lagi.'
            ]);
        }
    }

    public function doResetPassword()
    {
        $email    = trim($this->request->getPost('email'));
        $otp      = trim($this->request->getPost('otp'));
        $password = $this->request->getPost('password');

        // Ambil data reset dari session (tidak perlu tabel)
        $resetData = session()->get('pending_password_reset');

        if (!$resetData) {
            return redirect()->back()->with('error', 'Sesi reset password tidak ditemukan. Silakan request reset password lagi.');
        }

        // Cek email
        if ($resetData['email'] !== $email) {
            return redirect()->back()->with('error', 'Email tidak sesuai.');
        }

        // Cek OTP
        if ($resetData['otp'] !== $otp) {
            return redirect()->back()->with('error', 'OTP tidak valid.');
        }

        // Cek kadaluarsa
        if (strtotime($resetData['expires_at']) < time()) {
            session()->remove('pending_password_reset');
            return redirect()->to('/forgot-password')->with('error', 'Kode OTP sudah kadaluarsa. Silakan request reset password lagi.');
        }

        // Update password
        $userModel = new UserModel();
        $userModel->update($resetData['user_id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        
        // Bersihkan session
        session()->remove('pending_password_reset');
        session()->remove('pending_reset');

        return redirect()->to('/login')->with('success', 'Password berhasil direset.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('success', 'Berhasil logout.');
    }

    /**
     * Handle Firebase Google Sign-In
     * This endpoint receives the Firebase ID token and verifies it
     */
    public function firebaseAuth()
    {
        // Get Firebase ID token from request
        $idToken = $this->request->getPost('idToken');
        
        if (!$idToken) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID token tidak ditemukan.'
            ])->setStatusCode(400);
        }

        // Verify Firebase ID token
        // Note: For production, consider using Firebase Admin SDK for proper token verification
        try {
            // Decode the JWT token
            $tokenParts = explode('.', $idToken);
            if (count($tokenParts) !== 3) {
                throw new \Exception('Invalid token format');
            }
            
            // Decode base64url encoded payload
            $payloadEncoded = str_replace(['-', '_'], ['+', '/'], $tokenParts[1]);
            // Add padding if needed
            $padding = strlen($payloadEncoded) % 4;
            if ($padding) {
                $payloadEncoded .= str_repeat('=', 4 - $padding);
            }
            
            $payload = json_decode(base64_decode($payloadEncoded), true);
            
            if (!$payload) {
                throw new \Exception('Invalid token payload');
            }

            // Verify token is not expired
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new \Exception('Token expired');
            }

            // Extract user information from token
            $firebaseUid = $payload['user_id'] ?? $payload['sub'] ?? null;
            $email = $payload['email'] ?? null;
            $name = $payload['name'] ?? null;
            $emailVerified = $payload['email_verified'] ?? false;

            if (!$firebaseUid || !$email) {
                throw new \Exception('Missing required user information');
            }

            $userModel = new UserModel();
            
            // Check if user exists by firebase_uid
            $user = $userModel->where('firebase_uid', $firebaseUid)->first();
            
            if ($user) {
                // User exists, login
                if ($user['status'] !== 'aktif') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Akun nonaktif. Hubungi admin.'
                    ])->setStatusCode(403);
                }

                // Update email if changed in Google account
                if ($user['email'] !== $email) {
                    $userModel->update($user['id'], ['email' => $email]);
                }

                // Set session
                session()->set('user', [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'email'    => $email,
                    'role'     => $user['role'],
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Login berhasil.',
                    'redirect' => base_url('/dashboard')
                ]);
            } else {
                // New user, check if email already exists
                $existingUser = $userModel->where('email', $email)->first();
                
                if ($existingUser) {
                    // Email exists but no firebase_uid, link accounts
                    $userModel->update($existingUser['id'], [
                        'firebase_uid' => $firebaseUid,
                        'is_verified' => 1, // Google verified emails are trusted
                    ]);

                    if ($existingUser['status'] !== 'aktif') {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Akun nonaktif. Hubungi admin.'
                        ])->setStatusCode(403);
                    }

                    session()->set('user', [
                        'id'       => $existingUser['id'],
                        'username' => $existingUser['username'],
                        'email'    => $email,
                        'role'     => $existingUser['role'],
                    ]);

                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Akun berhasil dihubungkan dengan Google.',
                        'redirect' => base_url('/dashboard')
                    ]);
                } else {
                    // Create new user
                    // Generate username from email or name
                    $username = $name ? strtolower(str_replace(' ', '', $name)) : explode('@', $email)[0];
                    $originalUsername = $username;
                    $counter = 1;
                    
                    // Ensure username is unique
                    while ($userModel->where('username', $username)->first()) {
                        $username = $originalUsername . $counter;
                        $counter++;
                    }

                    $userId = $userModel->insert([
                        'firebase_uid'  => $firebaseUid,
                        'username'      => $username,
                        'email'         => $email,
                        'password_hash' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT), // Random password for Firebase users
                        'role'          => 'user',
                        'status'        => 'aktif',
                        'is_verified'   => $emailVerified ? 1 : 1, // Google accounts are considered verified
                    ], true);

                    // Create profile
                    $profileModel = new UserProfileModel();
                    $profileModel->insert([
                        'user_id'      => $userId,
                        'nama_lengkap' => $name ?? $username,
                    ]);

                    // Set session
                    $newUser = $userModel->find($userId);
                    session()->set('user', [
                        'id'       => $newUser['id'],
                        'username' => $newUser['username'],
                        'email'    => $newUser['email'],
                        'role'     => $newUser['role'],
                    ]);

                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Registrasi dan login berhasil.',
                        'redirect' => base_url('/dashboard')
                    ]);
                }
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Autentikasi gagal: ' . $e->getMessage()
            ])->setStatusCode(400);
        }
    }
}


