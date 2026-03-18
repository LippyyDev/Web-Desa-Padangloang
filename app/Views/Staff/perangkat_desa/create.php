<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/staff/perangkat-desa') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Tambah Perangkat Desa</h4>
            <div class="text-muted small mt-1">Isi data perangkat desa.</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/perangkat-desa') ?>">
            <?= csrf_field() ?>
            <div class="text-center mb-4">
                <div class="profile-photo-container d-inline-block">
                    <input type="file" id="fotoInput" name="foto" accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;">
                    <label for="fotoInput" class="profile-photo-wrapper">
                        <img id="photoPreview" src="<?= base_url('assets/img/guest.webp') ?>" class="profile-photo" alt="Foto Profil">
                        <div class="profile-photo-overlay">
                            <i class="bi bi-camera"></i>
                            <span>Pilih Foto</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama" value="<?= old('nama') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="jabatan" value="<?= old('jabatan') ?>" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Kontak</label>
                    <input type="text" class="form-control" name="kontak" value="<?= old('kontak') ?>" placeholder="Nomor telepon atau email">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
                <a href="<?= base_url('/staff/perangkat-desa') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fotoInput = document.getElementById('fotoInput');
    const photoPreview = document.getElementById('photoPreview');
    
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
