<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Tambah Album</h4>
        <div class="text-muted small">Isi detail album dan media.</div>
    </div>
    <a href="<?= base_url('/staff/galeri') ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/galeri') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Album</label>
                    <input type="text" class="form-control" name="nama_album" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal & Waktu</label>
                    <input type="datetime-local" class="form-control" name="tanggal_waktu">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Thumbnail</label>
                    <input type="file" class="form-control" name="thumbnail">
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Upload Foto</label>
                    <input type="file" class="form-control" name="media[]" multiple>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Video</label>
                    <div id="video-list">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="video_links[]" placeholder="https://youtube.com/...">
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-video">Tambah Link</button>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" type="submit">Simpan Album</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('video-list');
        const addBtn = document.getElementById('add-video');
        addBtn.addEventListener('click', function () {
            const group = document.createElement('div');
            group.className = 'input-group mb-2';
            group.innerHTML = '<input type="text" class="form-control" name="video_links[]" placeholder="https://youtube.com/...">';
            list.appendChild(group);
        });
    });
</script>
<?= $this->endSection() ?>

