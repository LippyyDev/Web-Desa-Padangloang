<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\LetterModel;

class UpdateKodeUnikLetters extends BaseCommand
{
    protected $group       = 'Letters';
    protected $name        = 'letters:update-kode-unik';
    protected $description = 'Update kode unik untuk surat yang sudah ada';

    public function run(array $params)
    {
        $letterModel = new LetterModel();
        
        // Ambil semua surat yang belum memiliki kode unik
        $letters = $letterModel->where('kode_unik IS NULL OR kode_unik = ""')->findAll();
        
        if (empty($letters)) {
            CLI::write('Tidak ada surat yang perlu diupdate.', 'green');
            return;
        }
        
        CLI::write('Menemukan ' . count($letters) . ' surat yang perlu diupdate.', 'yellow');
        
        $updated = 0;
        foreach ($letters as $letter) {
            $kodeUnik = $this->generateKodeUnik($letterModel, $letter['id']);
            
            $letterModel->update($letter['id'], ['kode_unik' => $kodeUnik]);
            $updated++;
            
            CLI::write("  - Surat ID {$letter['id']}: {$kodeUnik}", 'cyan');
        }
        
        CLI::write("Berhasil mengupdate {$updated} surat.", 'green');
    }
    
    /**
     * Generate kode unik untuk surat
     * Format: SURAT-YYYYMMDD-XXXXXX (6 digit random)
     */
    private function generateKodeUnik(LetterModel $letterModel, int $letterId): string
    {
        // Gunakan tanggal dari created_at surat
        $letter = $letterModel->find($letterId);
        $date = date('Ymd', strtotime($letter['created_at']));
        $prefix = 'SURAT-' . $date . '-';
        $maxAttempts = 100;
        $attempt = 0;

        do {
            $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
            $kodeUnik = $prefix . $random;
            $exists = $letterModel->where('kode_unik', $kodeUnik)->where('id !=', $letterId)->first();
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        return $kodeUnik;
    }
}

