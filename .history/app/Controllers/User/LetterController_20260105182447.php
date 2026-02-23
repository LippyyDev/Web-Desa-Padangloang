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
        $draw = $this->request->getGet('draw') ?? 1;
        $start = $this->request->getGet('start') ?? 0;
        $length = $this->request->getGet('length') ?? 10;
        $search = $this->request->getGet('search')['value'] ?? '';
        $searchCustom = $this->request->getGet('search_custom') ?? '';
        $orderColumn = $this->request->getGet('order')[0]['column'] ?? 3;
        $orderDir = $this->request->getGet('order')[0]['dir'] ?? 'desc';
        
        // Get filter parameters
        $dateStart = $this->request->getGet('date_start') ?? '';
        $dateEnd = $this->request->getGet('date_end') ?? '';
        $tipeSuratFilter = $this->request->getGet('tipe_surat_filter') ?? '';
        $statusFilter = $this->request->getGet('status_filter') ?? '';
        
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
                'kode_unik' => $letter['kode_unik'] ?? '-',
                'judul_perihal' => $letter['judul_perihal'],
                'tipe_surat' => $letter['tipe_surat'],
                'status' => $letter['status'],
                'sent_at' => date('d M Y H:i', strtotime($letter['sent_at'])),
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

        $letterModel = new LetterModel();
        
        // Generate kode unik
        $kodeUnik = $this->generateKodeUnik($letterModel);
        
        $data        = [
            'kode_unik'    => $kodeUnik,
            'user_id'   => $this->currentUser['id'],
            'judul_perihal' => $this->request->getPost('judul_perihal'),
            'tipe_surat'    => $tipeSurat,
            'isi_surat'     => $this->request->getPost('isi_surat'),
            'status'        => 'Terkirim',
            'sent_at'       => date('Y-m-d H:i:s'),
        ];

        $letterId = $letterModel->insert($data, true);
        $this->handleAttachments($letterId);

        // Buat notifikasi untuk semua staff
        $userModel = new UserModel();
        $staffList = $userModel->where('role', 'staf')->findAll();
        $notifModel = new NotificationModel();
        
        foreach ($staffList as $staff) {
            $notifModel->insert([
                'user_id'           => $staff['id'],
                'type'              => 'new_letter',
                'title'             => 'Surat Baru Masuk',
                'message'           => 'Surat baru dari user: ' . $data['judul_perihal'],
                'related_letter_id' => $letterId,
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
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

        $letterModel = new LetterModel();
        $letter      = $letterModel->where('user_id', $this->currentUser['id'])->find($id);

        if (!$letter) {
            return redirect()->to('/user/surat')->with('error', 'Surat tidak ditemukan.');
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
            $filePath = FCPATH . ltrim($att['file_path'], '/');
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
                $filePath = FCPATH . ltrim($replyAtt['file_path'], '/');
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

        $uploadPath      = FCPATH . 'uploads/letters';
        $attachmentModel = new LetterAttachmentModel();
        $this->ensureUploadPath($uploadPath);

        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);

            $attachmentModel->insert([
                'letter_id'     => $letterId,
                'file_path'     => 'uploads/letters/' . $newName,
                'original_name' => $file->getClientName(),
                'mime_type'     => $file->getClientMimeType(),
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

    public function generateWord($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $profileModel = new UserProfileModel();
        
        $letter = $letterModel->where('user_id', $this->currentUser['id'])->find($id);
        if (!$letter) {
            return redirect()->to('/user/surat')->with('error', 'Surat tidak ditemukan.');
        }

        $profile = $profileModel->find($this->currentUser['id']);
        if (!$profile) {
            return redirect()->to('/user/surat')->with('error', 'Profil tidak ditemukan.');
        }

        // Format tanggal Indonesia
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalSekarang = date('d') . ' ' . $bulan[(int)date('m') - 1] . ' ' . date('Y');
        
        // Format tanggal lahir
        $tanggalLahir = '-';
        if (!empty($profile['tanggal_lahir'])) {
            $tglLahir = date_create($profile['tanggal_lahir']);
            $tanggalLahir = date('d', $tglLahir->getTimestamp()) . ' ' . $bulan[(int)date('m', $tglLahir->getTimestamp()) - 1] . ' ' . date('Y', $tglLahir->getTimestamp());
        }
        $tempatTanggalLahir = (!empty($profile['tempat_lahir']) ? $profile['tempat_lahir'] : '-') . ' / ' . $tanggalLahir;

        // Judul surat berdasarkan tipe
        $judulSurat = 'SURAT PERMOHONAN ' . strtoupper($letter['tipe_surat']);

        // Create new PHPWord object
        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 1134,    // 2 cm
            'marginBottom' => 1134, // 2 cm
            'marginLeft' => 1134,   // 2 cm
            'marginRight' => 1134,  // 2 cm
        ]);

        // Header dengan logo dan teks
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        $headerRow = $headerTable->addRow();
        
        // Logo cell (kiri)
        $logoCell = $headerRow->addCell(2000);
        $logoPath = FCPATH . 'assets/img/logolandscape.webp';
        if (file_exists($logoPath)) {
            $logoCell->addImage($logoPath, [
                'width' => 60,
                'height' => 60,
                'alignment' => 'left',
            ]);
        }
        
        // Teks header (tengah)
        $textCell = $headerRow->addCell(10000);
        $textCell->addText('PEMERINTAH KABUPATEN BULUKUMBA', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $textCell->addText('KECAMATAN UJUNGLOE', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $textCell->addText('DESA PADANGLOANG', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $textCell->addText('Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', ['size' => 10], ['alignment' => 'center']);
        
        // Garis pemisah
        $section->addText('', [], ['spaceAfter' => 120]);
        $section->addLine(['width' => 100, 'height' => 0, 'positioning' => 'absolute']);

        // Judul surat
        $section->addText('', [], ['spaceAfter' => 240]);
        $section->addText($judulSurat, ['bold' => true, 'size' => 14, 'underline' => 'single'], ['alignment' => 'center', 'spaceAfter' => 360]);

        // Kepada Yth.
        $section->addText('Kepada Yth.', [], ['spaceAfter' => 120]);
        $section->addText('Bapak Kepala Desa Padangloang dan Staf Desa Padangloang', [], ['spaceAfter' => 120]);
        $section->addText('di', [], ['spaceAfter' => 120]);
        $section->addText('Tempat', [], ['spaceAfter' => 360]);

        // Dengan hormat
        $section->addText('Dengan hormat,', [], ['spaceAfter' => 360]);

        // Yang bertanda tangan
        $section->addText('Yang bertanda tangan di bawah ini:', [], ['spaceAfter' => 240]);

        // Data profil
        $dataTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 50,
        ]);
        
        $dataTable->addRow();
        $dataTable->addCell(3000)->addText('Nama', [], []);
        $dataTable->addCell(200)->addText(':', [], []);
        $dataTable->addCell(7000)->addText($profile['nama_lengkap'] ?? '-', [], []);
        
        $dataTable->addRow();
        $dataTable->addCell(3000)->addText('Tempat/ Tanggal Lahir', [], []);
        $dataTable->addCell(200)->addText(':', [], []);
        $dataTable->addCell(7000)->addText($tempatTanggalLahir, [], []);
        
        $dataTable->addRow();
        $dataTable->addCell(3000)->addText('Jenis Kelamin', [], []);
        $dataTable->addCell(200)->addText(':', [], []);
        $dataTable->addCell(7000)->addText('-', [], []);
        
        $dataTable->addRow();
        $dataTable->addCell(3000)->addText('Alamat', [], []);
        $dataTable->addCell(200)->addText(':', [], []);
        $dataTable->addCell(7000)->addText($profile['alamat'] ?? '-', [], []);
        
        $dataTable->addRow();
        $dataTable->addCell(3000)->addText('No NIK', [], []);
        $dataTable->addCell(200)->addText(':', [], []);
        $dataTable->addCell(7000)->addText($profile['nik'] ?? '-', [], []);

        $section->addText('', [], ['spaceAfter' => 240]);

        // Permohonan
        $section->addText('Dengan ini mengajukan permohonan pembuatan Surat ' . $letter['tipe_surat'] . '.', [], ['spaceAfter' => 360]);

        // Isi surat
        $section->addText($letter['isi_surat'], [], ['spaceAfter' => 360]);

        // Penutup
        $section->addText('Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.', [], ['spaceAfter' => 720]);

        // Tanda tangan
        $section->addText('Padangloang, ' . $tanggalSekarang, [], ['spaceAfter' => 480]);
        $section->addText('', [], ['spaceAfter' => 240]);
        $section->addText($profile['nama_lengkap'] ?? '-', [], []);

        // Save file
        $filename = 'Surat_' . str_replace(' ', '_', $letter['tipe_surat']) . '_' . $letter['kode_unik'] . '.docx';
        $filepath = WRITEPATH . 'uploads/temp/' . $filename;
        
        if (!is_dir(WRITEPATH . 'uploads/temp')) {
            mkdir(WRITEPATH . 'uploads/temp', 0755, true);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filepath);

        return $this->response->download($filepath, null)->setFileName($filename);
    }

    public function generatePDF($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $profileModel = new UserProfileModel();
        
        $letter = $letterModel->where('user_id', $this->currentUser['id'])->find($id);
        if (!$letter) {
            return redirect()->to('/user/surat')->with('error', 'Surat tidak ditemukan.');
        }

        $profile = $profileModel->find($this->currentUser['id']);
        if (!$profile) {
            return redirect()->to('/user/surat')->with('error', 'Profil tidak ditemukan.');
        }

        // Format tanggal Indonesia
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalSekarang = date('d') . ' ' . $bulan[(int)date('m') - 1] . ' ' . date('Y');
        
        // Format tanggal lahir
        $tanggalLahir = '-';
        if (!empty($profile['tanggal_lahir'])) {
            $tglLahir = date_create($profile['tanggal_lahir']);
            $tanggalLahir = date('d', $tglLahir->getTimestamp()) . ' ' . $bulan[(int)date('m', $tglLahir->getTimestamp()) - 1] . ' ' . date('Y', $tglLahir->getTimestamp());
        }
        $tempatTanggalLahir = (!empty($profile['tempat_lahir']) ? $profile['tempat_lahir'] : '-') . ' / ' . $tanggalLahir;

        // Judul surat berdasarkan tipe
        $judulSurat = 'SURAT PERMOHONAN ' . strtoupper($letter['tipe_surat']);

        // Create PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Desa Padang Loang');
        $pdf->SetAuthor('Sistem Desa');
        $pdf->SetTitle($judulSurat);
        $pdf->SetSubject('Surat Permohonan');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(TRUE, 20);

        // Add a page
        $pdf->AddPage();

        // Header dengan logo dan teks
        $logoPath = FCPATH . 'assets/img/logolandscape.webp';
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/webp;base64,' . $logoData . '" style="width: 60px; height: 60px;">';
        }

        $headerHtml = '<table border="0" cellpadding="5" cellspacing="0" width="100%">
            <tr>
                <td width="80" valign="top">' . $logoHtml . '</td>
                <td align="center">
                    <div style="font-weight: bold; font-size: 12pt;">PEMERINTAH KABUPATEN BULUKUMBA</div>
                    <div style="font-weight: bold; font-size: 12pt;">KECAMATAN UJUNGLOE</div>
                    <div style="font-weight: bold; font-size: 12pt;">DESA PADANGLOANG</div>
                    <div style="font-size: 10pt;">Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba</div>
                </td>
                <td width="80"></td>
            </tr>
        </table>
        <hr style="border: 1px solid #000;">';

        $pdf->writeHTML($headerHtml, true, false, true, false, '');

        // Judul surat
        $pdf->Ln(10);
        $pdf->SetFont('times', 'BU', 14);
        $pdf->Cell(0, 10, $judulSurat, 0, 1, 'C');
        $pdf->Ln(5);

        // Kepada Yth.
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(0, 6, 'Kepada Yth.', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Bapak Kepala Desa Padangloang dan Staf Desa Padangloang', 0, 1, 'L');
        $pdf->Cell(0, 6, 'di', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Tempat', 0, 1, 'L');
        $pdf->Ln(5);

        // Dengan hormat
        $pdf->Cell(0, 6, 'Dengan hormat,', 0, 1, 'L');
        $pdf->Ln(3);

        // Yang bertanda tangan
        $pdf->Cell(0, 6, 'Yang bertanda tangan di bawah ini:', 0, 1, 'L');
        $pdf->Ln(3);

        // Data profil
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(60, 6, 'Nama', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'L');
        $pdf->Cell(0, 6, $profile['nama_lengkap'] ?? '-', 0, 1, 'L');
        
        $pdf->Cell(60, 6, 'Tempat/ Tanggal Lahir', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'L');
        $pdf->Cell(0, 6, $tempatTanggalLahir, 0, 1, 'L');
        
        $pdf->Cell(60, 6, 'Jenis Kelamin', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'L');
        $pdf->Cell(0, 6, '-', 0, 1, 'L');
        
        $pdf->Cell(60, 6, 'Alamat', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'L');
        $pdf->Cell(0, 6, $profile['alamat'] ?? '-', 0, 1, 'L');
        
        $pdf->Cell(60, 6, 'No NIK', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'L');
        $pdf->Cell(0, 6, $profile['nik'] ?? '-', 0, 1, 'L');
        $pdf->Ln(3);

        // Permohonan
        $pdf->Cell(0, 6, 'Dengan ini mengajukan permohonan pembuatan Surat ' . $letter['tipe_surat'] . '.', 0, 1, 'L');
        $pdf->Ln(3);

        // Isi surat
        $pdf->MultiCell(0, 6, $letter['isi_surat'], 0, 'L', false, 1, '', '', true);
        $pdf->Ln(3);

        // Penutup
        $pdf->Cell(0, 6, 'Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.', 0, 1, 'L');
        $pdf->Ln(10);

        // Tanda tangan
        $pdf->Cell(0, 6, 'Padangloang, ' . $tanggalSekarang, 0, 1, 'R');
        $pdf->Ln(8);
        $pdf->Cell(0, 6, $profile['nama_lengkap'] ?? '-', 0, 1, 'R');

        // Output PDF
        $filename = 'Surat_' . str_replace(' ', '_', $letter['tipe_surat']) . '_' . $letter['kode_unik'] . '.pdf';
        $pdf->Output($filename, 'D');
    }
}


