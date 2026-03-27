<?= $this->extend('Admin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/admin/akun') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Edit Akun</h4>
            <div class="text-muted small mt-1">Perbarui data pengguna.</div>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="<?= base_url('/admin/akun/' . $user['id'] . '/hapus') ?>" class="page-header-icon page-header-icon-delete" title="Hapus Akun">
            <i class="bi bi-trash"></i>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/admin/akun/' . $user['id']) ?>">
            <?= csrf_field() ?>
            <div class="text-center mb-4">
                <div class="profile-photo-container d-inline-block">
                    <input type="file" id="fotoProfilInput" name="foto_profil" accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;">
                    <label for="fotoProfilInput" class="profile-photo-wrapper">
                        <img id="profilePhotoPreview" src="<?= !empty($profile['foto_profil']) ? base_url($profile['foto_profil']) : base_url('assets/img/guest.webp') ?>" class="profile-photo" alt="Foto Profil">
                        <div class="profile-photo-overlay">
                            <i class="bi bi-camera"></i>
                            <span>Ganti Foto</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" value="<?= old('username', esc($user['username'])) ?>" maxlength="50" placeholder="Maksimal 50 karakter">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= old('email', esc($user['email'])) ?>" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama_lengkap" value="<?= old('nama_lengkap', esc($profile['nama_lengkap'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <?php foreach (['admin','staf','user'] as $role): ?>
                            <option value="<?= $role ?>" <?= $user['role'] === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="aktif" <?= $user['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= $user['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" class="form-control" name="tempat_lahir" value="<?= old('tempat_lahir', esc($profile['tempat_lahir'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Lahir</label>
                    <?php
                        $tanggalLahirEdit = $profile['tanggal_lahir'] ?? '';
                        if ($tanggalLahirEdit === '0000-00-00' || empty($tanggalLahirEdit)) {
                            $tanggalLahirEdit = '';
                        }
                    ?>
                    <input type="date" class="form-control" name="tanggal_lahir" value="<?= old('tanggal_lahir', esc($tanggalLahirEdit)) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Agama</label>
                    <input type="text" class="form-control input-letters-only" name="agama" value="<?= old('agama', esc($profile['agama'] ?? '')) ?>" maxlength="50" placeholder="Hanya huruf">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" class="form-control input-letters-only" name="pekerjaan" value="<?= old('pekerjaan', esc($profile['pekerjaan'] ?? '')) ?>" maxlength="100" placeholder="Hanya huruf">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK</label>
                    <input type="text" class="form-control input-digits-only" name="nik" value="<?= old('nik', esc($profile['nik'] ?? '')) ?>" maxlength="16" minlength="16" inputmode="numeric" placeholder="16 digit angka">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea class="form-control" name="alamat" rows="2"><?= old('alamat', esc($profile['alamat'] ?? '')) ?></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock"></i>
            Ganti Password
        </h5>
        <form method="post" action="<?= base_url('/admin/akun/' . $user['id'] . '/ubah-password') ?>">
            <?= csrf_field() ?>
            <input type="text" name="username_hint" value="<?= esc($user['username']) ?>" autocomplete="username" style="display:none;" aria-hidden="true">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Masukkan Password Baru</label>
                    <input type="password" class="form-control" name="new_password" autocomplete="new-password" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" name="confirm_password" autocomplete="new-password" required>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-warning" type="submit">
                    <i class="bi bi-key"></i> Ganti Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fotoInput = document.getElementById('fotoProfilInput');
    const photoPreview = document.getElementById('profilePhotoPreview');

    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 1048576) {
                Swal.fire({ icon: 'error', title: 'File terlalu besar', text: 'Ukuran foto profil maksimal 1 MB.' });
                this.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Hanya boleh huruf dan spasi untuk input agama & pekerjaan
    document.querySelectorAll('.input-letters-only').forEach(function(el) {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
        });
        el.addEventListener('keypress', function(e) {
            if (!/[a-zA-Z\s]/.test(e.key)) e.preventDefault();
        });
    });

    // Hanya boleh angka untuk NIK, maksimal 16 digit
    document.querySelectorAll('.input-digits-only').forEach(function(el) {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 16);
        });
        el.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
    });
});
</script>
<?= $this->endSection() ?>
