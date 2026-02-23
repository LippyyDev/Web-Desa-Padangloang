<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return redirect()->to('/');
    }

    public function dashboard()
    {
        $user = session()->get('user');
        if (!$user) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($user['role'] === 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        if ($user['role'] === 'staf') {
            return redirect()->to('/staff/dashboard');
        }

        return redirect()->to('/user/dashboard');
    }
}
