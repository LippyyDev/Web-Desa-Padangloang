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
        if ($letter['tipe_surat'] !== 'Keterangan Usaha' && $letter['tipe_surat'] !== 'Keterangan Tidak Mampu' && $letter['tipe_surat'] !== 'Keterangan Belum Menikah' && $letter['tipe_surat'] !== 'Keterangan Domisili' && $letter['tipe_surat'] !== 'Undangan') {
            return redirect()->to('/staff/surat/' . $id)->with('error', 'Export hanya tersedia untuk Surat Keterangan Usaha, Keterangan Tidak Mampu, Keterangan Belum Menikah, Keterangan Domisili, dan Undangan.');
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

        // Judul surat berdasarkan tipe
        if ($letter['tipe_surat'] === 'Keterangan Tidak Mampu') {
            $judulSurat = 'SURAT KETERANGAN TIDAK MAMPU';
        } elseif ($letter['tipe_surat'] === 'Keterangan Belum Menikah') {
            $judulSurat = 'KETERANGAN BELUM MENIKAH';
        } elseif ($letter['tipe_surat'] === 'Keterangan Domisili') {
            $judulSurat = 'SURAT KETERANGAN DOMISILI';
        } else {
            $judulSurat = 'SURAT KETERANGAN USAHA';
        }

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
            'pageSizeW' => 12240,   // 21.5 cm in twips (21.5 * 567)
            'pageSizeH' => 18810,   // 33 cm in twips (33 * 570)
        ]);

        // Header dengan logo dan teks (untuk semua tipe surat)
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

            // Handle berdasarkan tipe surat
            if ($letter['tipe_surat'] === 'Keterangan Tidak Mampu') {
                // Format untuk Keterangan Tidak Mampu
                $this->generateKeteranganTidakMampu($section, $senderProfile, $staffProfile, $tempatTanggalLahirSender, $tanggalSekarang, $bulan);
            } elseif ($letter['tipe_surat'] === 'Keterangan Belum Menikah') {
                // Format untuk Keterangan Belum Menikah
                $this->generateKeteranganBelumMenikah($section, $senderProfile, $staffProfile, $tempatTanggalLahirSender, $tanggalSekarang);
            } elseif ($letter['tipe_surat'] === 'Keterangan Domisili') {
                // Format untuk Keterangan Domisili
                $this->generateKeteranganDomisili($section, $senderProfile, $staffProfile, $tempatTanggalLahirSender, $tanggalSekarang);
            } else {
                // Format untuk Keterangan Usaha
                $this->generateKeteranganUsaha($section, $senderProfile, $staffProfile, $tempatTanggalLahirSender, $tanggalSekarang);
            }
        }

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

    /**
     * Generate format Keterangan Usaha
     */
    private function generateKeteranganUsaha($section, $senderProfile, $staffProfile, $tempatTanggalLahirSender, $tanggalSekarang)
    {
        // Yang bertanda tangan
        $section->addText('Yang bertanda tangan dibawah ini :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data staff yang menerangkan (dengan indentasi)
        $staffTable = $section->addTable([
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
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jabatan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);


        // Menerangkan bahwa
        $section->addText('Menerangkan bahwa :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data pengirim yang diterangkan (dengan indentasi)
        $senderTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'nama_lengkap'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/ Tanggal Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($tempatTanggalLahirSender, ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('-', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'alamat'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('No NIK', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'nik'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

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
    }

    /**
     * Generate format Keterangan Tidak Mampu
     */
    private function generateKeteranganTidakMampu($section, $senderProfile, $staffProfile, $tempatTanggalLahirSender, $tanggalSekarang, $bulan)
    {
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

        // Yang bertanda tangan di bawah ini menerangkang bahwa pada
        $section->addText('Yang bertanda tangan di bawah ini menerangkang bahwa pada :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data staff yang menerangkan
        $staffTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jabatan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);


        // Yang Menerangkan Bahwa
        $section->addText('Yang Menerangkan Bahwa :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data pengirim yang diterangkan
        $senderTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'nama_lengkap'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/ Tgl Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($tempatTanggalLahirSender, ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('-', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Agama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'agama'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('NIK', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'nik'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Pekerjaan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'pekerjaan'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'alamat'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);


        // Orang Tua/Ayah
        $section->addText('Orang Tua/Ayah', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        $ayahTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/ Tgl Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Agama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Pekerjaan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);


        // Orang Tua/Ibu
        $section->addText('Orang Tua/Ibu', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        $ibuTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/ Tgl Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Agama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Pekerjaan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Isi surat
        $isiKeterangan = '           Setelah diadakan penelitian hingga saat dikeluarkan surat keterangan ini yang bersangkutan benar-benar keadaan sosial ekonominya kurang mampu yang penghasilannya kurang dari Rp 750.000 / bulan';
        $section->addText($isiKeterangan, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 120]);

        // Penutup
        $penutup = '           Demikan surat keterangan ini dibuat dan diberikan kepada yang berkepentingan untuk selanjutnya supaya dipergunakan sebagai persyaratan untuk mendapatkan bantuan siswa miskin (BSM)';
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
    }

    /**
     * Generate format Keterangan Belum Menikah
     */
    private function generateKeteranganBelumMenikah($section, $senderProfile, $staffProfile, $tempatTanggalLahirSender, $tanggalSekarang)
    {
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

        // Yang bertanda tangan di bawah ini
        $section->addText('Yang bertanda tangan di bawah ini :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data staff yang menerangkan
        $staffTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jabatan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Menerangkan Bahwa
        $section->addText('Menerangkan Bahwa :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data pengirim yang diterangkan
        $senderTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'nama_lengkap'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/Tgl Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($tempatTanggalLahirSender, ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Agama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'agama'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Pekerjaan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'pekerjaan'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'alamat'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Nama Orang Tua / Wali
        $section->addText('Nama Orang Tua / Wali :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        $ayahTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama Ayah', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/Tgl Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Pekerjaan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ayahTable->addRow();
        $cell0 = $ayahTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ayahTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ayahTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ayahTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        $ibuTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama Ibu', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/Tgl Lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jenis Kelamin', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Pekerjaan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $ibuTable->addRow();
        $cell0 = $ibuTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $ibuTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $ibuTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $ibuTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Isi surat
        $isiKeterangan = '           Yang  tersebut  Namanya  di atas  adalah  benar  selama berdomisili di Dusun Salebboe Desa Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba Belum Pernah Menikah.';
        $section->addText($isiKeterangan, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 120]);

        // Penutup
        $penutup = '           Demikian surat keterangan ini kami buat dan di berikan kepadanya untuk di pergunakan sebagaimana mestinya';
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
    }

    /**
     * Generate format Keterangan Domisili
     */
    private function generateKeteranganDomisili($section, $senderProfile, $staffProfile, $tempatTanggalLahirSender, $tanggalSekarang)
    {
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

        // Yang bertanda tangan dibawah ini
        $section->addText('Yang bertanda tangan dibawah ini:', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data staff yang menerangkan
        $staffTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jabatan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $staffTable->addRow();
        $cell0 = $staffTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $staffTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $staffTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $staffTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('( ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Menerangkan Bahwa
        $section->addText('Menerangkan Bahwa :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Data pengirim yang diterangkan
        $senderTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Nama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'nama_lengkap'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat/Tgl lahir', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($tempatTanggalLahirSender, ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Agama', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'agama'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Pekerjaan', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'pekerjaan'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $senderTable->addRow();
        $cell0 = $senderTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $senderTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Alamat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $senderTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $senderTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText($this->getProfileValue($senderProfile, 'alamat'), ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Isi surat
        $isiKeterangan = '           Orang tersebut disaksikan dengan sebenarnya bahwa ia penduduk Dusun Latamba ,Desa Padangloang Kecamatan Ujung Loe Kabupaten Bulukumba.';
        $section->addText($isiKeterangan, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 120]);

        // Penutup
        $penutup = '           Demikian Surat Keterangan Domisili ini kami buat dengan sebenarnya untuk dipergunakan sebagai mana mestinya.';
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
    }

    /**
     * Generate format Undangan
     */
    private function generateUndangan($section, $tanggalSekarang)
    {
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

        // Padangloang, tanggal (kanan atas)
        $section->addText('Padangloang, ' . $tanggalSekarang, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END, 'spaceAfter' => 240]);

        // Nomor, Sifat, Lampiran, Perihal
        $infoTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $infoTable->addRow();
        $cell0 = $infoTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $infoTable->addCell(2000, $cellStyleNoBorder);
        $cell1->addText('Nomor', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $infoTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $infoTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('(ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $infoTable->addRow();
        $cell0 = $infoTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $infoTable->addCell(2000, $cellStyleNoBorder);
        $cell1->addText('Sifat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $infoTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $infoTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('(ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $infoTable->addRow();
        $cell0 = $infoTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $infoTable->addCell(2000, $cellStyleNoBorder);
        $cell1->addText('Lampiran', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $infoTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $infoTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('(ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $infoTable->addRow();
        $cell0 = $infoTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $infoTable->addCell(2000, $cellStyleNoBorder);
        $cell1->addText('Perihal', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $infoTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $infoTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('(ISI DISINI)', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Kepada
        $section->addText('Kepada', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);
        
        $section->addText('Yth.', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);

        // Daftar penerima
        $section->addText('1.	Ketua dan Anggota BPD Desa Padangloang', 
            ['size' => 11, 'name' => 'Arial'], 
            ['indentation' => ['firstLine' => 700], 'spaceAfter' => 60]);
        $section->addText('2.	Perangkat Desa Padangloang', 
            ['size' => 11, 'name' => 'Arial'], 
            ['indentation' => ['firstLine' => 700], 'spaceAfter' => 60]);
        $section->addText('3.	Para RT/RW Desa Padangloang', 
            ['size' => 11, 'name' => 'Arial'], 
            ['indentation' => ['firstLine' => 700], 'spaceAfter' => 60]);
        $section->addText('4.	Para Kader Posyandu Desa Padangloang', 
            ['size' => 11, 'name' => 'Arial'], 
            ['indentation' => ['firstLine' => 700], 'spaceAfter' => 120]);

        $section->addText('Di –', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 60]);
        $section->addText('      Tempat', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 240]);

        // Isi surat
        $isiSurat = '           ISI DISINI , maka kami pandang perlu untuk mengundang bapak/ibu saudara/(i) untuk menghadiri perihal tersebut diatas yang akan dilaksanakan pada hari :';
        $section->addText($isiSurat, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 240]);

        // Detail acara
        $acaraTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $acaraTable->addRow();
        $cell0 = $acaraTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $acaraTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Hari/Tanggal', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $acaraTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $acaraTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('ISI DISINI', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $acaraTable->addRow();
        $cell0 = $acaraTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $acaraTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Jam', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $acaraTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $acaraTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('ISI DISINI', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        
        $acaraTable->addRow();
        $cell0 = $acaraTable->addCell(700, $cellStyleNoBorder);
        $cell0->addText('', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell1 = $acaraTable->addCell(2500, $cellStyleNoBorder);
        $cell1->addText('Tempat', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell2 = $acaraTable->addCell(300, $cellStyleNoBorder);
        $cell2->addText(':', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);
        $cell3 = $acaraTable->addCell(6000, $cellStyleNoBorder);
        $cell3->addText('ISI DISINI', ['size' => 11, 'name' => 'Arial'], ['spaceAfter' => 0]);

        $section->addTextBreak(1);

        // Penutup
        $penutup = '           Demikian undangan ini disampaikan untuk mendapatkan perhatian dan atas kehadiran bapak/ibu saudara (i) kami ucapkan terimaksih.';
        $section->addText($penutup, 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceAfter' => 360]);

        // Tanda tangan
        $section->addText('Kepala Desa Padangloang,', 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END, 'spaceAfter' => 1400]);
        
        // Nama penandatangan
        $section->addText('( ISI DISINI)', 
            ['size' => 11, 'name' => 'Arial'], 
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END, 'spaceAfter' => 240]);

        // Tembusan
        $section->addText('Tembusan :', 
            ['size' => 11, 'name' => 'Arial'], 
            ['spaceAfter' => 120]);
        
        $section->addText('1.	Arsip', 
            ['size' => 11, 'name' => 'Arial'], 
            ['indentation' => ['firstLine' => 700], 'spaceAfter' => 60]);
        $section->addText('2.	ISI DISINI', 
            ['size' => 11, 'name' => 'Arial'], 
            ['indentation' => ['firstLine' => 700], 'spaceAfter' => 0]);
    }

}