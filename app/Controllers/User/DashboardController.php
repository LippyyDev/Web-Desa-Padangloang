<?php

namespace App\Controllers\User;

use App\Controllers\ProtectedController;
use App\Models\LetterModel;
use App\Models\NotificationModel;

class DashboardController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $uid         = $this->currentUser['id'];
        $letterModel = new LetterModel();
        $notifModel  = new NotificationModel();

        // Data grafik 6 bulan terakhir
        $chartLabels = [];
        $chartMenunggu = [];
        $chartDibaca = [];
        $chartDiterima = [];
        $chartDitolak = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t 23:59:59', strtotime("-$i months"));
            $chartLabels[] = date('M Y', strtotime("-$i months"));
            
            $chartMenunggu[] = (new LetterModel())
                ->where('user_id', $uid)
                ->where('status', 'Menunggu')
                ->where('created_at >=', $monthStart . ' 00:00:00')
                ->where('created_at <=', $monthEnd)
                ->countAllResults(false);
                
            $chartDibaca[] = (new LetterModel())
                ->where('user_id', $uid)
                ->where('status', 'Dibaca')
                ->where('created_at >=', $monthStart . ' 00:00:00')
                ->where('created_at <=', $monthEnd)
                ->countAllResults(false);
                
            $chartDiterima[] = (new LetterModel())
                ->where('user_id', $uid)
                ->where('status', 'Diterima')
                ->where('created_at >=', $monthStart . ' 00:00:00')
                ->where('created_at <=', $monthEnd)
                ->countAllResults(false);
                
            $chartDitolak[] = (new LetterModel())
                ->where('user_id', $uid)
                ->where('status', 'Ditolak')
                ->where('created_at >=', $monthStart . ' 00:00:00')
                ->where('created_at <=', $monthEnd)
                ->countAllResults(false);
        }

        $data = [
            'totalLetters'  => $letterModel->where('user_id', $uid)->countAllResults(),
            'sentCount'     => $letterModel->where('user_id', $uid)->where('status', 'Terkirim')->countAllResults(),
            'readCount'     => $letterModel->where('user_id', $uid)->where('status', 'Dibaca')->countAllResults(),
            'repliedCount'  => $letterModel->where('user_id', $uid)->where('status', 'Diterima')->countAllResults(),
            'recentLetters' => $letterModel->where('user_id', $uid)->orderBy('created_at', 'DESC')->findAll(5),
            'chartLabels'   => $chartLabels,
            'chartMenunggu' => $chartMenunggu,
            'chartDibaca'   => $chartDibaca,
            'chartDiterima' => $chartDiterima,
            'chartDitolak'  => $chartDitolak,
        ];

        return view('User/dashboard', $data);
    }
}


