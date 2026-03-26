<?= $this->extend('Admin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon">
            <i class="bi bi-person"></i>
        </div>
        <div>
            <h4 class="mb-0">Profil Admin</h4>
            <div class="text-muted small mt-1">Perbarui identitas admin.</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/admin/profil') ?>">
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
                    <input type="text" class="form-control" name="username" value="<?= old('username', session('user.username')) ?>" maxlength="50" placeholder="Maksimal 50 karakter">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= old('email', $user['email'] ?? '') ?>" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama_lengkap" value="<?= old('nama_lengkap', $profile['nama_lengkap'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" class="form-control input-no-numbers" name="tempat_lahir" value="<?= old('tempat_lahir', $profile['tempat_lahir'] ?? '') ?>" placeholder="Hanya huruf dan simbol">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Lahir</label>
                    <?php 
                        $tanggalLahir = $profile['tanggal_lahir'] ?? '';
                        // Filter out invalid dates like "0000-00-00"
                        if ($tanggalLahir === '0000-00-00' || empty($tanggalLahir)) {
                            $tanggalLahir = '';
                        }
                    ?>
                    <input type="date" class="form-control" name="tanggal_lahir" value="<?= old('tanggal_lahir', $tanggalLahir) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jenis Kelamin</label>
                    <select class="form-select" name="jenis_kelamin">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-laki" <?= (isset($profile['jenis_kelamin']) && $profile['jenis_kelamin'] === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= (isset($profile['jenis_kelamin']) && $profile['jenis_kelamin'] === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Agama</label>
                    <input type="text" class="form-control input-letters-only" name="agama" value="<?= old('agama', $profile['agama'] ?? '') ?>" maxlength="50" placeholder="Hanya huruf">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" class="form-control input-letters-only" name="pekerjaan" value="<?= old('pekerjaan', $profile['pekerjaan'] ?? '') ?>" maxlength="100" placeholder="Hanya huruf">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK</label>
                    <input type="text" class="form-control input-digits-only" name="nik" value="<?= old('nik', $profile['nik'] ?? '') ?>" maxlength="16" minlength="16" inputmode="numeric" placeholder="16 digit angka">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Alamat</label>
                    <textarea class="form-control" name="alamat" rows="1"><?= old('alamat', $profile['alamat'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Simpan Profil
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
        <form method="post" action="<?= base_url('/admin/profil/ubah-password') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Masukkan Password Lama</label>
                    <div class="input-group password-group">
                        <input type="password" class="form-control" name="old_password" required>
                        <button class="btn btn-outline-secondary btn-toggle-pass" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Masukkan Password Baru</label>
                    <div class="input-group password-group">
                        <input type="password" class="form-control" name="new_password" required>
                        <button class="btn btn-outline-secondary btn-toggle-pass" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-group password-group">
                        <input type="password" class="form-control" name="confirm_password" required>
                        <button class="btn btn-outline-secondary btn-toggle-pass" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
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

    // Hanya boleh huruf dan simbol (tanpa angka) untuk tempat lahir
    document.querySelectorAll('.input-no-numbers').forEach(function(el) {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/[0-9]/g, '');
        });
        el.addEventListener('keypress', function(e) {
            if (/[0-9]/.test(e.key)) e.preventDefault();
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

    // Toggle Password Visibility
    document.querySelectorAll('.btn-toggle-pass').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>


