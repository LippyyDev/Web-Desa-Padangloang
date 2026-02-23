<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4>Edit Project</h4>
        <div class="text-muted small">Perbarui detail project.</div>
    </div>
    <div class="page-header-actions">
        <a href="<?= base_url('/staff/projects/' . $project['id'] . '/hapus') ?>" class="page-header-icon page-header-icon-delete" title="Hapus Project">
            <i class="bi bi-trash"></i>
        </a>
        <a href="<?= base_url('/staff/projects') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/projects/' . $project['id']) ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul</label>
                    <input type="text" class="form-control" name="judul" value="<?= esc($project['judul']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="datetime-local" class="form-control" name="tanggal_waktu" value="<?= date('Y-m-d\TH:i', strtotime($project['tanggal_waktu'])) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <?php foreach (['Perencanaan','Proses','Ditunda','Selesai'] as $status): ?>
                            <option value="<?= $status ?>" <?= $project['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Anggaran</label>
                    <input type="number" class="form-control" name="anggaran" value="<?= esc($project['anggaran']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Thumbnail (opsional)</label>
                    <input type="file" class="form-control" name="thumbnail">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tambah Foto</label>
                    <input type="file" class="form-control" name="media[]" multiple>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Video</label>
                    <div id="video-list"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-video">Tambah Link</button>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="4" required><?= esc($project['deskripsi']) ?></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Update
                </button>
            </div>
        </form>

        <?php if (!empty($media)): ?>
            <div class="mt-4">
                <div class="fw-semibold mb-2">Media Saat Ini</div>
                <div class="row g-2">
                    <?php foreach ($media as $m): ?>
                        <div class="col-md-3">
                            <div class="border rounded p-2 text-center position-relative">
                                <a href="<?= base_url('/staff/projects/media/' . $m['id'] . '/hapus') ?>" class="btn-close position-absolute top-0 end-0 m-1 bg-white"></a>
                                <?php if (isset($m['media_type']) && $m['media_type'] === 'video_link' && isset($m['embed_url'])): ?>
                                    <div class="ratio ratio-16x9">
                                        <iframe src="<?= esc($m['embed_url']) ?>" allowfullscreen></iframe>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= base_url($m['media_path']) ?>" class="img-fluid rounded" alt="media">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('video-list');
        const addBtn = document.getElementById('add-video');
        const addField = (value = '') => {
            const group = document.createElement('div');
            group.className = 'input-group mb-2 video-item';
            group.innerHTML = `
                <input type="text" class="form-control" name="video_links[]" placeholder="https://youtube.com/..." value="${value}">
                <button type="button" class="btn btn-outline-danger">Hapus</button>
            `;
            group.querySelector('button').addEventListener('click', () => group.remove());
            list.appendChild(group);
        };
        addBtn.addEventListener('click', () => addField(''));
        addField('');
    });
</script>
<?= $this->endSection() ?>


