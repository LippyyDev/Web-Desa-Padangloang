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
        
        // Ambil 50 akun terbaru yang terdaftar
        $newAccounts = $userModel->where('role', 'user')
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
