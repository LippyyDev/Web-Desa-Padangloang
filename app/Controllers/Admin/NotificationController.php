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

        $lastReadTime = session()->get('admin_notif_read_time') ?? 0;
        $readAccounts = session()->get('admin_read_accounts') ?? [];
        
        foreach ($newAccounts as &$account) {
            $isReadByTime = strtotime($account['created_at']) <= $lastReadTime;
            $isReadById = in_array($account['id'], $readAccounts);
            $account['is_read'] = ($isReadByTime || $isReadById) ? 1 : 0;
        }

        return $this->response->setJSON([
            csrf_token() => csrf_hash(),
            'success' => true,
            'notifications' => $newAccounts
        ]);
    }

    public function markAllRead()
    {
        if ($redirect = $this->guard(['admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        session()->set('admin_notif_read_time', time());
        
        // Also clear individually read accounts since everything is now read
        session()->remove('admin_read_accounts');

        return $this->response->setJSON([
            csrf_token() => csrf_hash(),
            'success' => true,
            'message' => 'Semua notifikasi telah ditandai dibaca'
        ]);
    }

    public function markRead($id)
    {
        if ($redirect = $this->guard(['admin'])) {
            return $redirect;
        }

        $readAccounts = session()->get('admin_read_accounts') ?? [];
        if (!in_array($id, $readAccounts)) {
            $readAccounts[] = $id;
            session()->set('admin_read_accounts', $readAccounts);
        }

        return redirect()->to('/admin/akun/' . $id . '/edit');
    }
}
