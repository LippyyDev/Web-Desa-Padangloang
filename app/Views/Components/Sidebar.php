<?php
$currentUser = session('user');
$role = $currentUser['role'] ?? '';
$currentUrl = current_url();
$basePath = base_url();

// Menentukan menu berdasarkan role dengan kategori (satu kata)
$menus = match($role) {
    'admin' => [
        'UTAMA' => [
            ['label' => 'Dashboard', 'url' => '/admin/dashboard', 'icon' => 'bi-speedometer2'],
        ],
        'SISTEM' => [
            ['label' => 'Kelola Akun', 'url' => '/admin/akun', 'icon' => 'bi-people'],
        ],
        'AKUN' => [
            ['label' => 'Profil', 'url' => '/admin/profil', 'icon' => 'bi-person'],
        ]
    ],
    'staf' => [
        'UTAMA' => [
            ['label' => 'Dashboard', 'url' => '/staff/dashboard', 'icon' => 'bi-speedometer2'],
        ],
        'LAYANAN' => [
            ['label' => 'Surat Masuk', 'url' => '/staff/surat', 'icon' => 'bi-envelope'],
        ],
        'KONTEN' => [
            ['label' => 'Project', 'url' => '/staff/projects', 'icon' => 'bi-folder'],
            ['label' => 'Galeri', 'url' => '/staff/galeri', 'icon' => 'bi-images'],
            ['label' => 'Berita', 'url' => '/staff/berita', 'icon' => 'bi-newspaper'],
        ],
        'DATA' => [
            ['label' => 'Perangkat Desa', 'url' => '/staff/perangkat-desa', 'icon' => 'bi-people'],
            ['label' => 'Profil Desa', 'url' => '/staff/desa', 'icon' => 'bi-building'],
        ],
        'AKUN' => [
            ['label' => 'Profil', 'url' => '/staff/profil', 'icon' => 'bi-person'],
            ['label' => 'Notifikasi', 'url' => '/staff/notifikasi', 'icon' => 'bi-bell'],
        ]
    ],
    'user' => [
        'UTAMA' => [
            ['label' => 'Dashboard', 'url' => '/user/dashboard', 'icon' => 'bi-speedometer2'],
        ],
        'LAYANAN' => [
            ['label' => 'Surat', 'url' => '/user/surat', 'icon' => 'bi-envelope'],
        ],
        'AKUN' => [
            ['label' => 'Profil', 'url' => '/user/profil', 'icon' => 'bi-person'],
            ['label' => 'Notifikasi', 'url' => '/user/notifikasi', 'icon' => 'bi-bell'],
        ]
    ],
    default => []
};

$brandName = match($role) {
    'admin' => 'Admin Panel',
    'staf' => 'Portal Staf',
    'user' => 'Portal Warga',
    default => 'Portal'
};
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="<?= base_url('assets/img/logolandscape.webp') ?>" alt="Logo" class="logo-img">
        </div>
    </div>
    <div class="sidebar-divider"></div>
    <nav class="sidebar-nav">
        <?php $firstCategory = true; ?>
        <?php foreach ($menus as $category => $items): ?>
            <?php if (!$firstCategory): ?>
                <div class="sidebar-category-divider"></div>
            <?php endif; ?>
            <div class="sidebar-category-label"><?= esc($category) ?></div>
            <?php foreach ($items as $menu): ?>
                <?php
                $menuUrl = base_url($menu['url']);
                // Check if current URL matches menu URL
                $isActive = (strpos($currentUrl, $menu['url']) !== false) || 
                           (strpos($currentUrl, $menuUrl) !== false);
                ?>
                <a href="<?= esc($menuUrl) ?>" class="sidebar-item <?= $isActive ? 'active' : '' ?>">
                    <i class="<?= esc($menu['icon']) ?>"></i>
                    <span><?= esc($menu['label']) ?></span>
                </a>
            <?php endforeach; ?>
            <?php $firstCategory = false; ?>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= base_url('/logout') ?>" class="sidebar-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOpenBtn = document.getElementById('sidebarOpenBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    function openSidebar() {
        sidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    if (sidebarOpenBtn) {
        sidebarOpenBtn.addEventListener('click', openSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    // Close sidebar when clicking outside on mobile
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1400) {
            closeSidebar();
        }
    });
});
</script>

