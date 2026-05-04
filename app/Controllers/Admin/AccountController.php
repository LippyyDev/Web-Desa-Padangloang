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

        $userModel    = new UserModel();
        $profileModel = new UserProfileModel();
        $db           = \Config\Database::connect();
        $csrfToken    = csrf_token();

        // Get DataTables parameters
        $draw        = $this->request->getPost('draw') ?? 1;
        $start       = $this->request->getPost('start') ?? 0;
        $length      = $this->request->getPost('length') ?? 10;
        $searchPost  = $this->request->getPost('search');
        $search      = is_array($searchPost) ? ($searchPost['value'] ?? '') : ($searchPost ?? '');
        $orderPost   = $this->request->getPost('order');
        $orderColumn = is_array($orderPost) ? ($orderPost[0]['column'] ?? 5) : 5;
        $orderDir    = is_array($orderPost) ? ($orderPost[0]['dir'] ?? 'desc') : 'desc';

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
                'id'          => $user['id'],
                'username'    => esc($user['username']),
                'email'       => esc($user['email']),
                'role'        => esc($user['role']),
                'status'      => esc($user['status']),
                'created_at'  => date('d M Y', strtotime($user['created_at'])),
                'nama_lengkap'=> (!empty($user['nama_lengkap'])) ? esc($user['nama_lengkap']) : '-',
                'foto_profil' => (!empty($user['foto_profil'])) ? $user['foto_profil'] : null,
            ];
        }

        return $this->response->setJSON([
            $csrfToken        => csrf_hash(),
            'draw'            => intval($draw),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
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

        // Validasi duplikat
        $newUsername = trim($this->request->getPost('username'));
        $newEmail    = trim($this->request->getPost('email'));

        if ($userModel->where('username', $newUsername)->first()) {
            return redirect()->back()->withInput()->with('warning', 'Username sudah digunakan oleh pengguna lain.');
        }

        if ($userModel->where('email', $newEmail)->first()) {
            return redirect()->back()->withInput()->with('warning', 'Email sudah digunakan oleh pengguna lain.');
        }

        // Validasi input fields
        if (!$this->validate(
            [
                'username' => 'required|max_length[50]',
                'email'    => 'required|valid_email|max_length[100]',
                // H2: Validasi password wajib dan minimal 8 karakter dengan huruf + angka
                'password' => 'required|min_length[8]|regex_match[/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/]',
            ],
            [
                'password' => [
                    'required'    => 'Password wajib diisi.',
                    'min_length'  => 'Password minimal 8 karakter.',
                    'regex_match' => 'Password harus mengandung minimal satu huruf dan satu angka.',
                ],
            ]
        )) {
            $errors = implode(' ', $this->validator->getErrors());
            return redirect()->back()->withInput()->with('error', $errors);
        }

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

        // Validasi duplikat
        $newUsername = trim($this->request->getPost('username')) ?: $user['username'];
        $newEmail    = trim($this->request->getPost('email')) ?: $user['email'];

        if ($userModel->where('id !=', $id)->where('username', $newUsername)->first()) {
            return redirect()->back()->withInput()->with('warning', 'Username sudah digunakan oleh pengguna lain.');
        }

        if ($userModel->where('id !=', $id)->where('email', $newEmail)->first()) {
            return redirect()->back()->withInput()->with('warning', 'Email sudah digunakan oleh pengguna lain.');
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
            return redirect()->back()->withInput()->with('error', $errors);
        }

        // Update user data
        $userData = [
            'username' => $newUsername,
            'email'    => $newEmail,
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
            $imgError = $this->validateProfilePhoto($file);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
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
                return redirect()->back()->with('error', 'Gagal memproses gambar. Pastikan file adalah gambar yang valid.');
            }
            $profileData['foto_profil'] = 'uploads/profile/' . $webpName;
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

        // M2: Cegah admin menghapus akunnya sendiri
        if ($id == $this->currentUser['id']) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userModel = new UserModel();
        $userModel->delete($id);

        return redirect()->back()->with('success', 'Akun dihapus.');
    }

    // ── Upload helpers ───────────────────────────────────────────

    /**
     * Validasi foto profil — batas 1MB, 8 lapisan keamanan.
     * Return string error atau null jika lolos.
     */
    private function validateProfilePhoto(\CodeIgniter\HTTP\Files\UploadedFile $file): ?string
    {
        if ($file->getSize() > 1048576)
            return 'Ukuran foto profil tidak boleh lebih dari 1 MB.';

        $clientName = $file->getClientName();
        if (strpos($clientName, "\0") !== false) return 'Nama file tidak valid.';
        if (basename($clientName) !== $clientName) return 'Nama file tidak valid.';
        if (preg_match('/[<>:"\/\\\\|?*\x00-\x1F]/', $clientName))
            return 'Nama file mengandung karakter yang tidak diperbolehkan.';
        if (preg_match('/[\x{200F}\x{202E}\x{202B}\x{202D}]/u', $clientName))
            return 'Nama file mengandung karakter tidak valid.';

        $dangerousExts = ['php','php3','php4','php5','php7','phtml','phar','asp','aspx','jsp',
                          'exe','sh','bat','cmd','py','rb','pl','cgi','htaccess','htpasswd','svg','shtml','pht'];
        $parts = explode('.', $clientName);
        if (count($parts) > 2)
            for ($i = 0; $i < count($parts) - 1; $i++)
                if (in_array(strtolower($parts[$i]), $dangerousExts)) return 'Format file tidak diperbolehkan.';

        $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp']))
            return 'Format file tidak diperbolehkan. Hanya JPG, JPEG, PNG, dan WEBP.';

        if (!in_array(strtolower(trim($file->getClientMimeType())), ['image/jpeg','image/png','image/webp']))
            return 'Tipe MIME file tidak diperbolehkan.';

        $tmpPath = $file->getTempName();
        $fh      = @fopen($tmpPath, 'rb');
        $header  = $fh ? fread($fh, 12) : '';
        if ($fh) fclose($fh);
        $magicMap = ['jpg'=>["\xFF\xD8\xFF"],'jpeg'=>["\xFF\xD8\xFF"],
                     'png'=>["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],'webp'=>["RIFF"]];
        if (isset($magicMap[$ext])) {
            $ok = false;
            foreach ($magicMap[$ext] as $magic) if (str_starts_with($header, $magic)) { $ok = true; break; }
            if (!$ok) return 'File gambar tidak valid atau telah dimanipulasi.';
        }

        $content = @file_get_contents($tmpPath);
        if ($content === false) return 'Gagal membaca file.';
        $patterns = ['/\<\?php/i','/\<\?=/i','/<script[\s>]/i','/eval\s*\(/i','/exec\s*\(/i',
            '/system\s*\(/i','/passthru\s*\(/i','/shell_exec\s*\(/i','/base64_decode\s*\(/i',
            '/preg_replace\s*\(.*\/e/i','/assert\s*\(/i','/create_function\s*\(/i',
            '/call_user_func(?:_array)?\s*\(/i','/file_put_contents\s*\(/i','/str_rot13\s*\(/i',
            '/\$_(?:GET|POST|REQUEST|COOKIE|SERVER|FILES|ENV)/i','/phar:\/\//i',
            '/data:[^,]*base64/i','/javascript:/i','/vbscript:/i','/on\w+\s*=/i'];
        foreach ($patterns as $p) if (preg_match($p, $content)) return 'File mengandung konten yang tidak diperbolehkan.';

        if ($ext === 'webp' && (strlen($content) < 12 || substr($content, 8, 4) !== 'WEBP'))
            return 'File WEBP tidak valid.';

        return null;
    }

    private function fixOrientation(string $filePath): void
    {
        if (!function_exists('exif_read_data')) return;
        $exif = @exif_read_data($filePath);
        if (!$exif || !isset($exif['Orientation'])) return;
        $image = \Config\Services::image();
        try {
            match ((int) $exif['Orientation']) {
                3 => $image->withFile($filePath)->rotate(180)->save($filePath),
                6 => $image->withFile($filePath)->rotate(270)->save($filePath),
                8 => $image->withFile($filePath)->rotate(90)->save($filePath),
                default => null,
            };
        } catch (\Exception $e) {
            log_message('error', 'fixOrientation failed: ' . $e->getMessage());
        }
    }

    private function toWebp(string $tempPath, string $webpPath, string $extension): bool
    {
        try {
            if (strtolower($extension) === 'webp') {
                if (!rename($tempPath, $webpPath)) { copy($tempPath, $webpPath); @unlink($tempPath); }
            } else {
                \Config\Services::image()->withFile($tempPath)->convert(IMAGETYPE_WEBP)->save($webpPath, 85);
                if (file_exists($tempPath)) @unlink($tempPath);
            }
            return true;
        } catch (\Exception $e) {
            if (file_exists($tempPath)) @unlink($tempPath);
            log_message('error', 'toWebp failed: ' . $e->getMessage());
            return false;
        }
    }
}
