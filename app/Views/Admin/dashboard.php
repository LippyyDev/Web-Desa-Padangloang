<?= $this->extend('Admin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4>Dashboard</h4>
        <div class="text-muted small">Ringkasan data dan aktivitas sistem.</div>
    </div>
    <div class="page-header-icon">
        <i class="bi bi-speedometer2"></i>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-people h-100">
            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-label">Total Pengguna</div>
            <div class="stat-value"><?= $userCount ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-mail h-100">
            <div class="stat-icon">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="stat-label">Surat</div>
            <div class="stat-value"><?= $letterCount ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-news h-100">
            <div class="stat-icon">
                <i class="bi bi-newspaper"></i>
            </div>
            <div class="stat-label">Berita</div>
            <div class="stat-value"><?= $newsCount ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-project h-100">
            <div class="stat-icon">
                <i class="bi bi-folder-fill"></i>
            </div>
            <div class="stat-label">Project</div>
            <div class="stat-value"><?= $projectCount ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-clock-history"></i>
        Riwayat Akun
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Username</th>
                        <th>Role</th>
                        <th class="pe-4">Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentAccounts)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada akun terdaftar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($recentAccounts as $account): ?>
                        <tr>
                            <td class="ps-4 fw-medium" style="max-width: 130px;">
                                <div class="d-flex align-items-center gap-2">
                                    <?php 
                                        $fotoUrl = !empty($account['foto_profil']) ? base_url($account['foto_profil']) : base_url('assets/img/guest.webp'); 
                                    ?>
                                    <img src="<?= $fotoUrl ?>" alt="Profil" class="rounded-circle flex-shrink-0" style="width: 28px; height: 28px; object-fit: cover;">
                                    <span class="d-inline-block text-truncate" style="max-width: 100%;" title="<?= esc($account['username']) ?>">
                                        <?= esc($account['username']) ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    $badgeClass = match($account['role']) {
                                        'admin' => 'bg-danger text-white',
                                        'staf'  => 'bg-primary text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                    $roleName = $account['role'] === 'staf' ? 'Staf' : ucfirst($account['role']);
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= esc($roleName) ?></span>
                            </td>
                            <td class="pe-4 text-muted small">
                                <?php
                                    // Set locale if needed, fallback to simple date
                                    setlocale(LC_TIME, 'id_ID.utf8');
                                    // Use format like "24 Februari 2026, 09:12"
                                    $months = [
                                        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                    ];
                                    $date = strtotime($account['created_at']);
                                    $monthStr = $months[(int)date('n', $date)];
                                    echo date('d', $date) . ' ' . $monthStr . ' ' . date('Y', $date);
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


