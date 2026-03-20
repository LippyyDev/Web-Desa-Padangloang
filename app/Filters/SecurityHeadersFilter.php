<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Security Headers Filter
 *
 * Menambahkan security headers yang tidak di-handle oleh CI4's built-in SecureHeaders:
 * - Permissions-Policy
 * - Referrer-Policy
 *
 * Dijalankan sebagai 'after' filter agar bisa memodifikasi response headers.
 */
class SecurityHeadersFilter implements FilterInterface
{
    /**
     * @param array|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Tidak perlu tindakan on before
    }

    /**
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // -----------------------------------------------------------------
        // Referrer-Policy
        // 'strict-origin-when-cross-origin': kirim full URL untuk same-origin,
        // hanya origin untuk cross-origin HTTPS, tidak ada untuk HTTP.
        // -----------------------------------------------------------------
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // -----------------------------------------------------------------
        // Permissions-Policy
        // Membatasi akses ke browser features yang tidak dibutuhkan web desa.
        // - camera=()           : tidak ada akses kamera
        // - microphone=()       : tidak ada akses mikrofon
        // - geolocation=()      : tidak ada akses lokasi (maps pakai iframe, bukan API)
        // - payment=()          : tidak ada payment API
        // - usb=()              : tidak ada akses USB
        // - fullscreen=(self)   : fullscreen hanya dari domain sendiri (YouTube iframe pakai sendiri)
        // - autoplay=(self)     : autoplay hanya dari domain sendiri
        // -----------------------------------------------------------------
        $response->setHeader('Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), ' .
            'fullscreen=(self), autoplay=(self)'
        );

        return $response;
    }
}
