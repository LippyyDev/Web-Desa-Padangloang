<?= $this->extend('Admin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon">
            <i class="bi bi-speedometer2"></i>
        </div>
        <div>
            <h4 class="mb-0">Dashboard</h4>
            <div class="text-muted small mt-1">Ringkasan data dan aktivitas sistem.</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-people h-100">
            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-label">Total Pengguna</div>
                <div class="stat-value"><?= $userCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-mail h-100">
            <div class="stat-icon">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
                <div class="stat-label">Surat</div>
                <div class="stat-value"><?= $letterCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-news h-100">
            <div class="stat-icon">
                <i class="bi bi-newspaper"></i>
            </div>
            <div>
                <div class="stat-label">Berita</div>
                <div class="stat-value"><?= $newsCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-project h-100">
            <div class="stat-icon">
                <i class="bi bi-folder-fill"></i>
            </div>
            <div>
                <div class="stat-label">Project</div>
                <div class="stat-value"><?= $projectCount ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">Riwayat Akun</div>
            <div class="small text-muted">Daftar akun terbaru yang terdaftar</div>
        </div>
        <a href="<?= base_url('/admin/accounts') ?>" class="btn btn-sm btn-primary">
            Lihat Semua
        </a>
    </div>
    <div class="list-group list-group-flush">
        <?php if (empty($recentAccounts)): ?>
            <div class="list-group-item text-muted small text-center py-4">Belum ada akun terdaftar.</div>
        <?php else: ?>
            <?php foreach($recentAccounts as $account): ?>
                <div class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 d-flex gap-3 align-items-center">
                        <?php 
                            $fotoUrl = !empty($account['foto_profil']) ? base_url($account['foto_profil']) : base_url('assets/img/guest.webp'); 
                        ?>
                        <img src="<?= $fotoUrl ?>" alt="Profil" class="rounded-circle flex-shrink-0" style="width: 36px; height: 36px; object-fit: cover;">
                        <div>
                            <div class="fw-semibold text-dark"><?= esc($account['username']) ?></div>
                            <div class="small text-muted mt-1">Akun Terdaftar</div>
                        </div>
                    </div>
                    <div class="text-end ms-3">
                        <?php 
                            $badgeClass = match($account['role']) {
                                'admin' => 'bg-danger',
                                'staf'  => 'bg-primary',
                                default => 'bg-secondary'
                            };
                            $roleName = $account['role'] === 'staf' ? 'Staf' : ucfirst($account['role']);
                        ?>
                        <span class="badge rounded-pill <?= $badgeClass ?> mb-1 d-block"><?= esc($roleName) ?></span>
                        <div class="small text-muted" style="font-size: 0.75rem;">
                            <?= date('d M Y', strtotime($account['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom pt-4 pb-3 px-4">
        <div>
            <div class="fw-semibold">Grafik Pertumbuhan Pengguna</div>
            <div class="small text-muted mt-1">Statistik jumlah akun terdaftar selama 6 bulan terakhir</div>
        </div>
    </div>
    <div class="card-body">
        <div id="adminAccountChart" style="min-height: 400px;"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartOptions = {
        series: [
            {
                name: 'Akun Terdaftar',
                data: <?= json_encode($chartAccounts) ?>
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
        colors: ['#6f42c1'], // purple
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

    const chart = new ApexCharts(document.querySelector("#adminAccountChart"), chartOptions);
    chart.render();
});
</script>
<?= $this->endSection() ?>


