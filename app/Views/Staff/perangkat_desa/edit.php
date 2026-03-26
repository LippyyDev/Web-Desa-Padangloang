<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/staff/perangkat-desa') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Edit Perangkat Desa</h4>
            <div class="text-muted small mt-1">Ubah data perangkat desa.</div>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="<?= base_url('/staff/perangkat-desa/' . $item['id'] . '/hapus') ?>" class="page-header-icon page-header-icon-delete" title="Hapus Perangkat Desa">
            <i class="bi bi-trash"></i>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/perangkat-desa/' . $item['id']) ?>">
            <?= csrf_field() ?>
            <div class="text-center mb-4">
                <div class="profile-photo-container d-inline-block">
                    <input type="file" id="fotoInput" name="foto" accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;">
                    <label for="fotoInput" class="profile-photo-wrapper">
                        <img id="photoPreview" src="<?= !empty($item['foto']) ? base_url($item['foto']) : base_url('assets/img/guest.webp') ?>" class="profile-photo" alt="Foto Profil">
                        <div class="profile-photo-overlay">
                            <i class="bi bi-camera"></i>
                            <span>Ganti Foto</span>
                        </div>
                    </label>
                </div>
                <div class="text-muted small mt-2">Maks. 1 MB (JPEG, JPG, PNG, WEBP)</div>
                <div class="text-danger small mt-1" id="fotoError" style="display:none;"></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama" value="<?= old('nama', esc($item['nama'])) ?>" pattern="[A-Za-z\s.,'-]+" title="Hanya huruf, spasi, titik, koma, dan kutip yang diizinkan" maxlength="150" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="jabatan" value="<?= old('jabatan', esc($item['jabatan'])) ?>" <?= (strtolower($item['jabatan']) === 'kepala desa') ? 'readonly title="Jabatan Kepala Desa tidak dapat diubah"' : '' ?> required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Kontak</label>
                    <input type="text" class="form-control" name="kontak" value="<?= old('kontak', esc($item['kontak'])) ?>" placeholder="Nomor telepon atau email">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
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
    const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1 MB
    const fotoInput    = document.getElementById('fotoInput');
    const photoPreview = document.getElementById('photoPreview');
    const fotoError    = document.getElementById('fotoError');
    const defaultSrc   = photoPreview.src;

    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        fotoError.style.display = 'none';
        if (!file) { photoPreview.src = defaultSrc; return; }
        if (file.size > MAX_FILE_SIZE) {
            fotoError.textContent = 'Ukuran foto terlalu besar (' + (file.size / 1024 / 1024).toFixed(2) + ' MB). Maksimal 1 MB.';
            fotoError.style.display = 'block';
            fotoInput.value = '';
            photoPreview.src = defaultSrc;
            return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) { photoPreview.src = ev.target.result; };
        reader.readAsDataURL(file);
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        if (fotoInput.files[0] && fotoInput.files[0].size > MAX_FILE_SIZE) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Harap perbaiki kesalahan pada form sebelum menyimpan.',
                confirmButtonColor: '#0d6efd'
            });
        }
    });
});
</script>
<?= $this->endSection() ?>
