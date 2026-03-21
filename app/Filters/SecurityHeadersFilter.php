<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Security Headers Filter
 *
 * Menambahkan security headers secara manual:
 * - Content-Security-Policy (manual, tanpa nonce agar 'unsafe-inline' berlaku)
 * - Referrer-Policy
 * - Permissions-Policy
 *
 * Catatan: $CSPEnabled di App.php dibiarkan false karena CI4 selalu inject
 * nonce ke CSP header-nya, yang menyebabkan 'unsafe-inline' diabaikan browser.
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
        // Content-Security-Policy (manual, tanpa nonce CI4)
        // Whitelist CDN berdasarkan audit seluruh Views:
        //   - cdn.jsdelivr.net     : Bootstrap, Bootstrap Icons, SweetAlert2, ApexCharts
        //   - code.jquery.com      : jQuery 3.7.1
        //   - cdn.datatables.net   : DataTables CSS/JS + i18n JSON (XHR)
        //   - cdn.quilljs.com      : Quill Editor (Staff - Berita)
        //   - fonts.googleapis.com : Google Fonts CSS (Poppins) - Guest
        //   - fonts.gstatic.com    : Google Fonts woff2 - Guest
        //   - www.google.com       : Google Maps iframe - Guest
        //   - www.youtube.com      : YouTube iframe embed - Guest & Staff
        //   - via.placeholder.com  : Placeholder image fallback - Guest
        // -----------------------------------------------------------------
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net cdn.quilljs.com www.gstatic.com apis.google.com",
            "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdn.datatables.net cdn.quilljs.com fonts.googleapis.com",
            "font-src 'self' cdn.jsdelivr.net fonts.gstatic.com",
            "img-src 'self' data: via.placeholder.com i.ytimg.com",
            "frame-src https://www.google.com https://www.youtube.com https://*.firebaseapp.com",
            "connect-src 'self' cdn.jsdelivr.net code.jquery.com cdn.datatables.net cdn.quilljs.com fonts.googleapis.com fonts.gstatic.com www.gstatic.com apis.google.com identitytoolkit.googleapis.com securetoken.googleapis.com",
            "form-action 'self'",
            "object-src 'self'",
            "base-uri 'self'",
        ]);

        $response->setHeader('Content-Security-Policy', $csp);

        // -----------------------------------------------------------------
        // Referrer-Policy
        // -----------------------------------------------------------------
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // -----------------------------------------------------------------
        // Permissions-Policy
        // -----------------------------------------------------------------
        $response->setHeader('Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), ' .
            'fullscreen=(self), autoplay=(self)'
        );

        return $response;
    }
}
