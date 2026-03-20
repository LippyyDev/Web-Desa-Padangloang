<?= $this->extend('Admin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/admin/akun') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Tambah Akun</h4>
            <div class="text-muted small mt-1">Buat akun baru dengan role tertentu.</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= base_url('/admin/akun') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama_lengkap" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required maxlength="50" placeholder="Maksimal 50 karakter">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <option value="admin">Admin</option>
                        <option value="staf">Staf</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Buat Akun
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>


