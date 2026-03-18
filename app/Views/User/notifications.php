<?= $this->extend('User/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon">
            <i class="bi bi-bell"></i>
        </div>
        <div>
            <h4 class="mb-0">Notifikasi</h4>
            <div class="text-muted small mt-1">Status terbaru surat dan balasan staf.</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
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
                <div class="small text-muted"><?= date('d M Y H:i', strtotime($notif['created_at'])) ?> WITA</div>
                <?php if (!(bool) $notif['is_read']): ?>
                    <span class="badge bg-primary rounded-pill mt-2">Baru</span>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
    <?php if (empty($notifications)): ?>
        <div class="list-group-item text-muted small text-center py-4">Belum ada notifikasi.</div>
    <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


