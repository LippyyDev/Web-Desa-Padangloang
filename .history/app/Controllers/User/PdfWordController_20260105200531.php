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
            'marginLeft' => 1417,   // 2.5 cm
            'marginRight' => 1134,  // 2 cm
        ]);

        // Header dengan logo dan teks
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        ]);
        $headerRow = $headerTable->addRow();
        
        // Logo cell (kiri)
        $logoCell = $headerRow->addCell(1500, [
            'valign' => 'center',
            'borderTopSize' => 0,
            'borderBottomSize' => 0,
            'borderLeftSize' => 0,
            'borderRightSize' => 0,
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
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    imagepng($image, $tempPngPath);
                    imagedestroy($image);
                    $logoPath = $tempPngPath;
                }
            }
            
            $logoCell->addImage($logoPath, [
                'width' => 55,
                'height' => 55,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
        }
        
        // Teks header (tengah)
        $textCell = $headerRow->addCell(7500, [
            'valign' => 'center',
            'borderTopSize' => 0,
            'borderBottomSize' => 0,
            'borderLeftSize' => 0,
            'borderRightSize' => 0,
        ]);
        $textCell->addText('PEMERINTAH KABUPATEN BULUKUMBA', 
            ['bold' => true, 'size' => 13, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('KECAMATAN UJUNGLOE', 
            ['bold' => true, 'size' => 13, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('DESA PADANGLOANG', 
            ['bold' => true, 'size' => 13, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $textCell->addText('Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', 
            ['size' => 11, 'name' => 'Arial', 'italic' => true], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100]);
        
        // Garis pemisah - menggunakan border pada section
        $section->addLine([
            'weight' => 1,
            'width' => 450,
            'height' => 0,
        ]);
        $section->addTextBreak(0.5); // Kurangi jarak dari 1 ke 0.5

        // Judul surat
        $section->addText($judulSurat, 
            ['bold' => true, 'size' => 12, 'underline' => 'single', 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 180]);

        // Kepada Yth.
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

        // Dengan hormat
        $section->addText('Dengan hormat,', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 240]);

        // Yang bertanda tangan
        $section->addText('Yang bertanda tangan di bawah ini:', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data profil - menggunakan table untuk alignment yang rapi
        $dataTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 20,
        ]);
        
        $cellStyleNoBorder = [
            'borderTopSize' => 0,
            'borderBottomSize' => 0,
            'borderLeftSize' => 0,
            'borderRightSize' => 0,
        ];
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(3500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $dataTable->addCell(5000, $cellStyleNoBorder);
        $cell3->addText($profile['nama_lengkap'] ?? '-', ['size' => 11, 'name' => 'Arial']);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(3500, $cellStyleNoBorder);
        $cell1->addText('Tempat/ Tanggal Lahir', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $dataTable->addCell(5000, $cellStyleNoBorder);
        $cell3->addText($tempatTanggalLahir, ['size' => 11, 'name' => 'Arial']);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(3500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $dataTable->addCell(5000, $cellStyleNoBorder);
        $cell3->addText($profile['jenis_kelamin'] ?? '-', ['size' => 11, 'name' => 'Arial']);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(3500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $dataTable->addCell(5000, $cellStyleNoBorder);
        $cell3->addText($profile['alamat'] ?? '-', ['size' => 11, 'name' => 'Arial']);
        
        $dataTable->addRow();
        $cell1 = $dataTable->addCell(3500, $cellStyleNoBorder);
        $cell1->addText('No NIK', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $dataTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $dataTable->addCell(5000, $cellStyleNoBorder);
        $cell3->addText($profile['nik'] ?? '-', ['size' => 11, 'name' => 'Arial']);

        $section->addTextBreak(1);

        // Permohonan
        $section->addText('Dengan ini mengajukan permohonan pembuatan Surat ' . $letter['tipe_surat'] . '.', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Isi surat
        $section->addText($letter['isi_surat'], 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Penutup
        $penutup = 'Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.';
        $section->addText($penutup, 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 360]);

        // Tanda tangan
        $section->addText('Padangloang, ' . $tanggalSekarang, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END, 'spaceAfter' => 720]);
        
        $section->addText($profile['nama_lengkap'] ?? '-', 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

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

        // Set margins (kiri, atas, kanan)
        $pdf->SetMargins(25, 20, 20);
        $pdf->SetAutoPageBreak(TRUE, 20);

        // Add a page
        $pdf->AddPage();

        // === HEADER SECTION ===
        $logoPath = FCPATH . 'assets/img/logo.webp';
        
        if (file_exists($logoPath)) {
            $tempPngPath = WRITEPATH . 'uploads/temp/logo_pdf_temp.png';
            if (!is_dir(WRITEPATH . 'uploads/temp')) {
                mkdir(WRITEPATH . 'uploads/temp', 0755, true);
            }
            
            if (function_exists('imagecreatefromwebp') && function_exists('imagepng')) {
                $image = @imagecreatefromwebp($logoPath);
                if ($image !== false) {
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $newImage = imagecreatetruecolor($width, $height);
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                    imagefill($newImage, 0, 0, $transparent);
                    imagealphablending($newImage, true);
                    imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
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
            
            // Logo di kiri dengan ukuran yang pas (tidak terlalu lebar)
            $pdf->Image($logoPath, 25, 18, 22, 22, $logoType, '', '', true, 300, '', false, false, 0, false, false, false);
        }
        
        // Header text - geser ke kanan agar tidak bertabrakan dengan logo
        $pdf->SetY(18);
        $pdf->SetX(55); // Geser ke kanan
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(135, 5, 'PEMERINTAH KABUPATEN BULUKUMBA', 0, 1, 'C');
        
        $pdf->SetX(55);
        $pdf->Cell(135, 5, 'KECAMATAN UJUNGLOE', 0, 1, 'C');
        
        $pdf->SetX(55);
        $pdf->Cell(135, 5, 'DESA PADANGLOANG', 0, 1, 'C');
        
        // Alamat dengan italic (hitam, tidak merah, tidak underline)
        $pdf->SetX(55);
        $pdf->SetFont('helvetica', 'I', 11);
        $pdf->Cell(135, 5, 'Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', 0, 1, 'C');
        
        // Garis pemisah hitam
        $pdf->SetY(50);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(25, $pdf->GetY(), 190, $pdf->GetY());
        
        // === JUDUL SURAT ===
        $pdf->SetY(57);
        $pdf->SetFont('helvetica', 'BU', 12);
        $pdf->Cell(0, 8, $judulSurat, 0, 1, 'C');
        $pdf->Ln(3);

        // === ISI SURAT ===
        $pdf->SetFont('helvetica', '', 11);
        
        // Kepada Yth.
        $pdf->Cell(0, 5, 'Kepada Yth.', 0, 1, 'L');
        $pdf->Cell(0, 5, 'Bapak Kepala Desa Padangloang dan Staf Desa Padangloang', 0, 1, 'L');
        $pdf->Cell(0, 5, 'di', 0, 1, 'L');
        $pdf->Cell(0, 5, 'Tempat', 0, 1, 'L');
        $pdf->Ln(3);

        // Dengan hormat
        $pdf->Cell(0, 5, 'Dengan hormat,', 0, 1, 'L');
        $pdf->Ln(3);

        // Yang bertanda tangan
        $pdf->Cell(0, 5, 'Yang bertanda tangan di bawah ini:', 0, 1, 'L');
        $pdf->Ln(2);

        // === DATA PROFIL - Format Table ===
        $colWidth1 = 60;
        $colWidth2 = 5;
        $colWidth3 = 95;
        
        // Nama
        $pdf->Cell($colWidth1, 5, 'Nama', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $profile['nama_lengkap'] ?? '-', 0, 1, 'L');
        
        // Tempat/Tanggal Lahir
        $pdf->Cell($colWidth1, 5, 'Tempat/ Tanggal Lahir', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $tempatTanggalLahir, 0, 1, 'L');
        
        // Jenis Kelamin
        $pdf->Cell($colWidth1, 5, 'Jenis Kelamin', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $profile['jenis_kelamin'] ?? '-', 0, 1, 'L');
        
        // Alamat
        $pdf->Cell($colWidth1, 5, 'Alamat', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->MultiCell($colWidth3, 5, $profile['alamat'] ?? '-', 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        
        // No NIK
        $pdf->Cell($colWidth1, 5, 'No NIK', 0, 0, 'L');
        $pdf->Cell($colWidth2, 5, ':', 0, 0, 'L');
        $pdf->Cell($colWidth3, 5, $profile['nik'] ?? '-', 0, 1, 'L');
        $pdf->Ln(2);

        // === PERMOHONAN ===
        $pdf->Cell(0, 5, 'Dengan ini mengajukan permohonan pembuatan Surat ' . $letter['tipe_surat'] . '.', 0, 1, 'L');
        $pdf->Ln(2);

        // Isi surat
        $pdf->MultiCell(0, 5, $letter['isi_surat'], 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        $pdf->Ln(2);

        // === PENUTUP ===
        $penutup = 'Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.';
        $pdf->MultiCell(0, 5, $penutup, 0, 'L', false, 1, '', '', true, 0, false, true, 5, 'T');
        $pdf->Ln(8);

        // === TANDA TANGAN ===
        // Tanggal dan tempat (kanan)
        $pdf->Cell(0, 5, 'Padangloang, ' . $tanggalSekarang, 0, 1, 'R');
        $pdf->Ln(15);
        
        // Nama penandatangan (kanan)
        $pdf->Cell(0, 5, $profile['nama_lengkap'] ?? '-', 0, 1, 'R');

        // Output PDF
        $filename = 'Surat_' . str_replace(' ', '_', $letter['tipe_surat']) . '_' . $letter['kode_unik'] . '.pdf';
        $pdf->Output($filename, 'D');
    }
}