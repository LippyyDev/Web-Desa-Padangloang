<?= $this->extend('User/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4>Dashboard</h4>
        <div class="text-muted small">Ringkasan surat dan notifikasi Anda.</div>
    </div>
    <div class="page-header-icon">
        <i class="bi bi-speedometer2"></i>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-mail h-100">
            <div class="stat-icon">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="stat-label">Total Surat</div>
            <div class="stat-value"><?= $totalLetters ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-sent h-100">
            <div class="stat-icon">
                <i class="bi bi-send-fill"></i>
            </div>
            <div class="stat-label">Terkirim</div>
            <div class="stat-value"><?= $sentCount ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-read h-100">
            <div class="stat-icon">
                <i class="bi bi-eye-fill"></i>
            </div>
            <div class="stat-label">Dibaca</div>
            <div class="stat-value"><?= $readCount ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-reply h-100">
            <div class="stat-icon">
                <i class="bi bi-reply-fill"></i>
            </div>
            <div class="stat-label">Diterima</div>
            <div class="stat-value"><?= $repliedCount ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history"></i>
            <div>
                <div class="fw-semibold">Riwayat Surat</div>
                <div class="small text-muted">Aktivitas terbaru surat Anda</div>
            </div>
        </div>
        <a href="<?= base_url('/user/notifikasi') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-right"></i> Lihat Semua
        </a>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($notifications as $notif): ?>
            <?php $notifUrl = base_url('/user/notifikasi/' . $notif['id'] . '/read'); ?>
            <a href="<?= $notifUrl ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start <?= !(bool)$notif['is_read'] ? 'bg-light' : '' ?>">
                <div>
                    <div class="fw-semibold <?= !(bool)$notif['is_read'] ? 'text-primary' : 'text-dark' ?>">
                        <?= esc($notif['title']) ?>
                    </div>
                    <div class="small text-muted mt-1"><?= esc($notif['message']) ?></div>
                </div>
                <div class="text-end">
                    <div class="small text-muted"><?= date('d M Y', strtotime($notif['created_at'])) ?></div>
                    <?php if (!(bool) $notif['is_read']): ?>
                        <span class="badge bg-primary rounded-pill mt-2">Baru</span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($notifications)): ?>
            <div class="list-group-item text-muted small">Belum ada riwayat surat.</div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>


