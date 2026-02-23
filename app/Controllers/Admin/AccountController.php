<?php

namespace App\Controllers\Admin;

use App\Controllers\ProtectedController;
use App\Models\UserModel;
use App\Models\UserProfileModel;

class AccountController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        return view('Admin/accounts');
    }

    public function api()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userModel = new UserModel();
        $profileModel = new UserProfileModel();
        $db = \Config\Database::connect();
        
        // Get DataTables parameters
        $draw = $this->request->getGet('draw') ?? 1;
        $start = $this->request->getGet('start') ?? 0;
        $length = $this->request->getGet('length') ?? 10;
        $search = $this->request->getGet('search')['value'] ?? '';
        $orderColumn = $this->request->getGet('order')[0]['column'] ?? 5;
        $orderDir = $this->request->getGet('order')[0]['dir'] ?? 'desc';
        
        // Column mapping (Foto, Username, Email, Role, Status, Dibuat, Aksi)
        $columns = ['foto_profil', 'username', 'email', 'role', 'status', 'created_at'];
        $orderBy = $columns[$orderColumn] ?? 'created_at';
        
        // Build base query with join
        $builder = $db->table('users u')
            ->select('u.*, up.nama_lengkap, up.foto_profil')
            ->join('user_profiles up', 'up.user_id = u.id', 'left');
        
        // Get total records
        $recordsTotal = $builder->countAllResults(false);
        
        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('u.email', $search)
                ->orLike('u.role', $search)
                ->orLike('u.status', $search)
                ->orLike('up.nama_lengkap', $search)
                ->groupEnd();
        }
        
        // Get filtered count
        $recordsFiltered = $builder->countAllResults(false);
        
        // Apply ordering
        if ($orderBy === 'foto_profil') {
            $builder->orderBy('u.created_at', strtoupper($orderDir));
        } else {
            $builder->orderBy('u.' . $orderBy, strtoupper($orderDir));
        }
        
        // Apply pagination
        $builder->limit($length, $start);
        
        $users = $builder->get()->getResultArray();
        
        // Format data
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user['id'],
                'username' => esc($user['username']),
                'email' => esc($user['email']),
                'role' => esc($user['role']),
                'status' => esc($user['status']),
                'created_at' => date('d M Y', strtotime($user['created_at'])),
                'nama_lengkap' => (!empty($user['nama_lengkap'])) ? esc($user['nama_lengkap']) : '-',
                'foto_profil' => (!empty($user['foto_profil'])) ? $user['foto_profil'] : null,
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        return view('Admin/accounts_create');
    }

    public function edit($id)
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $userModel    = new UserModel();
        $profileModel = new UserProfileModel();
        $user         = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/akun')->with('error', 'User tidak ditemukan.');
        }

        return view('Admin/accounts_edit', [
            'user'    => $user,
            'profile' => $profileModel->find($id),
        ]);
    }

    public function store()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $userModel    = new UserModel();
        $profileModel = new UserProfileModel();

        $userId = $userModel->insert([
            'username'      => $this->request->getPost('username'),
            'email'         => $this->request->getPost('email'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'          => $this->request->getPost('role') ?: 'user',
            'status'        => $this->request->getPost('status') ?: 'aktif',
            'is_verified'   => 1,
        ], true);

        $profileModel->insert([
            'user_id'      => $userId,
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
        ]);

        return redirect()->back()->with('success', 'Akun baru dibuat.');
    }

    public function update($id)
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $userModel = new UserModel();
        $profileModel = new UserProfileModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        // Update user data
        $userData = [
            'username' => $this->request->getPost('username') ?: $user['username'],
            'email'    => $this->request->getPost('email') ?: $user['email'],
            'role'     => $this->request->getPost('role') ?: $user['role'],
            'status'   => $this->request->getPost('status') ?: $user['status'],
        ];
        $userModel->update($id, $userData);

        // Update profile data
        $nik = trim($this->request->getPost('nik') ?? '');
        $profileData = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'=> $this->request->getPost('tanggal_lahir'),
            'agama'        => $this->request->getPost('agama'),
            'pekerjaan'    => $this->request->getPost('pekerjaan'),
            'nik'          => $nik !== '' ? $nik : null,
            'alamat'       => $this->request->getPost('alamat'),
        ];

        // Handle foto profil upload
        $file = $this->request->getFile('foto_profil');
        if ($file && $file->isValid()) {
            // Gunakan whitelist untuk ekstensi gambar yang aman
            $extension = strtolower($file->getClientExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->back()->with('error', 'Format file tidak diperbolehkan. Hanya JPG, JPEG, PNG, dan WEBP.');
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

                $profileData['foto_profil'] = 'uploads/profile/' . $webpName;
            } catch (\Exception $e) {
                // Jika konversi gagal, hapus file sementara
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                return redirect()->back()->with('error', 'Gagal memproses gambar. Pastikan file adalah gambar yang valid.');
            }
        }

        $profile = $profileModel->find($id);
        if ($profile) {
            $profileModel->update($id, $profileData);
        } else {
            $profileData['user_id'] = $id;
            $profileModel->insert($profileData);
        }

        return redirect()->back()->with('success', 'Akun diperbarui.');
    }

    public function changePassword($id)
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);
        
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validasi password baru dan konfirmasi
        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'Password baru dan konfirmasi password tidak sama.');
        }

        // Validasi panjang password baru
        if (strlen($newPassword) < 6) {
            return redirect()->back()->with('error', 'Password baru minimal 6 karakter.');
        }

        // Update password
        $userModel->update($id, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }

    public function delete($id)
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $userModel = new UserModel();
        $userModel->delete($id);

        return redirect()->back()->with('success', 'Akun dihapus.');
    }
}


