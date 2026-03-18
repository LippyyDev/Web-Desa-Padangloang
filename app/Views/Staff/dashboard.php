<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon">
            <i class="bi bi-speedometer2"></i>
        </div>
        <div>
            <h4 class="mb-0">Dashboard</h4>
            <div class="text-muted small mt-1">Ringkasan aktivitas dan data desa.</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-mail h-100">
            <div class="stat-icon">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
                <div class="stat-label">Surat Masuk</div>
                <div class="stat-value"><?= $incoming ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-gallery h-100">
            <div class="stat-icon">
                <i class="bi bi-images"></i>
            </div>
            <div>
                <div class="stat-label">Galeri</div>
                <div class="stat-value"><?= $galleryTotal ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-news h-100">
            <div class="stat-icon">
                <i class="bi bi-newspaper"></i>
            </div>
            <div>
                <div class="stat-label">Berita</div>
                <div class="stat-value"><?= $newsTotal ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-project h-100">
            <div class="stat-icon">
                <i class="bi bi-folder-fill"></i>
            </div>
            <div>
                <div class="stat-label">Project</div>
                <div class="stat-value"><?= $projectTotal ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-xl">
        <div class="stat-card stat-card-people h-100">
            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-label">Perangkat Desa</div>
                <div class="stat-value"><?= $perangkatTotal ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">Riwayat Surat Masuk</div>
            <div class="small text-muted">Aktivitas terbaru surat masuk</div>
        </div>
        <a href="<?= base_url('/staff/surat') ?>" class="btn btn-sm btn-primary">
            Lihat Semua
        </a>
    </div>
    <div class="list-group list-group-flush">
        <?php if (empty($recentLetters)): ?>
            <div class="list-group-item text-muted small text-center py-4">Belum ada surat masuk.</div>
        <?php else: ?>
            <?php foreach ($recentLetters as $letter): ?>
                <a href="<?= base_url('/staff/surat/' . $letter['id']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark letter-title"><?= esc($letter['judul_perihal']) ?></div>
                        <div class="small text-muted mt-1">Dari: <?= esc($letter['sender_name']) ?></div>
                    </div>
                    <div class="text-end ms-3">
                        <span class="badge rounded-pill bg-<?php 
                            if ($letter['status'] === 'Diterima') echo 'success';
                            elseif ($letter['status'] === 'Ditolak') echo 'danger';
                            elseif ($letter['status'] === 'Dibaca') echo 'primary'; // changed from 'info' to 'primary'
                            else echo 'warning';
                        ?> mb-1 d-block">
                            <?= esc($letter['status']) ?>
                        </span>
                        <div class="small text-muted" style="font-size: 0.75rem;"><?= date('d M Y', strtotime($letter['created_at'])) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom pt-4 pb-3 px-4">
        <div>
            <div class="fw-semibold">Grafik Jumlah Surat Masuk</div>
            <div class="small text-muted mt-1">Statistik jumlah surat masuk selama 6 bulan terakhir</div>
        </div>
    </div>
    <div class="card-body">
        <div id="letterChart" style="min-height: 400px;"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartOptions = {
        series: [
            {
                name: 'Surat Masuk',
                data: <?= json_encode($chartIncoming) ?>
            },
            {
                name: 'Surat Diterima',
                data: <?= json_encode($chartReplied) ?>
            }
        ],
        chart: {
            height: 400,
            type: 'area',
            fontFamily: 'Inter, sans-serif',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        colors: ['#0d6efd', '#198754'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.3,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: <?= json_encode($chartLabels) ?>,
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return Math.floor(val);
                }
            }
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4,
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            offsetY: -10
        }
    };

    const chart = new ApexCharts(document.querySelector("#letterChart"), chartOptions);
    chart.render();
});
</script>
<?= $this->endSection() ?>


