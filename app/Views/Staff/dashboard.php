<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4>Dashboard</h4>
        <div class="text-muted small">Ringkasan aktivitas dan data desa.</div>
    </div>
    <div class="page-header-icon">
        <i class="bi bi-speedometer2"></i>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-mail h-100">
            <div class="stat-icon">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="stat-label">Surat Masuk</div>
            <div class="stat-value"><?= $incoming ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-gallery h-100">
            <div class="stat-icon">
                <i class="bi bi-images"></i>
            </div>
            <div class="stat-label">Galeri</div>
            <div class="stat-value"><?= $galleryTotal ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-news h-100">
            <div class="stat-icon">
                <i class="bi bi-newspaper"></i>
            </div>
            <div class="stat-label">Berita</div>
            <div class="stat-value"><?= $newsTotal ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-project h-100">
            <div class="stat-icon">
                <i class="bi bi-folder-fill"></i>
            </div>
            <div class="stat-label">Project</div>
            <div class="stat-value"><?= $projectTotal ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-people h-100">
            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-label">Perangkat Desa</div>
            <div class="stat-value"><?= $perangkatTotal ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-clock-history"></i>
            Riwayat Surat Masuk
        </span>
        <a href="<?= base_url('/staff/surat') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-right"></i> Lihat Semua
        </a>
    </div>
    <div class="list-group list-group-flush">
        <?php if (!empty($recentLetters)): ?>
            <?php foreach ($recentLetters as $letter): ?>
                <a href="<?= base_url('/staff/surat/' . $letter['id']) ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= esc($letter['judul_perihal']) ?></div>
                            <div class="small text-muted">Dari: <?= esc($letter['sender_name']) ?></div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-<?php 
                                if ($letter['status'] === 'Diterima') echo 'success';
                                elseif ($letter['status'] === 'Ditolak') echo 'danger';
                                elseif ($letter['status'] === 'Dibaca') echo 'info';
                                else echo 'warning';
                            ?> mb-2">
                                <?= esc($letter['status']) ?>
                            </span>
                            <div class="small text-muted"><?= date('d M Y', strtotime($letter['created_at'])) ?></div>
                </div>
            </div>
                </a>
        <?php endforeach; ?>
        <?php else: ?>
            <div class="list-group-item text-muted small">Belum ada surat masuk.</div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-bar-chart-fill"></i>
        Grafik Jumlah Surat Masuk (6 Bulan Terakhir)
    </div>
    <div class="card-body">
        <canvas id="letterChart" style="max-height: 400px;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('letterChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    {
                        label: 'Surat Masuk',
                        data: <?= json_encode($chartIncoming) ?>,
                        borderColor: 'rgb(13, 110, 253)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Surat Diterima',
                        data: <?= json_encode($chartReplied) ?>,
                        borderColor: 'rgb(25, 135, 84)',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
<?= $this->endSection() ?>


