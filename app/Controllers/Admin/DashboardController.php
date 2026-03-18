<?php

namespace App\Controllers\Admin;

use App\Controllers\ProtectedController;
use App\Models\GalleryAlbumModel;
use App\Models\LetterModel;
use App\Models\NewsModel;
use App\Models\ProjectModel;
use App\Models\UserModel;

class DashboardController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $userModel    = new UserModel();
        $letterModel  = new LetterModel();
        $newsModel    = new NewsModel();
        $projectModel = new ProjectModel();
        $albumModel   = new GalleryAlbumModel();

        // Grafik akun terdaftar 6 bulan terakhir
        $chartLabels = [];
        $chartAccounts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t 23:59:59', strtotime("-$i months"));
            $chartLabels[] = date('M Y', strtotime("-$i months"));
            
            $chartAccounts[] = (new UserModel())
                ->where('created_at >=', $monthStart . ' 00:00:00')
                ->where('created_at <=', $monthEnd)
                ->countAllResults(false);
        }

        return view('Admin/dashboard', [
            'userCount'    => $userModel->countAllResults(),
            'letterCount'  => $letterModel->countAllResults(),
            'newsCount'    => $newsModel->countAllResults(),
            'projectCount' => $projectModel->countAllResults(),
            'albumCount'   => $albumModel->countAllResults(),
            'recentAccounts' => $userModel->orderBy('created_at', 'DESC')->limit(5)->find(),
            'chartLabels'   => $chartLabels,
            'chartAccounts' => $chartAccounts,
        ]);
    }
}


