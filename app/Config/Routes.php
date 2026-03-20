<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Guest\LandingController::index');
$routes->get('/profil', 'Guest\LandingController::profil');
$routes->get('/galeri', 'Guest\LandingController::galeri');
$routes->post('/galeri/api', 'Guest\LandingController::galeriAjax');
$routes->get('/galeri/(:num)', 'Guest\LandingController::galeriDetail/$1');
$routes->post('/galeri/detail-api/(:num)', 'Guest\LandingController::galeriDetailAjax/$1');
$routes->get('/berita', 'Guest\LandingController::berita');
$routes->post('/berita/api', 'Guest\LandingController::beritaAjax');
$routes->get('/berita/(:num)', 'Guest\LandingController::detailBerita/$1');
$routes->post('/berita/detail-api/(:num)', 'Guest\LandingController::detailBeritaAjax/$1');
$routes->get('/project', 'Guest\LandingController::project');
$routes->post('/project/api', 'Guest\LandingController::projectAjax');
$routes->get('/project/(:num)', 'Guest\LandingController::detailProject/$1');
$routes->post('/project/detail-api/(:num)', 'Guest\LandingController::detailProjectAjax/$1');

$routes->get('/login', 'Guest\AuthController::login');
$routes->post('/login', 'Guest\AuthController::doLogin');
$routes->get('/register', 'Guest\AuthController::register');
$routes->post('/auth/firebase', 'Guest\AuthController::firebaseAuth');
$routes->post('/auth/resend-reset-otp', 'Guest\AuthController::resendResetOtp');

// Email queue processing endpoint (untuk AJAX/cron)
$routes->post('/api/email-queue/process', 'Api\EmailQueueController::process');
$routes->get('/api/email-queue/process', 'Api\EmailQueueController::process');
$routes->get('/forgot-password', 'Guest\AuthController::forgotPassword');
$routes->post('/forgot-password', 'Guest\AuthController::sendReset');
$routes->get('/reset-password', 'Guest\AuthController::resetPassword');
$routes->post('/reset-password', 'Guest\AuthController::doResetPassword');
$routes->get('/logout', 'Guest\AuthController::logout');

$routes->get('/dashboard', 'Home::dashboard');

$routes->group('user', static function ($routes) {
    $routes->get('dashboard', 'User\DashboardController::index');
    $routes->get('profil', 'User\ProfileController::index');
    $routes->post('profil', 'User\ProfileController::update');
    $routes->post('profil/ubah-password', 'User\ProfileController::changePassword');

    $routes->get('surat', 'User\LetterController::index');
    $routes->post('surat/api', 'User\LetterController::api');
    $routes->get('surat/buat', 'User\LetterController::create');
    $routes->post('surat', 'User\LetterController::store');
    $routes->get('surat/(:num)', 'User\LetterController::show/$1');
    $routes->get('surat/(:num)/edit', 'User\LetterController::edit/$1');
    $routes->post('surat/(:num)', 'User\LetterController::update/$1');
    $routes->get('surat/(:num)/hapus', 'User\LetterController::delete/$1');
    $routes->get('surat/(:num)/word', 'User\PdfWordController::generateWord/$1');
    $routes->get('surat/(:num)/pdf', 'User\PdfWordController::generatePDF/$1');
    $routes->post('surat/preview/word', 'User\PdfWordController::previewWord');
    $routes->post('surat/preview/pdf', 'User\PdfWordController::previewPDF');
    $routes->get('surat/template/(:segment)', 'Staff\PdfWordController::downloadTemplate/$1');

    $routes->get('notifikasi', 'User\NotificationController::index');
    $routes->post('notifikasi/api', 'User\NotificationController::api');
    $routes->get('notifikasi/(:num)/read', 'User\NotificationController::markRead/$1');

    $routes->get('surat/lampiran/(:num)', 'User\LetterController::serveAttachment/$1');
    $routes->get('surat/balasan-lampiran/(:num)', 'User\LetterController::serveReplyAttachment/$1');
});

$routes->group('staff', static function ($routes) {
    $routes->get('dashboard', 'Staff\DashboardController::index');
    $routes->get('profil', 'Staff\ProfileController::index');
    $routes->post('profil', 'Staff\ProfileController::update');
    $routes->post('profil/ubah-password', 'Staff\ProfileController::changePassword');

    $routes->get('surat', 'Staff\LetterController::index');
    $routes->post('surat/api', 'Staff\LetterController::api');
    $routes->get('surat/(:num)', 'Staff\LetterController::show/$1');
    $routes->get('surat/(:num)/hapus', 'Staff\LetterController::delete/$1');
    $routes->post('surat/(:num)/balas', 'Staff\LetterController::reply/$1');
    $routes->get('surat/(:num)/balasan/(:num)/hapus', 'Staff\LetterController::deleteReply/$1/$2');
    $routes->get('surat/(:num)/word', 'Staff\PdfWordController::generateWordFromLetter/$1');
    $routes->get('surat/template/(:segment)', 'Staff\PdfWordController::downloadTemplate/$1');

    $routes->get('desa', 'Staff\ContentController::desaProfile');
    $routes->post('desa', 'Staff\ContentController::updateDesaProfile');

    $routes->get('galeri', 'Staff\ContentController::gallery');
    $routes->post('galeri/api', 'Staff\ContentController::galleryApi');
    $routes->get('galeri/tambah', 'Staff\ContentController::createGallery');
    $routes->get('galeri/(:num)/edit', 'Staff\ContentController::editGallery/$1');
    $routes->post('galeri', 'Staff\ContentController::storeGallery');
    $routes->post('galeri/(:num)', 'Staff\ContentController::updateGallery/$1');
    $routes->get('galeri/(:num)/hapus', 'Staff\ContentController::deleteGallery/$1');
    $routes->get('galeri/media/(:num)/hapus', 'Staff\ContentController::deleteGalleryMedia/$1');

    $routes->get('berita', 'Staff\ContentController::news');
    $routes->post('berita/api', 'Staff\ContentController::newsApi');
    $routes->get('berita/tambah', 'Staff\ContentController::createNews');
    $routes->get('berita/(:num)/edit', 'Staff\ContentController::editNews/$1');
    $routes->post('berita', 'Staff\ContentController::storeNews');
    $routes->post('berita/(:num)', 'Staff\ContentController::updateNews/$1');
    $routes->get('berita/(:num)/hapus', 'Staff\ContentController::deleteNews/$1');
    $routes->get('berita/media/(:num)/hapus', 'Staff\ContentController::deleteNewsMedia/$1');

    $routes->get('projects', 'Staff\ContentController::projects');
    $routes->post('projects/api', 'Staff\ContentController::projectsApi');
    $routes->get('projects/tambah', 'Staff\ContentController::createProject');
    $routes->get('projects/(:num)/edit', 'Staff\ContentController::editProject/$1');
    $routes->post('projects', 'Staff\ContentController::storeProject');
    $routes->post('projects/(:num)', 'Staff\ContentController::updateProject/$1');
    $routes->get('projects/(:num)/hapus', 'Staff\ContentController::deleteProject/$1');
    $routes->get('projects/media/(:num)/hapus', 'Staff\ContentController::deleteProjectMedia/$1');

    $routes->get('perangkat-desa', 'Staff\ContentController::perangkatDesa');
    $routes->post('perangkat-desa/api', 'Staff\ContentController::perangkatDesaApi');
    $routes->get('perangkat-desa/tambah', 'Staff\ContentController::createPerangkatDesa');
    $routes->get('perangkat-desa/(:num)/edit', 'Staff\ContentController::editPerangkatDesa/$1');
    $routes->post('perangkat-desa', 'Staff\ContentController::storePerangkatDesa');
    $routes->post('perangkat-desa/(:num)', 'Staff\ContentController::updatePerangkatDesa/$1');
    $routes->get('perangkat-desa/(:num)/hapus', 'Staff\ContentController::deletePerangkatDesa/$1');

    $routes->get('notifikasi', 'Staff\NotificationController::index');
    $routes->post('notifikasi/api', 'Staff\NotificationController::api');
    $routes->get('notifikasi/(:num)/read', 'Staff\NotificationController::markRead/$1');

    $routes->get('surat/lampiran/(:num)', 'Staff\LetterController::serveAttachment/$1');
    $routes->get('surat/balasan-lampiran/(:num)', 'Staff\LetterController::serveReplyAttachment/$1');
});

$routes->group('admin', static function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('akun', 'Admin\AccountController::index');
    $routes->post('akun/api', 'Admin\AccountController::api');
    $routes->get('akun/tambah', 'Admin\AccountController::create');
    $routes->get('akun/(:num)/edit', 'Admin\AccountController::edit/$1');
    $routes->post('akun', 'Admin\AccountController::store');
    $routes->post('akun/(:num)', 'Admin\AccountController::update/$1');
    $routes->post('akun/(:num)/ubah-password', 'Admin\AccountController::changePassword/$1');
    $routes->get('akun/(:num)/hapus', 'Admin\AccountController::delete/$1');
    $routes->get('profil', 'Admin\ProfileController::index');
    $routes->post('profil', 'Admin\ProfileController::update');
    $routes->post('profil/ubah-password', 'Admin\ProfileController::changePassword');
    
    $routes->get('notifikasi', 'Admin\NotificationController::index');
    $routes->post('notifikasi/api', 'Admin\NotificationController::api');
});
