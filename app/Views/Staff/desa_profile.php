<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon">
            <i class="bi bi-building"></i>
        </div>
        <div>
            <h4 class="mb-0">Profil Desa</h4>
            <div class="text-muted small mt-1">Perbarui informasi resmi desa.</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= base_url('/staff/desa') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">Visi</label>
                    <textarea class="form-control" name="visi" rows="3"><?= esc($profile['visi'] ?? '') ?></textarea>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Misi</label>
                    <textarea class="form-control" name="misi" rows="3"><?= esc($profile['misi'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <label class="form-label">Jumlah Penduduk</label>
                    <input type="number" class="form-control" name="jumlah_penduduk" value="<?= esc($profile['jumlah_penduduk'] ?? 0) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jumlah KK</label>
                    <input type="number" class="form-control" name="jumlah_kk" value="<?= esc($profile['jumlah_kk'] ?? 0) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Penduduk Sementara</label>
                    <input type="number" class="form-control" name="penduduk_sementara" value="<?= esc($profile['penduduk_sementara'] ?? 0) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Laki-laki</label>
                    <input type="number" class="form-control" name="jumlah_laki" value="<?= esc($profile['jumlah_laki'] ?? 0) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Perempuan</label>
                    <input type="number" class="form-control" name="jumlah_perempuan" value="<?= esc($profile['jumlah_perempuan'] ?? 0) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mutasi Penduduk</label>
                    <input type="number" class="form-control" name="mutasi_penduduk" value="<?= esc($profile['mutasi_penduduk'] ?? 0) ?>">
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <label class="form-label">Kontak WA</label>
                    <input type="text" class="form-control" name="kontak_wa" value="<?= esc($profile['kontak_wa'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="kontak_email" value="<?= esc($profile['kontak_email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Alamat Kantor</label>
                    <input type="text" class="form-control" name="alamat_kantor" value="<?= esc($profile['alamat_kantor'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Google Maps Embed</label>
                    <input type="text" class="form-control" name="maps_url" value="<?= esc($profile['maps_url'] ?? '') ?>" placeholder="https://www.google.com/maps/embed?pb=...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Deskripsi Lokasi</label>
                    <input type="text" class="form-control" name="deskripsi_lokasi" value="<?= esc($profile['deskripsi_lokasi'] ?? '') ?>">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Simpan Profil Desa
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>


