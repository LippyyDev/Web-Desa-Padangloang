<?php

namespace App\Controllers\User;

use App\Controllers\ProtectedController;
use App\Models\LetterModel;
use App\Models\UserProfileModel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;
use TCPDF;

class PdfWordController extends ProtectedController
{
    /**
     * Helper function untuk format tempat/tanggal lahir dengan pesan default
     */
    private function formatTempatTanggalLahir($profile, $bulan)
    {
        $tanggalLahir = '';
        if (!empty($profile['tanggal_lahir'])) {
            $tglLahir = date_create($profile['tanggal_lahir']);
            $tanggalLahir = date('d', $tglLahir->getTimestamp()) . ' ' . $bulan[(int)date('m', $tglLahir->getTimestamp()) - 1] . ' ' . date('Y', $tglLahir->getTimestamp());
        }
        
        $tempatLahir = !empty($profile['tempat_lahir']) ? $profile['tempat_lahir'] : '';
        if (empty($tempatLahir) && empty($tanggalLahir)) {
            return 'Belum Diisi Pada Profil';
        } elseif (empty($tempatLahir)) {
            return 'Belum Diisi Pada Profil / ' . $tanggalLahir;
        } elseif (empty($tanggalLahir)) {
            return $tempatLahir . ' / Belum Diisi Pada Profil';
        } else {
            return $tempatLahir . ' / ' . $tanggalLahir;
        }
    }
    
    /**
     * Helper function untuk mendapatkan nilai dengan pesan default
     */
    private function getProfileValue($profile, $key, $defaultMessage = 'Belum Diisi Pada Profil')
    {
        return !empty($profile[$key]) ? $profile[$key] : $defaultMessage;
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
        $tempatTanggalLahir = $this->formatTempatTanggalLahir($profile, $bulan);

        // Judul surat berdasarkan tipe
        $judulSurat = 'SURAT PERMOHONAN ' . strtoupper($letter['tipe_surat']);

        // Create new PHPWord object
        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 1134,
            'marginBottom' => 1134,
            'marginLeft' => 1417,
            'marginRight' => 1134,
            'pageSizeW' => 12240,   // 21.5 cm
            'pageSizeH' => 18810,   // 33 cm
        ]);

        // Header dengan logo dan teks
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        ]);
        $headerRow = $headerTable->addRow();
        
        $logoCell = $headerRow->addCell(1200, [
            'valign' => 'center',
            'borderTopSize' => 0,
            'borderTopColor' => 'FFFFFF',
            'borderBottomSize' => 0,
            'borderBottomColor' => 'FFFFFF',
            'borderLeftSize' => 0,
            'borderLeftColor' => 'FFFFFF',
            'borderRightSize' => 0,
            'borderRightColor' => 'FFFFFF',
        ]);
        $logoPath = FCPATH . 'assets/img/logo.webp';
        if (file_exists($logoPath)) {
            $tempPngPath = WRITEPATH . 'uploads/temp/logo_word_temp.png';
            if (!is_dir(WRITEPATH . 'uploads/temp')) {
                mkdir(WRITEPATH . 'uploads/temp', 0755, true);
            }
            
            if (function_exists('imagecreatefromwebp')) {
                $image = imagecreatefromwebp($logoPath);
                if ($image !== false) {
                    $origWidth = imagesx($image);
                    $origHeight = imagesy($image);
                    $size = max($origWidth, $origHeight);
                    $squareImage = imagecreatetruecolor($size, $size);
                    imagealphablending($squareImage, false);
                    imagesavealpha($squareImage, true);
                    $transparent = imagecolorallocatealpha($squareImage, 0, 0, 0, 127);
                    imagefill($squareImage, 0, 0, $transparent);
                    $x = ($size - $origWidth) / 2;
                    $y = ($size - $origHeight) / 2;
                    imagealphablending($squareImage, true);
                    imagecopy($squareImage, $image, $x, $y, 0, 0, $origWidth, $origHeight);
                    imagealphablending($squareImage, false);
                    imagesavealpha($squareImage, true);
                    imagepng($squareImage, $tempPngPath);
                    imagedestroy($image);
                    imagedestroy($squareImage);
                    $logoPath = $tempPngPath;
                }
            }
            
            $logoCell->addImage($logoPath, [
                'width' => 68,
                'height' => 68,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START,
                'wrappingStyle' => 'inline',
                'positioning' => 'relative',
            ]);
        }
        
        $textCell = $headerRow->addCell(8800, [
            'valign' => 'center',
            'borderTopSize' => 0,
            'borderTopColor' => 'FFFFFF',
            'borderBottomSize' => 0,
            'borderBottomColor' => 'FFFFFF',
            'borderLeftSize' => 0,
            'borderLeftColor' => 'FFFFFF',
            'borderRightSize' => 0,
            'borderRightColor' => 'FFFFFF',
        ]);
        $textCell->addText('PEMERINTAH KABUPATEN BULUKUMBA', 
            ['bold' => true, 'size' => 16, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('KECAMATAN UJUNGLOE', 
            ['bold' => true, 'size' => 16, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('DESA PADANGLOANG', 
            ['bold' => true, 'size' => 14, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', 
            ['size' => 11, 'name' => 'Arial', 'italic' => true], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100]);
        
        $section->addLine([
            'weight' => 1,
            'width' => 450,
            'height' => 0,
        ]);
        $section->addTextBreak(0.5);

        $section->addText($judulSurat, 
            ['bold' => true, 'size' => 12, 'underline' => 'single', 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 180]);

        $section->addText('Kepada Yth.', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 0]);
        $section->addText('Bapak Kepala Desa Padangloang dan Staf Desa Padangloang', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 0]);
        $section->addText('di', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 0]);
        $section->addText('Tempat', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 240]);

        $section->addText('Dengan hormat,', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 240]);

        $section->addText('Yang bertanda tangan di bawah ini:', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data profil dengan cellMargin 0 dan spaceAfter 0
        $dataTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $cellStyleNoBorder = [
            'borderTopSize' => 0,
            'borderTopColor' => 'FFFFFF',
            'borderBottomSize' => 0,
            'borderBottomColor' => 'FFFFFF',
            'borderLeftSize' => 0,
            'borderLeftColor' => 'FFFFFF',
            'borderRightSize' => 0,
            'borderRightColor' => 'FFFFFF',
        ];
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($profile['nama_lengkap'] ?? '-', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/ Tanggal Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($tempatTanggalLahir, ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($profile, 'jenis_kelamin'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($profile, 'alamat'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('No NIK', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($profile, 'nik'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Permohonan tanpa 11 spasi
        $section->addText('Dengan ini mengajukan permohonan pembuatan Surat ' . $letter['tipe_surat'] . '.', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Isi surat tanpa 11 spasi
        $section->addText($letter['isi_surat'], 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Penutup tanpa 11 spasi
        $penutup = 'Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.';
        $section->addText($penutup, 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 360]);

        $section->addText('Padangloang, ' . $tanggalSekarang, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END, 'spaceAfter' => 1400]);
        
        $section->addText($profile['nama_lengkap'] ?? '-', 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

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

        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalSekarang = date('d') . ' ' . $bulan[(int)date('m') - 1] . ' ' . date('Y');
        $tempatTanggalLahir = $this->formatTempatTanggalLahir($profile, $bulan);
        $judulSurat = 'SURAT PERMOHONAN ' . strtoupper($letter['tipe_surat']);

        // PDF dengan custom size
        $pdf = new TCPDF('P', 'mm', array(215, 330), true, 'UTF-8', false);
        
        $pdf->SetCreator('Desa Padang Loang');
        $pdf->SetAuthor('Sistem Desa');
        $pdf->SetTitle($judulSurat);
        $pdf->SetSubject('Surat Permohonan');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(25, 20, 20);
        $pdf->SetAutoPageBreak(TRUE, 20);
        $pdf->AddPage();

        $logoPath = FCPATH . 'assets/img/logo.webp';
        
        if (file_exists($logoPath)) {
            $tempPngPath = WRITEPATH . 'uploads/temp/logo_pdf_temp.png';
            if (!is_dir(WRITEPATH . 'uploads/temp')) {
                mkdir(WRITEPATH . 'uploads/temp', 0755, true);
            }
            
            if (function_exists('imagecreatefromwebp') && function_exists('imagepng')) {
                $image = @imagecreatefromwebp($logoPath);
                if ($image !== false) {
                    $origWidth = imagesx($image);
                    $origHeight = imagesy($image);
                    $size = max($origWidth, $origHeight);
                    $newImage = imagecreatetruecolor($size, $size);
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                    imagefill($newImage, 0, 0, $transparent);
                    $x = ($size - $origWidth) / 2;
                    $y = ($size - $origHeight) / 2;
                    imagealphablending($newImage, true);
                    imagecopy($newImage, $image, $x, $y, 0, 0, $origWidth, $origHeight);
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    imagepng($newImage, $tempPngPath, 9);
                    imagedestroy($image);
                    imagedestroy($newImage);
                    $logoPath = $tempPngPath;
                    $logoType = 'PNG';
                } else {
                    $logoType = 'WEBP';
                }
            } else {
                $logoType = 'WEBP';
            }
            
            $pdf->Image($logoPath, 25, 18, 25, 25, $logoType, '', '', true, 300, '', false, false, 0, false, false, false);
        }
        
        $pdf->SetY(18);
        $pdf->SetX(55);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(135, 5, 'PEMERINTAH KABUPATEN BULUKUMBA', 0, 1, 'C');
        
        $pdf->SetX(55);
        $pdf->Cell(135, 5, 'KECAMATAN UJUNGLOE', 0, 1, 'C');
        
        $pdf->SetX(55);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(135, 5, 'DESA PADANGLOANG', 0, 1, 'C');
        
        $pdf->SetX(55);
        $pdf->SetFont('helvetica', 'I', 11);
        $pdf->Cell(135, 5, 'Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', 0, 1, 'C');
        
        $pdf->SetY(50);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(25, $pdf->GetY(), 190, $pdf->GetY());
        
        $pdf->SetY(57);
        $pdf->SetFont('helvetica', 'BU', 12);
        $pdf->Cell(0, 8, $judulSurat, 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 11);
        
        $pdf->Cell(0, 5, 'Kepada Yth.', 0, 1, 'L');
        $pdf->Cell(0, 5, 'Bapak Kepala Desa Padangloang dan Staf Desa Padangloang', 0, 1, 'L');
        $pdf->Cell(0, 5, 'di', 0, 1, 'L');
        $pdf->Cell(0, 5, 'Tempat', 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->Cell(0, 5, 'Dengan hormat,', 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->Cell(0, 5, 'Yang bertanda tangan di bawah ini:', 0, 1, 'L');
        $pdf->Ln(2);

        $colWidth1 = 60;
        $colWidth2 = 5;
        $colWidth3 = 95;
        
        $pdf->Cell($colWidth1, 5, 'Nama', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $profile['nama_lengkap'] ?? '-', 0, 1, 'L');
        
        $pdf->Cell($colWidth1, 5, 'Tempat/ Tanggal Lahir', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $tempatTanggalLahir, 0, 1, 'L');
        
        $pdf->Cell($colWidth1, 5, 'Jenis Kelamin', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $this->getProfileValue($profile, 'jenis_kelamin'), 0, 1, 'L');
        
        $pdf->Cell($colWidth1, 5, 'Alamat', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->MultiCell($colWidth3, 5, $this->getProfileValue($profile, 'alamat'), 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        
        $pdf->Cell($colWidth1, 5, 'No NIK', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $this->getProfileValue($profile, 'nik'), 0, 1, 'L');
        $pdf->Ln(2);

        // Tanpa 11 spasi
        $pdf->Cell(0, 5, 'Dengan ini mengajukan permohonan pembuatan Surat ' . $letter['tipe_surat'] . '.', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->MultiCell(0, 5, $letter['isi_surat'], 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        $pdf->Ln(2);

        $penutup = 'Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.';
        $pdf->MultiCell(0, 5, $penutup, 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        $pdf->Ln(8);

        $pdf->Cell(0, 5, 'Padangloang, ' . $tanggalSekarang, 0, 1, 'R');
        $pdf->Ln(20);
        $pdf->Cell(0, 5, $profile['nama_lengkap'] ?? '-', 0, 1, 'R');

        $filename = 'Surat_' . str_replace(' ', '_', $letter['tipe_surat']) . '_' . $letter['kode_unik'] . '.pdf';
        $pdf->Output($filename, 'D');
    }

    public function previewWord()
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        if (!$this->request->is('post')) {
            return $this->response->setJSON(['error' => 'Method not allowed'])->setStatusCode(405);
        }

        $tipeSurat = $this->request->getPost('tipe_surat');
        $isiSurat = $this->request->getPost('isi_surat');

        // H2: Validasi whitelist tipe surat
        $allowedTypes = [
            'Keterangan Usaha', 'Keterangan Tidak Mampu', 'Keterangan Belum Menikah',
            'Keterangan Domisili', 'Undangan', 'Lain Lain',
        ];
        if (!in_array($tipeSurat, $allowedTypes)) {
            return $this->response->setJSON(['error' => 'Jenis surat tidak valid.'])->setStatusCode(400);
        }

        if (empty($isiSurat)) {
            return $this->response->setJSON(['error' => 'Tipe surat dan isi surat harus diisi'])->setStatusCode(400);
        }

        $profileModel = new UserProfileModel();
        $profile = $profileModel->find($this->currentUser['id']);
        if (!$profile) {
            return $this->response->setJSON(['error' => 'Profil tidak ditemukan'])->setStatusCode(404);
        }

        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalSekarang = date('d') . ' ' . $bulan[(int)date('m') - 1] . ' ' . date('Y');
        $tempatTanggalLahir = $this->formatTempatTanggalLahir($profile, $bulan);
        $judulSurat = 'SURAT PERMOHONAN ' . strtoupper($tipeSurat);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 1134,
            'marginBottom' => 1134,
            'marginLeft' => 1417,
            'marginRight' => 1134,
            'pageSizeW' => 12240,
            'pageSizeH' => 18810,
        ]);

        $headerTable = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        ]);
        $headerRow = $headerTable->addRow();
        
        $logoCell = $headerRow->addCell(1200, [
            'valign' => 'center',
            'borderTopSize' => 0,
            'borderTopColor' => 'FFFFFF',
            'borderBottomSize' => 0,
            'borderBottomColor' => 'FFFFFF',
            'borderLeftSize' => 0,
            'borderLeftColor' => 'FFFFFF',
            'borderRightSize' => 0,
            'borderRightColor' => 'FFFFFF',
        ]);
        $logoPath = FCPATH . 'assets/img/logo.webp';
        if (file_exists($logoPath)) {
            $tempPngPath = WRITEPATH . 'uploads/temp/logo_word_temp.png';
            if (!is_dir(WRITEPATH . 'uploads/temp')) {
                mkdir(WRITEPATH . 'uploads/temp', 0755, true);
            }
            
            if (function_exists('imagecreatefromwebp')) {
                $image = imagecreatefromwebp($logoPath);
                if ($image !== false) {
                    $origWidth = imagesx($image);
                    $origHeight = imagesy($image);
                    $size = max($origWidth, $origHeight);
                    $squareImage = imagecreatetruecolor($size, $size);
                    imagealphablending($squareImage, false);
                    imagesavealpha($squareImage, true);
                    $transparent = imagecolorallocatealpha($squareImage, 0, 0, 0, 127);
                    imagefill($squareImage, 0, 0, $transparent);
                    $x = ($size - $origWidth) / 2;
                    $y = ($size - $origHeight) / 2;
                    imagealphablending($squareImage, true);
                    imagecopy($squareImage, $image, $x, $y, 0, 0, $origWidth, $origHeight);
                    imagealphablending($squareImage, false);
                    imagesavealpha($squareImage, true);
                    imagepng($squareImage, $tempPngPath);
                    imagedestroy($image);
                    imagedestroy($squareImage);
                    $logoPath = $tempPngPath;
                }
            }
            
            $logoCell->addImage($logoPath, [
                'width' => 68,
                'height' => 68,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START,
                'wrappingStyle' => 'inline',
                'positioning' => 'relative',
            ]);
        }
        
        $textCell = $headerRow->addCell(8800, [
            'valign' => 'center',
            'borderTopSize' => 0,
            'borderTopColor' => 'FFFFFF',
            'borderBottomSize' => 0,
            'borderBottomColor' => 'FFFFFF',
            'borderLeftSize' => 0,
            'borderLeftColor' => 'FFFFFF',
            'borderRightSize' => 0,
            'borderRightColor' => 'FFFFFF',
        ]);
        $textCell->addText('PEMERINTAH KABUPATEN BULUKUMBA', 
            ['bold' => true, 'size' => 16, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('KECAMATAN UJUNGLOE', 
            ['bold' => true, 'size' => 16, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('DESA PADANGLOANG', 
            ['bold' => true, 'size' => 14, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', 
            ['size' => 11, 'name' => 'Arial', 'italic' => true], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100]);
        
        $section->addLine([
            'weight' => 1,
            'width' => 450,
            'height' => 0,
        ]);
        $section->addTextBreak(0.5);

        $section->addText($judulSurat, 
            ['bold' => true, 'size' => 12, 'underline' => 'single', 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 180]);

        $section->addText('Kepada Yth.', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $section->addText('Bapak Kepala Desa Padangloang dan Staf Desa Padangloang', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $section->addText('di', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $section->addText('Tempat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 240]);

        $section->addText('Dengan hormat,', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 240]);

        $section->addText('Yang bertanda tangan di bawah ini:', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 120]);

        $dataTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        
        $cellStyleNoBorder = [
            'borderTopSize' => 0, 'borderTopColor' => 'FFFFFF',
            'borderBottomSize' => 0, 'borderBottomColor' => 'FFFFFF',
            'borderLeftSize' => 0, 'borderLeftColor' => 'FFFFFF',
            'borderRightSize' => 0, 'borderRightColor' => 'FFFFFF',
        ];
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($profile['nama_lengkap'] ?? '-', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/ Tanggal Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($tempatTanggalLahir, ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($profile, 'jenis_kelamin'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($profile, 'alamat'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('No NIK', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $dataTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($profile, 'nik'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        $section->addText('Dengan ini mengajukan permohonan pembuatan Surat ' . $tipeSurat . '.', 
            ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 120]);

        $section->addText($isiSurat, 
            ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 120]);

        $penutup = 'Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.';
        $section->addText($penutup, 
            ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 360]);

        $section->addText('Padangloang, ' . $tanggalSekarang, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END, 'spaceAfter' => 1400]);
        
        $section->addText($profile['nama_lengkap'] ?? '-', 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        $filename = 'Surat_' . str_replace(' ', '_', $tipeSurat) . '_' . date('YmdHis') . '.docx';
        $filepath = WRITEPATH . 'uploads/temp/' . $filename;
        
        if (!is_dir(WRITEPATH . 'uploads/temp')) {
            mkdir(WRITEPATH . 'uploads/temp', 0755, true);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filepath);

        return $this->response->download($filepath, null)->setFileName($filename);
    }

    public function previewPDF()
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        if (!$this->request->is('post')) {
            return $this->response->setJSON(['error' => 'Method not allowed'])->setStatusCode(405);
        }

        $tipeSurat = $this->request->getPost('tipe_surat');
        $isiSurat = $this->request->getPost('isi_surat');

        // H2: Validasi whitelist tipe surat
        $allowedTypes = [
            'Keterangan Usaha', 'Keterangan Tidak Mampu', 'Keterangan Belum Menikah',
            'Keterangan Domisili', 'Undangan', 'Lain Lain',
        ];
        if (!in_array($tipeSurat, $allowedTypes)) {
            return $this->response->setJSON(['error' => 'Jenis surat tidak valid.'])->setStatusCode(400);
        }

        if (empty($isiSurat)) {
            return $this->response->setJSON(['error' => 'Tipe surat dan isi surat harus diisi'])->setStatusCode(400);
        }

        $profileModel = new UserProfileModel();
        $profile = $profileModel->find($this->currentUser['id']);
        if (!$profile) {
            return $this->response->setJSON(['error' => 'Profil tidak ditemukan'])->setStatusCode(404);
        }

        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalSekarang = date('d') . ' ' . $bulan[(int)date('m') - 1] . ' ' . date('Y');
        $tempatTanggalLahir = $this->formatTempatTanggalLahir($profile, $bulan);
        $judulSurat = 'SURAT PERMOHONAN ' . strtoupper($tipeSurat);

        $pdf = new TCPDF('P', 'mm', array(215, 330), true, 'UTF-8', false);

        $pdf->SetCreator('Desa Padang Loang');
        $pdf->SetAuthor('Sistem Desa');
        $pdf->SetTitle($judulSurat);
        $pdf->SetSubject('Surat Permohonan');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(25, 20, 20);
        $pdf->SetAutoPageBreak(TRUE, 20);
        $pdf->AddPage();

        $logoPath = FCPATH . 'assets/img/logo.webp';
        
        if (file_exists($logoPath)) {
            $tempPngPath = WRITEPATH . 'uploads/temp/logo_pdf_temp.png';
            if (!is_dir(WRITEPATH . 'uploads/temp')) {
                mkdir(WRITEPATH . 'uploads/temp', 0755, true);
            }
            
            if (function_exists('imagecreatefromwebp') && function_exists('imagepng')) {
                $image = @imagecreatefromwebp($logoPath);
                if ($image !== false) {
                    $origWidth = imagesx($image);
                    $origHeight = imagesy($image);
                    $size = max($origWidth, $origHeight);
                    $newImage = imagecreatetruecolor($size, $size);
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                    imagefill($newImage, 0, 0, $transparent);
                    $x = ($size - $origWidth) / 2;
                    $y = ($size - $origHeight) / 2;
                    imagealphablending($newImage, true);
                    imagecopy($newImage, $image, $x, $y, 0, 0, $origWidth, $origHeight);
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    imagepng($newImage, $tempPngPath);
                    imagedestroy($image);
                    imagedestroy($newImage);
                    $logoPath = $tempPngPath;
                    $logoType = 'PNG';
                } else {
                    $logoType = 'WEBP';
                }
            } else {
                $logoType = 'WEBP';
            }
            
            $pdf->Image($logoPath, 25, 18, 25, 25, $logoType, '', '', true, 300, '', false, false, 0, false, false, false);
        }
        
        $pdf->SetY(18);
        $pdf->SetX(55);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(135, 5, 'PEMERINTAH KABUPATEN BULUKUMBA', 0, 1, 'C');
        
        $pdf->SetX(55);
        $pdf->Cell(135, 5, 'KECAMATAN UJUNGLOE', 0, 1, 'C');
        
        $pdf->SetX(55);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(135, 5, 'DESA PADANGLOANG', 0, 1, 'C');
        
        $pdf->SetX(55);
        $pdf->SetFont('helvetica', 'I', 11);
        $pdf->Cell(135, 5, 'Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', 0, 1, 'C');
        
        $pdf->SetY(50);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(25, $pdf->GetY(), 190, $pdf->GetY());
        
        $pdf->SetY(57);
        $pdf->SetFont('helvetica', 'BU', 12);
        $pdf->Cell(0, 8, $judulSurat, 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 11);
        
        $pdf->Cell(0, 5, 'Kepada Yth.', 0, 1, 'L');
        $pdf->Cell(0, 5, 'Bapak Kepala Desa Padangloang dan Staf Desa Padangloang', 0, 1, 'L');
        $pdf->Cell(0, 5, 'di', 0, 1, 'L');
        $pdf->Cell(0, 5, 'Tempat', 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->Cell(0, 5, 'Dengan hormat,', 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->Cell(0, 5, 'Yang bertanda tangan di bawah ini:', 0, 1, 'L');
        $pdf->Ln(2);

        $colWidth1 = 60;
        $colWidth2 = 5;
        $colWidth3 = 95;
        
        $pdf->Cell($colWidth1, 5, 'Nama', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $profile['nama_lengkap'] ?? '-', 0, 1, 'L');
        
        $pdf->Cell($colWidth1, 5, 'Tempat/ Tanggal Lahir', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $tempatTanggalLahir, 0, 1, 'L');
        
        $pdf->Cell($colWidth1, 5, 'Jenis Kelamin', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $this->getProfileValue($profile, 'jenis_kelamin'), 0, 1, 'L');
        
        $pdf->Cell($colWidth1, 5, 'Alamat', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->MultiCell($colWidth3, 5, $this->getProfileValue($profile, 'alamat'), 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        
        $pdf->Cell($colWidth1, 5, 'No NIK', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $this->getProfileValue($profile, 'nik'), 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->Cell(0, 5, 'Dengan ini mengajukan permohonan pembuatan Surat ' . $tipeSurat . '.', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->MultiCell(0, 5, $isiSurat, 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        $pdf->Ln(2);

        $penutup = 'Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.';
        $pdf->MultiCell(0, 5, $penutup, 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        $pdf->Ln(8);

        $pdf->Cell(0, 5, 'Padangloang, ' . $tanggalSekarang, 0, 1, 'R');
        $pdf->Ln(20);
        $pdf->Cell(0, 5, $profile['nama_lengkap'] ?? '-', 0, 1, 'R');

        $filename = 'Surat_' . str_replace(' ', '_', $tipeSurat) . '_' . date('YmdHis') . '.pdf';
        $pdf->Output($filename, 'D');
    }
}