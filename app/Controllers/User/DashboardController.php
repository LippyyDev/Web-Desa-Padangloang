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

        $data = [
            'totalLetters'  => $letterModel->where('user_id', $uid)->countAllResults(),
            'sentCount'     => $letterModel->where('user_id', $uid)->where('status', 'Terkirim')->countAllResults(),
            'readCount'     => $letterModel->where('user_id', $uid)->where('status', 'Dibaca')->countAllResults(),
            'repliedCount'  => $letterModel->where('user_id', $uid)->where('status', 'Diterima')->countAllResults(),
            'notifications' => $notifModel->where('user_id', $uid)->orderBy('created_at', 'DESC')->findAll(5),
        ];

        return view('User/dashboard', $data);
    }
}


