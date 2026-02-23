<?php

namespace App\Controllers\User;

use App\Controllers\ProtectedController;
use App\Models\NotificationModel;

class NotificationController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $notifModel = new NotificationModel();

        return view('User/notifications', [
            'notifications' => $notifModel->where('user_id', $this->currentUser['id'])
                ->orderBy('created_at', 'DESC')
                ->findAll(),
        ]);
    }

    public function markRead($id)
    {
        if ($redirect = $this->guard(['user'])) {
            return $redirect;
        }

        $notifModel = new NotificationModel();
        $notif      = $notifModel->where('user_id', $this->currentUser['id'])->find($id);

        if ($notif) {
            if (!(bool)$notif['is_read']) {
                $notifModel->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
            }
            if (!empty($notif['related_letter_id'])) {
                return redirect()->to('/user/surat/' . $notif['related_letter_id']);
            }
        }

        return redirect()->to('/user/notifikasi');
    }
}


