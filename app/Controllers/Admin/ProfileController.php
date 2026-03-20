<?php

namespace App\Controllers\Admin;

use App\Controllers\ProtectedController;
use App\Models\UserModel;
use App\Models\UserProfileModel;

class ProfileController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $profileModel = new UserProfileModel();
        $userModel = new UserModel();
        $user = $userModel->find($this->currentUser['id']);

        return view('Admin/profile', [
            'profile' => $profileModel->find($this->currentUser['id']),
            'user' => $user,
        ]);
    }

    public function update()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $profileModel = new UserProfileModel();
        $userModel    = new UserModel();
        $uid          = $this->currentUser['id'];

        // Validasi duplikat username dan email (kecuali milik admin sendiri)
        $newUsername = trim($this->request->getPost('username'));
        $newEmail    = trim($this->request->getPost('email'));

        $duplicateUsername = $userModel->where('id !=', $uid)->where('username', $newUsername)->first();
        if ($duplicateUsername) {
            return redirect()->to('/admin/profil')->with('warning', 'Username sudah digunakan oleh pengguna lain.');
        }

        $duplicateEmail = $userModel->where('id !=', $uid)->where('email', $newEmail)->first();
        if ($duplicateEmail) {
            return redirect()->to('/admin/profil')->with('warning', 'Email sudah digunakan oleh pengguna lain.');
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
            // Gunakan whitelist untuk ekstensi gambar yang aman
            $extension = strtolower($file->getClientExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->to('/admin/profil')->with('error', 'Format file tidak diperbolehkan. Hanya JPG, JPEG, PNG, dan WEBP.');
            }

            $path = FCPATH . 'uploads/profile';
            $this->ensureUploadPath($path);
            
            // Upload file sementara
            $tempName = $file->getRandomName();
            $file->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            
            // Convert ke WebP
            $image = \Config\Services::image();
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            $webpPath = $path . '/' . $webpName;
            
            try {
                $image->withFile($tempPath)
                    ->convert(IMAGETYPE_WEBP)
                    ->save($webpPath, 85); // Quality 85
                
                // Hapus file sementara
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                
                $data['foto_profil'] = 'uploads/profile/' . $webpName;
            } catch (\Exception $e) {
                // Jika konversi gagal, hapus file sementara
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                return redirect()->to('/admin/profil')->with('error', 'Gagal memproses gambar. Pastikan file adalah gambar yang valid.');
            }
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

        return redirect()->to('/admin/profil')->with('success', 'Profil diperbarui.');
    }

    public function changePassword()
    {
        if ($redirect = $this->guard(['admin'])) {
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
            return redirect()->to('/admin/profil')->with('error', 'Password lama tidak sesuai.');
        }

        // Validasi password baru dan konfirmasi
        if ($newPassword !== $confirmPassword) {
            return redirect()->to('/admin/profil')->with('error', 'Password baru dan konfirmasi password tidak sama.');
        }

        // Validasi panjang password baru
        if (strlen($newPassword) < 6) {
            return redirect()->to('/admin/profil')->with('error', 'Password baru minimal 6 karakter.');
        }

        // Update password
        $userModel->update($uid, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/admin/profil')->with('success', 'Password berhasil diubah.');
    }
}


