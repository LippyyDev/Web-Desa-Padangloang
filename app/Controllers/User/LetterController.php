<?php

namespace App\Controllers\User;

use App\Controllers\ProtectedController;
use App\Models\LetterAttachmentModel;
use App\Models\LetterModel;
use App\Models\LetterReplyModel;
use App\Models\ReplyAttachmentModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class LetterController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $letters     = $letterModel->where('user_id', $this->currentUser['id'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('User/letters/index', ['letters' => $letters]);
    }

    public function api()
    {
        if ($redirect = $this->guard(['user'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $letterModel = new LetterModel();
        $db = \Config\Database::connect();
        
        // Get DataTables parameters
        $draw = $this->request->getPost('draw') ?? 1;
        $start = $this->request->getPost('start') ?? 0;
        $length = $this->request->getPost('length') ?? 10;
        $search = $this->request->getPost('search')['value'] ?? '';
        $searchCustom = $this->request->getPost('search_custom') ?? '';
        $orderColumn = $this->request->getPost('order')[0]['column'] ?? 3;
        $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
        
        // Get filter parameters
        $dateStart = $this->request->getPost('date_start') ?? '';
        $dateEnd = $this->request->getPost('date_end') ?? '';
        $tipeSuratFilter = $this->request->getPost('tipe_surat_filter') ?? '';
        $statusFilter = $this->request->getPost('status_filter') ?? '';
        
        // Use search_custom if provided, otherwise use default search
        $searchValue = !empty($searchCustom) ? $searchCustom : $search;
        
        // Column mapping
        $columns = ['kode_unik', 'judul_perihal', 'tipe_surat', 'status', 'sent_at'];
        $orderBy = $columns[$orderColumn] ?? 'sent_at';
        
        // Build base query
        $builder = $db->table('letters')
            ->where('user_id', $this->currentUser['id']);
        
        // Get total records
        $recordsTotal = $builder->countAllResults(false);
        
        // Apply date filter
        if (!empty($dateStart)) {
            $builder->where('DATE(sent_at) >=', $dateStart);
        }
        if (!empty($dateEnd)) {
            $builder->where('DATE(sent_at) <=', $dateEnd);
        }
        
        // Apply tipe surat filter
        if (!empty($tipeSuratFilter)) {
            $builder->where('tipe_surat', $tipeSuratFilter);
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $builder->where('status', $statusFilter);
        }
        
        // Apply search filter
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('kode_unik', $searchValue)
                ->orLike('judul_perihal', $searchValue)
                ->orLike('tipe_surat', $searchValue)
                ->orLike('status', $searchValue)
                ->groupEnd();
        }
        
        // Get filtered count
        $recordsFiltered = $builder->countAllResults(false);
        
        // Apply ordering
        $builder->orderBy($orderBy, strtoupper($orderDir));
        
        // Apply pagination
        $builder->limit($length, $start);
        
        $letters = $builder->get()->getResultArray();
        
        // Format data
        $data = [];
        foreach ($letters as $letter) {
            $data[] = [
                'id' => $letter['id'],
                'kode_unik' => esc($letter['kode_unik'] ?? '-'),
                'judul_perihal' => esc($letter['judul_perihal']),
                'tipe_surat' => esc($letter['tipe_surat']),
                'status' => esc($letter['status']),
                'sent_at' => date('d M Y H:i', strtotime($letter['sent_at'])),
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            csrf_token() => csrf_hash()
        ]);
    }

    public function create()
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        return view('User/letters/form');
    }

    public function store()
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        // Cek kelengkapan data diri (Jenis Kelamin)
        $profileModel = new \App\Models\UserProfileModel();
        $userProfile = $profileModel->find($this->currentUser['id']);

        if (empty($userProfile['jenis_kelamin'])) {
            return redirect()->to('/user/profil')->with('error', 'Mohon lengkapi data jenis kelamin Anda sebelum membuat surat.');
        }

        // Validasi jenis surat
        $allowedTypes = [
            'Keterangan Usaha',
            'Keterangan Tidak Mampu',
            'Keterangan Belum Menikah',
            'Keterangan Domisili',
            'Undangan',
            'Lain Lain'
        ];
        
        $tipeSurat = $this->request->getPost('tipe_surat');
        if (!in_array($tipeSurat, $allowedTypes)) {
            return redirect()->back()->withInput()->with('error', 'Jenis surat tidak valid.');
        }

        $validationRules = [
            'judul_perihal' => 'required|max_length[150]',
            'isi_surat'     => 'required|max_length[3000]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', 'Karakter Judul/Perihal atau Isi Surat melebihi batas maksimal yang diizinkan (Judul: 150, Isi: 3000).');
        }

        $letterModel = new LetterModel();
        
        // Generate kode unik
        $kodeUnik = $this->generateKodeUnik($letterModel);
        
        $data        = [
            'kode_unik'    => $kodeUnik,
            'user_id'   => $this->currentUser['id'],
            'judul_perihal' => $this->request->getPost('judul_perihal'),
            'tipe_surat'    => $tipeSurat,
            'isi_surat'     => $this->request->getPost('isi_surat'),
            'status'        => 'Menunggu',
            'sent_at'       => date('Y-m-d H:i:s'),
        ];

        $letterId = $letterModel->insert($data, true);
        $this->handleAttachments($letterId);

        // Buat notifikasi untuk semua staff
        $userModel = new UserModel();
        $profileModel = new \App\Models\UserProfileModel();
        $staffList = $userModel->where('role', 'staf')->findAll();
        $notifModel = new NotificationModel();
        $emailService = new \App\Libraries\EmailService();
        
        // Ambil nama user (nama_lengkap dari profile, fallback ke username)
        $userProfile = $profileModel->find($this->currentUser['id']);
        $userName = ($userProfile && !empty($userProfile['nama_lengkap'])) 
            ? $userProfile['nama_lengkap'] 
            : $this->currentUser['username'];
        
        $letterUrl = base_url('/staff/surat/' . $letterId);
        
        foreach ($staffList as $staff) {
            $notifModel->insert([
                'user_id'           => $staff['id'],
                'type'              => 'new_letter',
                'title'             => 'Surat Baru Masuk',
                'message'           => 'Surat baru dari ' . $userName,
                'related_letter_id' => $letterId,
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
            
            // Kirim email notifikasi ke staff
            $emailService->sendNotification(
                $staff['email'],
                $staff['username'],
                'Surat Baru Masuk',
                'Surat baru dari ' . htmlspecialchars($userName),
                'new_letter',
                $letterUrl,
                $data['judul_perihal'],
                $data['tipe_surat']
            );
        }

        return redirect()->to('/user/surat')->with('success', 'Surat berhasil dikirim.');
    }

    public function show($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $letterModel       = new LetterModel();
        $attachmentModel   = new LetterAttachmentModel();
        $replyModel        = new LetterReplyModel();
        $replyAttachModel  = new ReplyAttachmentModel();

        $letter = $letterModel->where('user_id', $this->currentUser['id'])->find($id);
        if (!$letter) {
            return redirect()->to('/user/surat')->with('error', 'Surat tidak ditemukan.');
        }

        $userModel = new \App\Models\UserModel();
        $profileModel = new \App\Models\UserProfileModel();
        
        $replies = $replyModel->where('letter_id', $id)->findAll();
        $replyAttachments = [];
        $replyProfiles = [];
        
        foreach ($replies as $reply) {
            $replyAttachments[$reply['id']] = $replyAttachModel->where('reply_id', $reply['id'])->findAll();
            
            // Get staff profile for reply
            if (!empty($reply['staff_id'])) {
                $staffProfile = $profileModel->find($reply['staff_id']);
                $staffUser = $userModel->find($reply['staff_id']);
                
                $replyProfiles[$reply['id']] = [
                    'foto_profil' => $staffProfile['foto_profil'] ?? null,
                    'nama_lengkap' => $staffProfile['nama_lengkap'] ?? null,
                    'username' => $staffUser['username'] ?? 'Staff',
                ];
            }
        }

        return view('User/letters/detail', [
            'letter'           => $letter,
            'attachments'      => $attachmentModel->where('letter_id', $id)->findAll(),
            'replies'          => $replies,
            'replyAttachments' => $replyAttachments,
            'replyProfiles'    => $replyProfiles,
        ]);
    }

    public function edit($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $letter      = $letterModel->where('user_id', $this->currentUser['id'])->find($id);

        if (!$letter) {
            return redirect()->to('/user/surat')->with('error', 'Surat tidak ditemukan.');
        }

        if ($letter['status'] !== 'Menunggu') {
            return redirect()->to('/user/surat')->with('error', 'Surat yang sudah diproses tidak dapat diedit lagi.');
        }

        return view('User/letters/form', ['letter' => $letter]);
    }

    public function update($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        // Validasi jenis surat
        $allowedTypes = [
            'Keterangan Usaha',
            'Keterangan Tidak Mampu',
            'Keterangan Belum Menikah',
            'Keterangan Domisili',
            'Undangan',
            'Lain Lain'
        ];
        
        $tipeSurat = $this->request->getPost('tipe_surat');
        if (!in_array($tipeSurat, $allowedTypes)) {
            return redirect()->back()->withInput()->with('error', 'Jenis surat tidak valid.');
        }

        $validationRules = [
            'judul_perihal' => 'required|max_length[150]',
            'isi_surat'     => 'required|max_length[3000]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', 'Karakter Judul/Perihal atau Isi Surat melebihi batas maksimal yang diizinkan (Judul: 150, Isi: 3000).');
        }

        $letterModel = new LetterModel();
        $letter      = $letterModel->where('user_id', $this->currentUser['id'])->find($id);

        if (!$letter) {
            return redirect()->to('/user/surat')->with('error', 'Surat tidak ditemukan.');
        }

        if ($letter['status'] !== 'Menunggu') {
            return redirect()->to('/user/surat')->with('error', 'Surat yang sudah diproses tidak dapat diedit lagi.');
        }

        $letterModel->update($id, [
            'judul_perihal' => $this->request->getPost('judul_perihal'),
            'tipe_surat'    => $tipeSurat,
            'isi_surat'     => $this->request->getPost('isi_surat'),
        ]);

        $this->handleAttachments($id);

        return redirect()->to('/user/surat/' . $id)->with('success', 'Surat diperbarui.');
    }

    public function delete($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $attachmentModel = new LetterAttachmentModel();
        $replyModel = new LetterReplyModel();
        $replyAttachModel = new ReplyAttachmentModel();
        $notificationModel = new NotificationModel();

        $letter = $letterModel->where('user_id', $this->currentUser['id'])->find($id);
        if (!$letter) {
            return redirect()->to('/user/surat')->with('error', 'Surat tidak ditemukan.');
        }

        // Hapus lampiran surat
        $attachments = $attachmentModel->where('letter_id', $id)->findAll();
        foreach ($attachments as $att) {
            $filePath = WRITEPATH . 'uploads/letters/' . basename($att['file_path']);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        $attachmentModel->where('letter_id', $id)->delete();

        // Hapus balasan dan lampiran balasan
        $replies = $replyModel->where('letter_id', $id)->findAll();
        foreach ($replies as $reply) {
            // Hapus lampiran balasan
            $replyAttachments = $replyAttachModel->where('reply_id', $reply['id'])->findAll();
            foreach ($replyAttachments as $replyAtt) {
                $filePath = WRITEPATH . 'uploads/replies/' . basename($replyAtt['file_path']);
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
            $replyAttachModel->where('reply_id', $reply['id'])->delete();
        }
        $replyModel->where('letter_id', $id)->delete();

        // Hapus notifikasi terkait
        $notificationModel->where('related_letter_id', $id)->delete();

        // Hapus surat
        $letterModel->delete($id);

        return redirect()->to('/user/surat')->with('success', 'Surat berhasil dihapus.');
    }

    private function handleAttachments(int $letterId): void
    {
        $files = $this->request->getFileMultiple('attachments');
        if (!$files) {
            return;
        }

        $uploadPath      = WRITEPATH . 'uploads/letters';
        $attachmentModel = new LetterAttachmentModel();
        $this->ensureUploadPath($uploadPath);

        // Batasi maksimal 5 file
        if (count($files) > 5) {
            session()->setFlashdata('error', 'Maksimal lampiran yang diperbolehkan adalah 5 file. Beberapa file mungkin tidak tersimpan.');
            $files = array_slice($files, 0, 5);
        }

        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }

            // 1. Validasi ukuran file (maks 1 MB)
            if ($file->getSize() > 1048576) {
                session()->setFlashdata('error', 'Terdapat lampiran yang melebihi batas 1MB atau format tidak didukung dan gagal diunggah.');
                continue;
            }

            // 2. Ambil nama file asli dan sanitasi
            $clientName = $file->getClientName();

            // 2a. Cegah null byte injection
            if (strpos($clientName, "\0") !== false) {
                session()->setFlashdata('error', 'Nama file tidak valid.');
                continue;
            }

            // 2b. Cegah path traversal
            if (basename($clientName) !== $clientName) {
                session()->setFlashdata('error', 'Nama file tidak valid.');
                continue;
            }

            // 2c. Cegah karakter berbahaya dalam nama file
            if (preg_match('/[<>:"\/\\\\|?*\x00-\x1F]/', $clientName)) {
                session()->setFlashdata('error', 'Nama file mengandung karakter yang tidak diperbolehkan.');
                continue;
            }

            // 2d. Cegah Unicode RTL override spoofing (\u202E, \u202B, \u202D, \u200F)
            if (preg_match('/[\x{200F}\x{202E}\x{202B}\x{202D}]/u', $clientName)) {
                session()->setFlashdata('error', 'Nama file mengandung karakter tidak valid.');
                continue;
            }

            // 3. Cek ekstensi ganda berbahaya (misal: shell.php.jpg)
            $nameParts     = explode('.', $clientName);
            $dangerousExts = [
                'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar',
                'asp', 'aspx', 'jsp', 'exe', 'sh', 'bat', 'cmd', 'py',
                'rb', 'pl', 'cgi', 'htaccess', 'htpasswd', 'svg',
                'shtml', 'pht', // Apache alternative PHP/SSI extensions
            ];
            $hasDoubleExt = false;
            if (count($nameParts) > 2) {
                for ($i = 0; $i < count($nameParts) - 1; $i++) {
                    if (in_array(strtolower($nameParts[$i]), $dangerousExts)) {
                        $hasDoubleExt = true;
                        break;
                    }
                }
            }
            if ($hasDoubleExt) {
                session()->setFlashdata('error', 'Terdapat lampiran yang melebihi batas 1MB atau format tidak didukung dan gagal diunggah.');
                continue;
            }

            // 4. Cek ekstensi akhir
            $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                session()->setFlashdata('error', 'Terdapat lampiran yang melebihi batas 1MB atau format tidak didukung dan gagal diunggah.');
                continue;
            }

            // 5. Whitelist MIME type dari header request (lapisan tambahan)
            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/webp',
            ];
            $clientMime = $file->getClientMimeType();
            if (!in_array($clientMime, $allowedMimes)) {
                session()->setFlashdata('error', 'Terdapat lampiran yang melebihi batas 1MB atau format tidak didukung dan gagal diunggah.');
                continue;
            }

            // 6. Validasi Magic Bytes — baca 12 byte pertama dari isi file
            $tmpPath  = $file->getTempName();
            $fh       = @fopen($tmpPath, 'rb');
            $header   = $fh ? fread($fh, 12) : '';
            if ($fh) fclose($fh);

            $magicMap = [
                'pdf'  => ["\x25\x50\x44\x46"],                    // %PDF
                'jpg'  => ["\xFF\xD8\xFF"],                         // JPEG SOI
                'jpeg' => ["\xFF\xD8\xFF"],
                'png'  => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],   // PNG
                'webp' => ["RIFF"],                                  // RIFF (WebP)
                'doc'  => ["\xD0\xCF\x11\xE0"],                    // OLE2
                'docx' => ["\x50\x4B\x03\x04"],                    // PK zip
            ];

            if (isset($magicMap[$ext])) {
                $magicOk = false;
                foreach ($magicMap[$ext] as $magic) {
                    if (str_starts_with($header, $magic)) {
                        $magicOk = true;
                        break;
                    }
                }
                if (!$magicOk) {
                    session()->setFlashdata('error', 'Terdapat lampiran yang melebihi batas 1MB atau format tidak didukung dan gagal diunggah.');
                    continue;
                }
            }

            // 7. Scan konten file untuk pola skrip berbahaya
            // Scan HANYA header (1024 byte pertama) + trailer (512 byte terakhir)
            // untuk menghindari false positive dari binary EXIF/ICC profile di tengah gambar.
            $fileContent = @file_get_contents($tmpPath);
            if ($fileContent === false) {
                session()->setFlashdata('error', 'Gagal membaca lampiran.');
                continue;
            }
            $fSize      = strlen($fileContent);
            $scanTarget = substr($fileContent, 0, 1024) . ($fSize > 1024 ? substr($fileContent, -512) : '');

            // Pola inti yang berbahaya di semua tipe file
            $dangerousPatterns = [
                '/\<\?php/i',
                '/\<\?=/i',
                '/<script[\s>]/i',
                '/eval\s*\(/i',
                '/exec\s*\(/i',
                '/system\s*\(/i',
                '/passthru\s*\(/i',
                '/shell_exec\s*\(/i',
                '/base64_decode\s*\(/i',
                '/preg_replace\s*\(.*\/e/i',
                '/assert\s*\(/i',
                '/create_function\s*\(/i',
                '/call_user_func(?:_array)?\s*\(/i',
                '/file_put_contents\s*\(/i',
                '/str_rot13\s*\(/i',
                '/phar:\/\//i',
                '/javascript:/i',
                '/vbscript:/i',
            ];

            // Pola tambahan yang HANYA aman dicek untuk file non-Office
            // (docx/doc adalah ZIP berisi OOXML — XML-nya secara legitimate
            //  mengandung atribut on*= dan properti dengan pola mirip superglobal)
            $isOfficeFile = in_array($ext, ['doc', 'docx']);
            if (!$isOfficeFile) {
                $dangerousPatterns[] = '/\$_(?:GET|POST|REQUEST|COOKIE|SERVER|FILES|ENV)/i';
            }

            $dangerous = false;
            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $scanTarget)) {
                    $dangerous = true;
                    break;
                }
            }
            if ($dangerous) {
                session()->setFlashdata('error', 'Lampiran mengandung konten yang tidak diperbolehkan.');
                continue;
            }

            // 8. Validasi tambahan WEBP — pastikan chunk WEBP ada setelah header RIFF
            if ($ext === 'webp') {
                // Format WEBP: RIFF[4 bytes ukuran]WEBP
                if (strlen($fileContent) < 12 || substr($fileContent, 8, 4) !== 'WEBP') {
                    session()->setFlashdata('error', 'Terdapat lampiran yang melebihi batas 1MB atau format tidak didukung dan gagal diunggah.');
                    continue;
                }
            }

            // 9. Semua validasi lulus — simpan file
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);

            // Gunakan MIME yang terdeteksi PHP (bukan dari client) untuk penyimpanan
            $safeMime = mime_content_type($uploadPath . '/' . $newName) ?: $clientMime;

            $attachmentModel->insert([
                'letter_id'     => $letterId,
                'file_path'     => 'letters/' . $newName,
                'original_name' => basename($clientName),
                'mime_type'     => $safeMime,
                'file_size'     => $file->getSize(),
            ]);
        }
    }

    /**
     * Generate kode unik untuk surat
     * Format: SURAT-YYYYMMDD-XXXXXX (6 digit random)
     */
    private function generateKodeUnik(LetterModel $letterModel): string
    {
        $prefix = 'SURAT-' . date('Ymd') . '-';
        $maxAttempts = 100;
        $attempt = 0;

        do {
            $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
            $kodeUnik = $prefix . $random;
            $exists = $letterModel->where('kode_unik', $kodeUnik)->first();
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        return $kodeUnik;
    }

    /**
     * Serve lampiran surat secara aman (hanya user pemilik surat)
     */
    public function serveAttachment($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $attachmentModel = new LetterAttachmentModel();
        $letterModel     = new LetterModel();

        $att = $attachmentModel->find($id);
        if (!$att) {
            return $this->response->setStatusCode(404);
        }

        // Pastikan lampiran ini milik surat user yang sedang login
        $letter = $letterModel->find($att['letter_id']);
        if (!$letter || $letter['user_id'] != $this->currentUser['id']) {
            return $this->response->setStatusCode(403);
        }

        $filePath = WRITEPATH . 'uploads/letters/' . basename($att['file_path']);
        if (!is_file($filePath)) {
            return $this->response->setStatusCode(404);
        }

        // Gunakan deteksi MIME nyata dari file (bukan dari data tersimpan)
        $detectedMime = mime_content_type($filePath) ?: 'application/octet-stream';

        // Sanitasi nama file untuk header
        $safeOriginalName = preg_replace('/[^\w\s.\-]/', '_', $att['original_name']);

        return $this->response
            ->setHeader('Content-Type', $detectedMime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $safeOriginalName . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Security-Policy', "default-src 'none'")
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Serve lampiran balasan surat secara aman (hanya user pemilik surat)
     */
    public function serveReplyAttachment($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $replyAttachModel = new ReplyAttachmentModel();
        $replyModel       = new LetterReplyModel();
        $letterModel      = new LetterModel();

        $att = $replyAttachModel->find($id);
        if (!$att) {
            return $this->response->setStatusCode(404);
        }

        // Pastikan lampiran balasan ini terhubung ke surat milik user
        $reply  = $replyModel->find($att['reply_id']);
        $letter = $reply ? $letterModel->find($reply['letter_id']) : null;
        if (!$letter || $letter['user_id'] != $this->currentUser['id']) {
            return $this->response->setStatusCode(403);
        }

        $filePath = WRITEPATH . 'uploads/replies/' . basename($att['file_path']);
        if (!is_file($filePath)) {
            return $this->response->setStatusCode(404);
        }

        // Gunakan deteksi MIME nyata dari file (bukan dari data tersimpan)
        $detectedMime = mime_content_type($filePath) ?: 'application/octet-stream';

        // Sanitasi nama file untuk header
        $safeOriginalName = preg_replace('/[^\w\s.\-]/', '_', $att['original_name']);

        return $this->response
            ->setHeader('Content-Type', $detectedMime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $safeOriginalName . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Security-Policy', "default-src 'none'")
            ->setBody(file_get_contents($filePath));
    }
}


