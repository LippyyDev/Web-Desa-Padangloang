<?php

namespace App\Controllers\User;

use App\Controllers\ProtectedController;
use App\Models\LetterModel;
use App\Models\UserProfileModel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
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
        $logoPath = FCPATH . 'assets/img/logo.webp';
        if (file_exists($logoPath)) {
            // Convert webp ke PNG untuk support transparansi di Word
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
                'width' => 60,
                'height' => 60,
                'alignment' => 'left',
            ]);
        }
        
        // Teks header (tengah) - gunakan font Arial
        $textCell = $headerRow->addCell(10000);
        $textCell->addText('PEMERINTAH KABUPATEN BULUKUMBA', ['bold' => true, 'size' => 12, 'name' => 'Arial'], ['alignment' => 'center']);
        $textCell->addText('KECAMATAN UJUNGLOE', ['bold' => true, 'size' => 12, 'name' => 'Arial'], ['alignment' => 'center']);
        $textCell->addText('DESA PADANGLOANG', ['bold' => true, 'size' => 12, 'name' => 'Arial'], ['alignment' => 'center']);
        $textCell->addText('Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', ['size' => 10, 'name' => 'Arial'], ['alignment' => 'center']);
        
        // Garis pemisah
        $section->addText('', [], ['spaceAfter' => 120]);
        $section->addLine(['width' => 100, 'height' => 0, 'positioning' => 'absolute']);

        // Judul surat
        $section->addText('', [], ['spaceAfter' => 240]);
        $section->addText($judulSurat, ['bold' => true, 'size' => 14, 'underline' => 'single', 'name' => 'Arial'], ['alignment' => 'center', 'spaceAfter' => 360]);

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

        // Header dengan logo dan teks menggunakan method TCPDF langsung
        $logoPath = FCPATH . 'assets/img/logo.webp';
        
        // Logo di kiri (posisi X=20, Y=20, width=30, height=30)
        // Untuk transparansi, gunakan maskImage atau convert ke PNG
        if (file_exists($logoPath)) {
            // Convert webp ke temporary PNG untuk support transparansi
            $tempPngPath = WRITEPATH . 'uploads/temp/logo_temp.png';
            if (!is_dir(WRITEPATH . 'uploads/temp')) {
                mkdir(WRITEPATH . 'uploads/temp', 0755, true);
            }
            
            // Coba convert webp ke png jika extension tersedia
            if (function_exists('imagecreatefromwebp')) {
                $image = imagecreatefromwebp($logoPath);
                if ($image !== false) {
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    imagepng($image, $tempPngPath);
                    imagedestroy($image);
                    $logoPath = $tempPngPath;
                    $logoType = 'PNG';
                } else {
                    $logoType = 'WEBP';
                }
            } else {
                $logoType = 'WEBP';
            }
            
            $pdf->Image($logoPath, 20, 20, 30, 30, $logoType, '', '', true, 300, '', false, false, 0, false, false, false);
        }
        
        // Teks header di tengah (mulai dari X=60 untuk memberi ruang logo)
        $pdf->SetY(20);
        $pdf->SetX(60);
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(110, 6, 'PEMERINTAH KABUPATEN BULUKUMBA', 0, 1, 'C');
        
        $pdf->SetX(60);
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(110, 6, 'KECAMATAN UJUNGLOE', 0, 1, 'C');
        
        $pdf->SetX(60);
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(110, 6, 'DESA PADANGLOANG', 0, 1, 'C');
        
        $pdf->SetX(60);
        $pdf->SetFont('arial', '', 10);
        $pdf->Cell(110, 6, 'Alamat : Jl. Poros Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba', 0, 1, 'C');
        
        // Garis pemisah
        $pdf->SetY(58);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        
        // Judul surat
        $pdf->SetY(65);
        $pdf->SetFont('arial', 'BU', 14);
        $pdf->Cell(0, 10, $judulSurat, 0, 1, 'C');
        $pdf->Ln(5);

        // Kepada Yth.
        $pdf->SetFont('arial', '', 12);
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
        $pdf->SetFont('arial', '', 12);
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

        // Penutup - gunakan MultiCell agar tidak terpotong
        $penutup = 'Demikian surat permohonan ini saya sampaikan. Atas perhatian dan bantuan Bapak/Ibu, saya ucapkan terima kasih.';
        $pdf->MultiCell(0, 6, $penutup, 0, 'L', false, 1, '', '', true);
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

