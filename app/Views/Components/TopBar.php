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
    'admin' => '#', // Admin belum punya route notifikasi
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
            <h5 class="topbar-greeting">
                <?php
                $hour = (int)date('H');
                if ($hour < 12) {
                    echo 'Selamat pagi';
                } elseif ($hour < 15) {
                    echo 'Selamat siang';
                } elseif ($hour < 19) {
                    echo 'Selamat sore';
                } else {
                    echo 'Selamat malam';
                }
                ?>, <?= esc($userName) ?>
            </h5>
            <div class="topbar-date">
                <span id="current-date"></span>
                <span id="current-time"></span>
            </div>
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
            <div class="user-profile">
                <img src="<?= esc($userPhoto) ?>" alt="Profile" class="profile-image">
                <div class="user-info">
                    <span class="user-name"><?= esc($currentUser['username'] ?? 'User') ?></span>
                    <span class="user-role"><?= esc(ucfirst($currentUser['role'] ?? 'User')) ?></span>
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
});
</script>