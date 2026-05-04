<?php

namespace App\Controllers\Staff;

use App\Controllers\ProtectedController;
use App\Models\UserModel;
use App\Models\UserProfileModel;

class ProfileController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $profileModel = new UserProfileModel();
        $userModel = new UserModel();
        $user = $userModel->find($this->currentUser['id']);

        return view('Staff/profile', [
            'profile' => $profileModel->find($this->currentUser['id']),
            'user' => $user,
        ]);
    }

    public function update()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $profileModel = new UserProfileModel();
        $userModel    = new UserModel();
        $uid          = $this->currentUser['id'];

        // Validasi duplikat username dan email (kecuali milik staff sendiri)
        $newUsername = trim($this->request->getPost('username'));
        $newEmail    = trim($this->request->getPost('email'));

        $duplicateUsername = $userModel->where('id !=', $uid)->where('username', $newUsername)->first();
        if ($duplicateUsername) {
            return redirect()->to('/staff/profil')->with('warning', 'Username sudah digunakan oleh pengguna lain.');
        }

        $duplicateEmail = $userModel->where('id !=', $uid)->where('email', $newEmail)->first();
        if ($duplicateEmail) {
            return redirect()->to('/staff/profil')->with('warning', 'Email sudah digunakan oleh pengguna lain.');
        }

        // Validasi input fields
        $validationRules = [
            'username'  => 'permit_empty|max_length[50]',
            'email'     => 'permit_empty|valid_email|max_length[100]',
            'agama'     => 'permit_empty|max_length[50]|regex_match[/^[a-zA-Z\s]*$/]',
            'pekerjaan' => 'permit_empty|max_length[100]|regex_match[/^[a-zA-Z\s]*$/]',
            'nik'       => 'permit_empty|exact_length[16]|regex_match[/^[0-9]+$/]',
        ];

        $validationMessages = [
            'agama'     => ['regex_match' => 'Agama hanya boleh berisi huruf.'],
            'pekerjaan' => ['regex_match' => 'Pekerjaan hanya boleh berisi huruf.'],
            'nik'       => [
                'exact_length' => 'NIK harus tepat 16 digit.',
                'regex_match'  => 'NIK hanya boleh berisi angka.',
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = implode(' ', $this->validator->getErrors());
            return redirect()->to('/staff/profil')->withInput()->with('error', $errors);
        }

        $nik = trim($this->request->getPost('nik') ?? '');
        
        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'=> $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin'=> $this->request->getPost('jenis_kelamin'),
            'agama'        => $this->request->getPost('agama'),
            'pekerjaan'    => $this->request->getPost('pekerjaan'),
            'nik'          => $nik !== '' ? $nik : null,
            'alamat'       => $this->request->getPost('alamat'),
        ];

        $file = $this->request->getFile('foto_profil');
        if ($file && $file->isValid()) {
            $imgError = $this->validateProfilePhoto($file);
            if ($imgError !== null) {
                return redirect()->to('/staff/profil')->with('error', $imgError);
            }
            $ext = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/profile';
            $this->ensureUploadPath($path);
            $tempName = $file->getRandomName();
            $file->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $this->fixOrientation($tempPath);
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->toWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->to('/staff/profil')->with('error', 'Gagal memproses gambar. Pastikan file adalah gambar yang valid.');
            }
            $data['foto_profil'] = 'uploads/profile/' . $webpName;
        }

        $profile = $profileModel->find($uid);
        if ($profile) {
            $profileModel->update($uid, $data);
        } else {
            $data['user_id'] = $uid;
            $profileModel->insert($data);
        }

        $userModel->update($uid, [
            'username' => $newUsername,
            'email'    => $newEmail,
        ]);

        return redirect()->to('/staff/profil')->with('success', 'Profil diperbarui.');
    }

    public function changePassword()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $userModel = new UserModel();
        $uid = $this->currentUser['id'];
        $user = $userModel->find($uid);

        $oldPassword = $this->request->getPost('old_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validasi password lama
        if (!password_verify($oldPassword, $user['password_hash'])) {
            return redirect()->to('/staff/profil')->with('error', 'Password lama tidak sesuai.');
        }

        // Validasi password baru dan konfirmasi
        if ($newPassword !== $confirmPassword) {
            return redirect()->to('/staff/profil')->with('error', 'Password baru dan konfirmasi password tidak sama.');
        }

        // Validasi panjang password baru
        if (strlen($newPassword) < 6) {
            return redirect()->to('/staff/profil')->with('error', 'Password baru minimal 6 karakter.');
        }

        // Update password
        $userModel->update($uid, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/staff/profil')->with('success', 'Password berhasil diubah.');
    }

    // ── Upload helpers ───────────────────────────────────────────

    private function validateProfilePhoto(\CodeIgniter\HTTP\Files\UploadedFile $file): ?string
    {
        if ($file->getSize() > 1048576) return 'Ukuran foto profil tidak boleh lebih dari 1 MB.';
        $n = $file->getClientName();
        if (strpos($n, "\0") !== false) return 'Nama file tidak valid.';
        if (basename($n) !== $n) return 'Nama file tidak valid.';
        if (preg_match('/[<>:"\/\\\\|?*\x00-\x1F]/', $n)) return 'Nama file mengandung karakter yang tidak diperbolehkan.';
        if (preg_match('/[\x{200F}\x{202E}\x{202B}\x{202D}]/u', $n)) return 'Nama file mengandung karakter tidak valid.';
        $dExt = ['php','php3','php4','php5','php7','phtml','phar','asp','aspx','jsp','exe',
                 'sh','bat','cmd','py','rb','pl','cgi','htaccess','htpasswd','svg','shtml','pht'];
        $parts = explode('.', $n);
        if (count($parts) > 2)
            for ($i = 0; $i < count($parts) - 1; $i++)
                if (in_array(strtolower($parts[$i]), $dExt)) return 'Format file tidak diperbolehkan.';
        $ext = strtolower(pathinfo($n, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) return 'Format file tidak diperbolehkan. Hanya JPG, JPEG, PNG, dan WEBP.';
        if (!in_array(strtolower(trim($file->getClientMimeType())), ['image/jpeg','image/png','image/webp']))
            return 'Tipe MIME file tidak diperbolehkan.';
        $tmp = $file->getTempName();
        $fh  = @fopen($tmp, 'rb'); $hdr = $fh ? fread($fh, 12) : ''; if ($fh) fclose($fh);
        $mm  = ['jpg'=>["\xFF\xD8\xFF"],'jpeg'=>["\xFF\xD8\xFF"],'png'=>["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],'webp'=>["RIFF"]];
        if (isset($mm[$ext])) {
            $ok = false; foreach ($mm[$ext] as $m) if (str_starts_with($hdr, $m)) { $ok = true; break; }
            if (!$ok) return 'File gambar tidak valid atau telah dimanipulasi.';
        }
        $c = @file_get_contents($tmp); if ($c === false) return 'Gagal membaca file.';
        $ps = ['/\<\?php/i','/\<\?=/i','/<script[\s>]/i','/eval\s*\(/i','/exec\s*\(/i','/system\s*\(/i',
               '/passthru\s*\(/i','/shell_exec\s*\(/i','/base64_decode\s*\(/i','/preg_replace\s*\(.*\/e/i',
               '/assert\s*\(/i','/create_function\s*\(/i','/call_user_func(?:_array)?\s*\(/i',
               '/file_put_contents\s*\(/i','/str_rot13\s*\(/i','/\$_(?:GET|POST|REQUEST|COOKIE|SERVER|FILES|ENV)/i',
               '/phar:\/\//i','/data:[^,]*base64/i','/javascript:/i','/vbscript:/i','/on\w+\s*=/i'];
        foreach ($ps as $p) if (preg_match($p, $c)) return 'File mengandung konten yang tidak diperbolehkan.';
        if ($ext === 'webp' && (strlen($c) < 12 || substr($c, 8, 4) !== 'WEBP')) return 'File WEBP tidak valid.';
        return null;
    }

    private function fixOrientation(string $fp): void
    {
        if (!function_exists('exif_read_data')) return;
        $e = @exif_read_data($fp); if (!$e || !isset($e['Orientation'])) return;
        $img = \Config\Services::image();
        try {
            match ((int) $e['Orientation']) {
                3 => $img->withFile($fp)->rotate(180)->save($fp),
                6 => $img->withFile($fp)->rotate(270)->save($fp),
                8 => $img->withFile($fp)->rotate(90)->save($fp),
                default => null,
            };
        } catch (\Exception $ex) { log_message('error', 'fixOrientation: ' . $ex->getMessage()); }
    }

    private function toWebp(string $tmp, string $dest, string $ext): bool
    {
        try {
            if (strtolower($ext) === 'webp') {
                if (!rename($tmp, $dest)) { copy($tmp, $dest); @unlink($tmp); }
            } else {
                \Config\Services::image()->withFile($tmp)->convert(IMAGETYPE_WEBP)->save($dest, 85);
                if (file_exists($tmp)) @unlink($tmp);
            }
            return true;
        } catch (\Exception $e) {
            if (file_exists($tmp)) @unlink($tmp);
            log_message('error', 'toWebp: ' . $e->getMessage());
            return false;
        }
    }
}
