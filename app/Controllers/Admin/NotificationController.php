<?php

namespace App\Controllers\Admin;

use App\Controllers\ProtectedController;
use App\Models\UserModel;

class NotificationController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        return view('Admin/notifications');
    }

    public function api()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $userModel = new UserModel();
        
        // H1: Pilih hanya kolom yang aman — jangan ekspos password_hash atau google_id
        $newAccounts = $userModel
            ->select('id, username, email, role, status, created_at')
            ->where('role', 'user')
            ->orderBy('created_at', 'DESC')
            ->limit(50)
            ->findAll();

        return $this->response->setJSON([
            csrf_token() => csrf_hash(),
            'success' => true,
            'notifications' => $newAccounts
        ]);
    }
}
