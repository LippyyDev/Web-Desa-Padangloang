<?php
$currentUser = session()->get('user');
$isLoggedIn = !empty($currentUser);

// Jika user sudah login, ambil data profil
$userName = 'User';
$userPhoto = base_url('assets/img/guest.webp');
$userRole = '';

if ($isLoggedIn) {
    $userProfileModel = new \App\Models\UserProfileModel();
    $userId = $currentUser['id'] ?? null;
    $profile = $userId ? $userProfileModel->find($userId) : null;
    
    $userName = ($profile && !empty($profile['nama_lengkap'])) 
        ? $profile['nama_lengkap'] 
        : ($currentUser['username'] ?? 'User');
    
    $userPhoto = ($profile && !empty($profile['foto_profil'])) 
        ? base_url($profile['foto_profil']) 
        : base_url('assets/img/guest.webp');
    
    $userRole = $currentUser['role'] ?? '';
}

// Tentukan dashboard URL berdasarkan role
$dashboardUrl = match($userRole) {
    'admin' => base_url('/admin/dashboard'),
    'staf' => base_url('/staff/dashboard'),
    'user' => base_url('/user/dashboard'),
    default => base_url('/dashboard')
};
?>
<nav class="guest-navbar">
    <div class="container">
        <div class="navbar-content">
            <!-- Logo dan Nama Desa -->
            <div class="navbar-brand-section">
                <a href="<?= base_url('/') ?>" class="navbar-logo-link">
                    <img src="<?= base_url('assets/img/logo.webp') ?>" alt="Logo Desa Padangloang" class="navbar-logo">
                    <span class="navbar-brand-text">Desa Padangloang</span>
                </a>
            </div>

            <!-- Mobile Toggle Button (Highest Priority for Alignment) -->
            <button class="navbar-toggle" type="button" id="navbarToggleBtn" aria-label="Toggle navigation">
                <span class="toggle-icon"></span>
            </button>

            <!-- Menu Navigasi & Actions Wrapper -->
            <div class="navbar-menu">
                <ul class="navbar-nav" id="navbarNav">
                    <li class="nav-item">
                        <a class="nav-link <?= uri_string() == '' || uri_string() == '/' ? 'active' : '' ?>" href="<?= base_url('/') ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(uri_string(), 'profil') !== false ? 'active' : '' ?>" href="<?= base_url('/profil') ?>">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(uri_string(), 'galeri') !== false ? 'active' : '' ?>" href="<?= base_url('/galeri') ?>">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(uri_string(), 'berita') !== false ? 'active' : '' ?>" href="<?= base_url('/berita') ?>">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(uri_string(), 'project') !== false ? 'active' : '' ?>" href="<?= base_url('/project') ?>">Project</a>
                    </li>
                </ul>

                <!-- Tombol Masuk / Profil User (Inside Menu for Mobile) -->
                <div class="navbar-actions ms-lg-4">
                    <?php if (!$isLoggedIn): ?>
                        <a href="<?= base_url('/login') ?>" class="btn-masuk">Masuk</a>
                    <?php else: ?>
                        <div class="user-profile-dropdown">
                            <button class="user-profile-btn" type="button" id="userProfileBtn">
                                <img src="<?= esc($userPhoto) ?>" alt="Profile" class="user-profile-img">
                                <span class="user-profile-name"><?= esc($currentUser['username'] ?? 'User') ?></span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="user-profile-menu" id="userProfileMenu">
                                <a href="<?= $dashboardUrl ?>" class="profile-menu-item">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                                <a href="<?= base_url('/logout') ?>" class="profile-menu-item">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>

