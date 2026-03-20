<?php
$currentUser = session('user');
if (!$currentUser) {
    return;
}

$userProfileModel = new \App\Models\UserProfileModel();
$notificationModel = new \App\Models\NotificationModel();

$userId = $currentUser['id'] ?? null;
$profile = $userId ? $userProfileModel->find($userId) : null;
$unreadCount = $userId ? $notificationModel->where('user_id', $userId)
    ->where('is_read', 0)
    ->countAllResults() : 0;

$userName = ($profile && !empty($profile['nama_lengkap'])) ? $profile['nama_lengkap'] : ($currentUser['username'] ?? 'User');
$userPhoto = ($profile && !empty($profile['foto_profil'])) 
    ? base_url($profile['foto_profil']) 
    : base_url('assets/img/guest.webp');
$notificationUrl = match($currentUser['role'] ?? '') {
    'admin' => base_url('/admin/notifikasi'),
    'staf' => base_url('/staff/notifikasi'),
    'user' => base_url('/user/notifikasi'),
    default => '#'
};
?>
<div class="topbar">
    <div class="topbar-content">
        <button class="sidebar-toggle-open" id="sidebarOpenBtn">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-left">
            <?php
            $hour = (int)date('H');
            $timeIcon = '';
            $greeting = '';
            if ($hour >= 5 && $hour < 11) {
                $greeting = 'Selamat pagi';
                $timeIcon = 'bi-sunrise';
            } elseif ($hour >= 11 && $hour < 15) {
                $greeting = 'Selamat siang';
                $timeIcon = 'bi-sun';
            } elseif ($hour >= 15 && $hour < 18) {
                $greeting = 'Selamat sore';
                $timeIcon = 'bi-sunset';
            } else {
                $greeting = 'Selamat malam';
                $timeIcon = 'bi-moon-stars';
            }
            ?>
            <div class="topbar-date">
                <i class="bi <?= $timeIcon ?> time-icon"></i>
                <span id="current-date"></span>
                <span id="current-time"></span>
            </div>
            <h5 class="topbar-greeting">
                <?= $greeting ?>, <?= esc($userName) ?>
            </h5>
        </div>
        <div class="topbar-right">
        <a href="<?= base_url('/') ?>" class="home-icon" title="Kembali ke Beranda">
            <i class="bi bi-house-door"></i>
        </a>
            <a href="<?= $notificationUrl ?>" class="notification-icon">
                <i class="bi bi-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                <?php endif; ?>
            </a>
            <div class="user-profile" id="userProfileToggle">
                <img src="<?= esc($userPhoto) ?>" alt="Profile" class="profile-image">
                <div class="user-info">
                    <span class="user-name"><?= esc($currentUser['username'] ?? 'User') ?></span>
                    <span class="user-role"><?= esc(ucfirst($currentUser['role'] ?? 'User')) ?></span>
                </div>
                <i class="bi bi-chevron-down profile-dropdown-icon"></i>
                
                <div class="profile-dropdown-menu" id="profileDropdown">
                    <?php
                    $profileUrl = match($currentUser['role'] ?? '') {
                        'admin' => base_url('/admin/profil'),
                        'staf' => base_url('/staff/profil'),
                        'user' => base_url('/user/profil'),
                        default => '#'
                    };
                    ?>
                    <a href="<?= $profileUrl ?>" class="dropdown-item">
                        <i class="bi bi-person"></i>
                        <span>Profil Saya</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= base_url('/logout') ?>" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    function updateDateTime() {
        const now = new Date();
        const day = days[now.getDay()];
        const date = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const dateElement = document.getElementById('current-date');
        const timeElement = document.getElementById('current-time');
        
        if (dateElement) {
            dateElement.textContent = `${day}, ${date} ${month} ${year}`;
        }
        if (timeElement) {
            timeElement.textContent = `${hours}.${minutes}.${seconds}`;
        }
    }
    
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Profile Dropdown Toggle
    const userProfileToggle = document.getElementById('userProfileToggle');
    if (userProfileToggle) {
        userProfileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userProfileToggle.contains(e.target)) {
                userProfileToggle.classList.remove('active');
            }
        });
    }
});
</script>