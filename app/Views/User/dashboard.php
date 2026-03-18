<?= $this->extend('User/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon">
            <i class="bi bi-speedometer2"></i>
        </div>
        <div>
            <h4 class="mb-0">Dashboard</h4>
            <div class="text-muted small mt-1">Ringkasan surat dan notifikasi Anda.</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-mail h-100">
            <div class="stat-icon">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
                <div class="stat-label">Total Surat</div>
                <div class="stat-value"><?= $totalLetters ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-sent h-100">
            <div class="stat-icon">
                <i class="bi bi-send-fill"></i>
            </div>
            <div>
                <div class="stat-label">Terkirim</div>
                <div class="stat-value"><?= $sentCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-read h-100">
            <div class="stat-icon">
                <i class="bi bi-eye-fill"></i>
            </div>
            <div>
                <div class="stat-label">Dibaca</div>
                <div class="stat-value"><?= $readCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-reply h-100">
            <div class="stat-icon">
                <i class="bi bi-reply-fill"></i>
            </div>
            <div>
                <div class="stat-label">Diterima</div>
                <div class="stat-value"><?= $repliedCount ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">Riwayat Surat</div>
            <div class="small text-muted">Aktivitas terbaru surat Anda</div>
        </div>
        <a href="<?= base_url('/user/surat') ?>" class="btn btn-primary btn-sm">
            Lihat Semua
        </a>
    </div>
    <div class="list-group list-group-flush">
        <?php if (empty($recentLetters)): ?>
            <div class="list-group-item text-muted small text-center py-4">Belum ada riwayat surat.</div>
        <?php else: ?>
            <?php foreach ($recentLetters as $letter): ?>
                <a href="<?= base_url('/user/surat/' . $letter['id']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark letter-title">
                            <?= esc($letter['judul_perihal']) ?>
                        </div>
                        <div class="small text-muted mt-1">Jenis Surat: <?= esc($letter['tipe_surat']) ?></div>
                    </div>
                    <div class="text-end ms-3">
                        <span class="badge rounded-pill bg-<?php 
                            if ($letter['status'] === 'Diterima') echo 'success';
                            elseif ($letter['status'] === 'Ditolak') echo 'danger';
                            elseif ($letter['status'] === 'Dibaca') echo 'primary';
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
            <div class="fw-semibold">Statistik Surat Anda</div>
            <div class="small text-muted mt-1">Status surat dalam 6 bulan terakhir</div>
        </div>
    </div>
    <div class="card-body">
        <div id="userLetterChart" style="min-height: 400px;"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartOptions = {
        series: [
            {
                name: 'Menunggu',
                data: <?= json_encode($chartMenunggu) ?>
            },
            {
                name: 'Dibaca',
                data: <?= json_encode($chartDibaca) ?>
            },
            {
                name: 'Diterima',
                data: <?= json_encode($chartDiterima) ?>
            },
            {
                name: 'Ditolak',
                data: <?= json_encode($chartDitolak) ?>
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
        // warning, primary, success, danger
        colors: ['#ffc107', '#0d6efd', '#198754', '#dc3545'],
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

    const chart = new ApexCharts(document.querySelector("#userLetterChart"), chartOptions);
    chart.render();
});
</script>
<?= $this->endSection() ?>
