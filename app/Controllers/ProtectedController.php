<?php

namespace App\Controllers;

class ProtectedController extends BaseController
{
    /**
     * Pastikan user login dan memiliki role yang sesuai.
     */
    protected function guard(array $roles = [])
    {
        if (!$this->currentUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!empty($roles) && !in_array($this->currentUser['role'], $roles, true)) {
            return redirect()->to('/login')->with('error', 'Akses ditolak untuk role ini.');
        }

        return null;
    }

    /**
     * Membuat folder upload jika belum ada.
     */
    protected function ensureUploadPath(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }
}


