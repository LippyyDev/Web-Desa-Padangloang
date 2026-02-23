<?php

namespace App\Controllers\Staff;

use App\Controllers\ProtectedController;
use App\Models\LetterModel;
use App\Models\UserProfileModel;
use App\Models\PerangkatDesaModel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;

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

    /**
     * Helper function untuk mendapatkan jabatan staff dari perangkat desa
     */
    private function getStaffJabatan($staffProfile)
    {
        $perangkatModel = new PerangkatDesaModel();
        if (!empty($staffProfile['nama_lengkap'])) {
            $perangkat = $perangkatModel->where('nama', $staffProfile['nama_lengkap'])->first();
            if ($perangkat && !empty($perangkat['jabatan'])) {
                return $perangkat['jabatan'];
            }
        }
        return 'Kepala Desa Padangloang'; // Default
    }

    /**
     * Generate Word untuk Surat Keterangan Usaha dari surat masuk
     */
    public function generateWordFromLetter($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $profileModel = new UserProfileModel();
        
        $letter = $letterModel->find($id);
        if (!$letter) {
            return redirect()->to('/staff/surat')->with('error', 'Surat tidak ditemukan.');
        }

        // Cek tipe surat
        if ($letter['tipe_surat'] !== 'Keterangan Usaha') {
            return redirect()->to('/staff/surat/' . $id)->with('error', 'Export hanya tersedia untuk Surat Keterangan Usaha.');
        }

        // Ambil profil pengirim (user yang mengirim surat)
        $senderProfile = $profileModel->find($letter['user_id']);
        if (!$senderProfile) {
            return redirect()->to('/staff/surat/' . $id)->with('error', 'Profil pengirim tidak ditemukan.');
        }

        // Ambil profil staff yang login (yang menerangkan)
        $staffProfile = $profileModel->find($this->currentUser['id']);
        if (!$staffProfile) {
            return redirect()->to('/staff/surat/' . $id)->with('error', 'Profil staff tidak ditemukan.');
        }

        // Format tanggal Indonesia
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalSekarang = date('d') . ' ' . $bulan[(int)date('m') - 1] . ' ' . date('Y');
        
        // Format tanggal lahir pengirim
        $tempatTanggalLahirSender = $this->formatTempatTanggalLahir($senderProfile, $bulan);

        // Judul surat
        $judulSurat = 'SURAT KETERANGAN USAHA';

        // Get jabatan staff
        $jabatanStaff = $this->getStaffJabatan($staffProfile);
        $alamatStaff = $this->getProfileValue($staffProfile, 'alamat', 'Desa Padangloang');

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
        
        // Teks header (tengah)
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
        
        // Garis pemisah
        $section->addLine([
            'weight' => 1,
            'width' => 450,
            'height' => 0,
        ]);
        $section->addTextBreak(0.5);

        // Judul surat
        $section->addText($judulSurat, 
            ['bold' => true, 'size' => 12, 'underline' => 'single', 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);

        // Nomor (di bawah judul, tengah)
        $section->addText('Nomor :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 240]);

        // Yang bertanda tangan
        $section->addText('Yang bertanda tangan dibawah ini :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data staff yang menerangkan (dengan indentasi)
        $staffTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 5,
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
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder); // Cell kosong untuk indentasi
        $cell0->addText('', ['size' => 11, 'name' => 'Arial']);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder); // Dikurangi untuk mendekatkan titik dua
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder); // Ditambah untuk menyeimbangkan
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial']);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder); // Cell kosong untuk indentasi
        $cell0->addText('', ['size' => 11, 'name' => 'Arial']);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder); // Dikurangi untuk mendekatkan titik dua
        $cell1->addText('Jabatan', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder); // Ditambah untuk menyeimbangkan
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial']);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder); // Cell kosong untuk indentasi
        $cell0->addText('', ['size' => 11, 'name' => 'Arial']);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder); // Dikurangi untuk mendekatkan titik dua
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder); // Ditambah untuk menyeimbangkan
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial']);

        $section->addTextBreak(1);

        // Menerangkan bahwa
        $section->addText('Menerangkan bahwa :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data pengirim yang diterangkan (dengan indentasi)
        $senderTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 5,
        ]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder); // Cell kosong untuk indentasi
        $cell0->addText('', ['size' => 11, 'name' => 'Arial']);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder); // Dikurangi untuk mendekatkan titik dua
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder); // Ditambah untuk menyeimbangkan
        $cell3->addText($this->getProfileValue($senderProfile, 'nama_lengkap'), ['size' => 11, 'name' => 'Arial']);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder); // Cell kosong untuk indentasi
        $cell0->addText('', ['size' => 11, 'name' => 'Arial']);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder); // Dikurangi untuk mendekatkan titik dua
        $cell1->addText('Tempat/ Tanggal Lahir', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder); // Ditambah untuk menyeimbangkan
        $cell3->addText($tempatTanggalLahirSender, ['size' => 11, 'name' => 'Arial']);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder); // Cell kosong untuk indentasi
        $cell0->addText('', ['size' => 11, 'name' => 'Arial']);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder); // Dikurangi untuk mendekatkan titik dua
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder); // Ditambah untuk menyeimbangkan
        $cell3->addText('-', ['size' => 11, 'name' => 'Arial']);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder); // Cell kosong untuk indentasi
        $cell0->addText('', ['size' => 11, 'name' => 'Arial']);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder); // Dikurangi untuk mendekatkan titik dua
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder); // Ditambah untuk menyeimbangkan
        $cell3->addText($this->getProfileValue($senderProfile, 'alamat'), ['size' => 11, 'name' => 'Arial']);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder); // Cell kosong untuk indentasi
        $cell0->addText('', ['size' => 11, 'name' => 'Arial']);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder); // Dikurangi untuk mendekatkan titik dua
        $cell1->addText('No NIK', ['size' => 11, 'name' => 'Arial']);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial']);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder); // Ditambah untuk menyeimbangkan
        $cell3->addText($this->getProfileValue($senderProfile, 'nik'), ['size' => 11, 'name' => 'Arial']);

        $section->addTextBreak(1);

        // Isi surat keterangan usaha (dengan 11 spasi manual di awal dan rata kiri kanan)
        $isiKeterangan = '           Yang tersebut namanya diatas adalah benar mempunyai Usaha (Nama Usaha) berdiri Sejak tahun (Tahun) sampai sekarang, yang terletak di Dusun (Nama Dusun) Desa Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba.';
        $section->addText($isiKeterangan, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 120]);

        // Penutup (dengan 11 spasi manual di awal dan rata kiri kanan)
        $penutup = '           Demikian Surat keterangan usaha ini diberikan untuk dipergunakan sebagaimana mestinya.';
        $section->addText($penutup, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 360]);

        // Tanda tangan
        $section->addText('Padangloang, ' . $tanggalSekarang, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END, 'spaceAfter' => 1400]);
        
        // Nama penandatangan
        $section->addText('( ISI DISINI)', 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        // Save file
        $filename = 'Surat_Keterangan_Usaha_' . $letter['kode_unik'] . '.docx';
        $filepath = WRITEPATH . 'uploads/temp/' . $filename;
        
        if (!is_dir(WRITEPATH . 'uploads/temp')) {
            mkdir(WRITEPATH . 'uploads/temp', 0755, true);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filepath);

        return $this->response->download($filepath, null)->setFileName($filename);
    }

}