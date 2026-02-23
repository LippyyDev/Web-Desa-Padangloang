<?php

namespace App\Controllers\Staff;

use App\Controllers\ProtectedController;
use App\Models\LetterModel;
use App\Models\NewsModel;
use App\Models\ProjectModel;
use App\Models\PerangkatDesaModel;
use App\Models\GalleryAlbumModel;
use App\Models\UserModel;
use App\Models\UserProfileModel;

class DashboardController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $uid              = $this->currentUser['id'];
        $letterModel      = new LetterModel();
        $newsModel        = new NewsModel();
        $projectModel     = new ProjectModel();
        $perangkatModel   = new PerangkatDesaModel();
        $galleryModel     = new GalleryAlbumModel();
        $userModel        = new UserModel();
        $profileModel     = new UserProfileModel();

        // Hitung total surat masuk SEBELUM query lainnya
        $incoming = $letterModel->countAllResults();

        // Ambil 5 surat terbaru
        $recentLetters = $letterModel->orderBy('created_at', 'DESC')->findAll(5);
        foreach ($recentLetters as &$letter) {
            $user = $userModel->find($letter['user_id']);
            $profile = $profileModel->find($letter['user_id']);
            
            $letter['sender_name'] = ($profile && !empty($profile['nama_lengkap'])) 
                ? $profile['nama_lengkap'] 
                : ($user['username'] ?? 'Unknown');
        }

        // Data grafik 6 bulan terakhir
        $chartLabels = [];
        $chartIncoming = [];
        $chartReplied = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t 23:59:59', strtotime("-$i months"));
            $chartLabels[] = date('M Y', strtotime("-$i months"));
            
            // Buat instance baru untuk setiap query agar tidak terpengaruh query sebelumnya
            $letterModelForChart = new LetterModel();
            
            // Hitung surat masuk per bulan
            $incomingCount = $letterModelForChart
                ->where('created_at >=', $monthStart . ' 00:00:00')
                ->where('created_at <=', $monthEnd)
                ->countAllResults(false);
            $chartIncoming[] = $incomingCount;
            
            // Buat instance baru lagi untuk query surat diterima
            $letterModelForReplied = new LetterModel();
            
            // Hitung surat diterima per bulan
            $repliedCount = $letterModelForReplied
                ->where('status', 'Diterima')
                ->where('replied_at >=', $monthStart . ' 00:00:00')
                ->where('replied_at <=', $monthEnd)
                ->countAllResults(false);
            $chartReplied[] = $repliedCount;
        }

        $data = [
            'incoming'        => $incoming,
            'galleryTotal'    => $galleryModel->countAllResults(),
            'newsTotal'       => $newsModel->countAllResults(),
            'projectTotal'    => $projectModel->countAllResults(),
            'perangkatTotal'  => $perangkatModel->countAllResults(),
            'recentLetters'   => $recentLetters,
            'chartLabels'     => $chartLabels,
            'chartIncoming'   => $chartIncoming,
            'chartReplied'    => $chartReplied,
        ];

        return view('Staff/dashboard', $data);
    }
}


