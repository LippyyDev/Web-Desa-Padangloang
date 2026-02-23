<?= $this->extend('User/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4>Profil Saya</h4>
        <div class="text-muted small">Perbarui informasi data diri Anda.</div>
    </div>
    <div class="page-header-icon">
        <i class="bi bi-person"></i>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/user/profil') ?>">
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
                    <input type="text" class="form-control" name="username" value="<?= old('username', session('user.username')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= old('email', $user['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama_lengkap" value="<?= old('nama_lengkap', $profile['nama_lengkap'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" class="form-control" name="tempat_lahir" value="<?= old('tempat_lahir', $profile['tempat_lahir'] ?? '') ?>">
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
                    <input type="text" class="form-control" name="agama" value="<?= old('agama', $profile['agama'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" class="form-control" name="pekerjaan" value="<?= old('pekerjaan', $profile['pekerjaan'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK</label>
                    <input type="text" class="form-control" name="nik" value="<?= old('nik', $profile['nik'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea class="form-control" name="alamat" rows="3"><?= old('alamat', $profile['alamat'] ?? '') ?></textarea>
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
        <form method="post" action="<?= base_url('/user/profil/ubah-password') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <?php if (!empty($user['password_hash'])): ?>
                <div class="col-md-12">
                    <label class="form-label">Masukkan Password Lama</label>
                    <input type="password" class="form-control" name="old_password" required>
                </div>
                <?php endif; ?>
                <div class="col-md-6">
                    <label class="form-label">Masukkan Password Baru</label>
                    <input type="password" class="form-control" name="new_password" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" name="confirm_password" required>
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
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
<?= $this->endSection() ?>


