<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Stores the default settings for the ContentSecurityPolicy, if you
 * choose to use it. The values here will be read in and set as defaults
 * for the site. If needed, they can be overridden on a page-by-page basis.
 *
 * CDN Whitelist hasil audit seluruh Views:
 * - cdn.jsdelivr.net     : Bootstrap, Bootstrap Icons, SweetAlert2, ApexCharts
 * - code.jquery.com      : jQuery 3.7.1
 * - cdn.datatables.net   : DataTables CSS/JS + i18n JSON (XHR)
 * - cdn.quilljs.com      : Quill Editor (Staff - Berita)
 * - fonts.googleapis.com : Google Fonts CSS (Poppins) - Guest
 * - fonts.gstatic.com    : Google Fonts woff2 file server - Guest
 * - www.google.com       : Google Maps embed iframe - Guest
 * - www.youtube.com      : YouTube iframe embed via toEmbedUrl() - Guest & Staff
 * - via.placeholder.com  : Placeholder image fallback - Guest
 *
 * @see https://www.html5rocks.com/en/tutorials/security/content-security-policy/
 */
class ContentSecurityPolicy extends BaseConfig
{
    // -------------------------------------------------------------------------
    // Broadbrush CSP management
    // -------------------------------------------------------------------------

    /**
     * Default CSP report context.
     * Set ke true untuk mode debug (header dikirim tapi tidak diblokir).
     */
    public bool $reportOnly = false;

    /**
     * Specifies a URL where a browser will send reports
     * when a content security policy is violated.
     */
    public ?string $reportURI = null;

    /**
     * Instructs user agents to rewrite URL schemes,
     * changing HTTP to HTTPS.
     */
    public bool $upgradeInsecureRequests = false;

    // -------------------------------------------------------------------------
    // Sources allowed
    // -------------------------------------------------------------------------

    /**
     * Will default to self if not overridden.
     *
     * @var list<string>|string|null
     */
    public $defaultSrc;

    /**
     * Lists allowed scripts' URLs.
     * 'unsafe-inline' diperlukan karena semua layout (Admin, Staff, User, Guest)
     * memiliki inline <script> blocks (SweetAlert2 handler, flash messages, dll).
     *
     * @var list<string>|string
     */
    public $scriptSrc = [
        'self',
        "'unsafe-inline'",     // Inline <script> blocks di semua layout
        'cdn.jsdelivr.net',    // Bootstrap JS, SweetAlert2, ApexCharts
        'code.jquery.com',     // jQuery 3.7.1
        'cdn.datatables.net',  // DataTables JS
        'cdn.quilljs.com',     // Quill Editor (Staff - Berita)
    ];

    /**
     * Lists allowed stylesheets' URLs.
     * 'unsafe-inline' diperlukan karena Guest layout memiliki inline <style>
     * block yang besar (slide notification CSS).
     *
     * @var list<string>|string
     */
    public $styleSrc = [
        'self',
        "'unsafe-inline'",      // Inline <style> blocks (Guest layout, dll)
        'cdn.jsdelivr.net',    // Bootstrap CSS, Bootstrap Icons, SweetAlert2
        'cdn.datatables.net',  // DataTables CSS
        'cdn.quilljs.com',     // Quill Editor CSS
        'fonts.googleapis.com', // Google Fonts CSS (Poppins) - Guest
    ];

    /**
     * Defines the origins from which images can be loaded.
     *
     * @var list<string>|string
     */
    public $imageSrc = [
        'self',
        'data:',               // Inline base64 images
        'via.placeholder.com', // Placeholder image fallback - Guest
    ];

    /**
     * Restricts the URLs that can appear in a page's `<base>` element.
     *
     * @var list<string>|string|null
     */
    public $baseURI;

    /**
     * Lists the URLs for workers and embedded frame contents.
     *
     * @var list<string>|string
     */
    public $childSrc = 'self';

    /**
     * Limits the origins that you can connect to (via XHR,
     * WebSockets, fetch, preconnect, dan EventSource).
     * Semua CDN domain dimasukkan untuk mendukung preconnect link
     * di Guest layout dan request dari Debug Toolbar.
     *
     * @var list<string>|string
     */
    public $connectSrc = [
        'self',
        'cdn.jsdelivr.net',    // preconnect + XHR dari Debug Toolbar
        'code.jquery.com',     // preconnect
        'cdn.datatables.net',  // DataTables i18n JSON (XHR fetch)
        'cdn.quilljs.com',     // preconnect
        'fonts.googleapis.com', // preconnect di Guest layout
        'fonts.gstatic.com',   // preconnect di Guest layout
    ];

    /**
     * Specifies the origins that can serve web fonts.
     *
     * @var list<string>|string
     */
    public $fontSrc = [
        'self',
        'cdn.jsdelivr.net',  // Bootstrap Icons font files
        'fonts.gstatic.com', // Google Fonts woff2 files - Guest
    ];

    /**
     * Lists valid endpoints for submission from `<form>` tags.
     *
     * @var list<string>|string
     */
    public $formAction = 'self';

    /**
     * Specifies the sources that can embed the current page.
     *
     * @var list<string>|string|null
     */
    public $frameAncestors;

    /**
     * The frame-src directive restricts the URLs which may
     * be loaded into nested browsing contexts (iframe).
     *
     * @var list<string>|string|null
     */
    public $frameSrc = [
        'https://www.google.com',  // Google Maps embed iframe - Guest (home, profil)
        'https://www.youtube.com', // YouTube embed iframe via toEmbedUrl() - Guest & Staff
    ];

    /**
     * Restricts the origins allowed to deliver video and audio.
     *
     * @var list<string>|string|null
     */
    public $mediaSrc;

    /**
     * Allows control over Flash and other plugins.
     *
     * @var list<string>|string
     */
    public $objectSrc = 'self';

    /**
     * @var list<string>|string|null
     */
    public $manifestSrc;

    /**
     * Limits the kinds of plugins a page may invoke.
     *
     * @var list<string>|string|null
     */
    public $pluginTypes;

    /**
     * List of actions allowed.
     *
     * @var list<string>|string|null
     */
    public $sandbox;

    /**
     * Nonce tag for style
     */
    public string $styleNonceTag = '{csp-style-nonce}';

    /**
     * Nonce tag for script
     */
    public string $scriptNonceTag = '{csp-script-nonce}';

    /**
     * Replace nonce tag automatically.
     * NONAKTIF: Ketika nonce aktif, browser mengabaikan 'unsafe-inline'.
     * Karena semua layout menggunakan inline script & style tanpa nonce,
     * autoNonce harus dimatikan agar 'unsafe-inline' dihormati browser.
     */
    public bool $autoNonce = false;
}
