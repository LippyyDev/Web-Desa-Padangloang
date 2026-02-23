<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4>Edit Album</h4>
        <div class="text-muted small">Perbarui informasi album.</div>
    </div>
    <div class="page-header-actions">
        <a href="<?= base_url('/staff/galeri/' . $album['id'] . '/hapus') ?>" class="page-header-icon page-header-icon-delete" title="Hapus Album">
            <i class="bi bi-trash"></i>
        </a>
        <a href="<?= base_url('/staff/galeri') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/galeri/' . $album['id']) ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Album</label>
                    <input type="text" class="form-control" name="nama_album" value="<?= esc($album['nama_album']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal & Waktu</label>
                    <input type="datetime-local" class="form-control" name="tanggal_waktu" value="<?= date('Y-m-d\TH:i', strtotime($album['tanggal_waktu'])) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Thumbnail (opsional)</label>
                    <input type="file" class="form-control" id="thumbnailInput" name="thumbnail" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <div id="thumbnailPreview" class="mt-2">
                        <?php if (!empty($album['thumbnail'])): ?>
                            <img src="<?= base_url($album['thumbnail']) ?>" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="2"><?= esc($album['deskripsi'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tambah Foto</label>
                    <input type="file" class="form-control" id="mediaInput" name="media[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp">
                    <div id="mediaPreview" class="row g-2 mt-2"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Video</label>
                    <div id="video-list"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-video">Tambah Link</button>
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
                            <div class="border rounded p-2 text-center position-relative" style="overflow: visible;">
                                <a href="<?= base_url('/staff/galeri/media/' . $m['id'] . '/hapus') ?>" class="btn btn-sm btn-danger position-absolute" style="top: 4px; right: 4px; z-index: 1000; width: 28px; height: 28px; padding: 0; line-height: 1; border-radius: 50%; display: flex; align-items: center; justify-content: center;">×</a>
                                <?php if ($m['media_type'] === 'foto'): ?>
                                    <img src="<?= base_url($m['media_path']) ?>" class="img-fluid rounded" style="max-width: 100%; max-height: 150px; object-fit: cover;" alt="media">
                                <?php else: ?>
                                    <div class="ratio ratio-16x9 mb-2 position-relative" style="z-index: 1; pointer-events: none;">
                                        <iframe src="<?= esc($m['embed_url'] ?? $m['media_path']) ?>" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" style="pointer-events: auto;"></iframe>
                                    </div>
                                    <div class="small text-muted text-truncate"><?= esc($m['media_path']) ?></div>
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
        // Preview Thumbnail
        const thumbnailInput = document.getElementById('thumbnailInput');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        
        thumbnailInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    thumbnailPreview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">';
                };
                reader.readAsDataURL(file);
            } else {
                // Kembalikan ke thumbnail lama jika ada
                const oldThumbnail = thumbnailPreview.querySelector('img');
                if (oldThumbnail) {
                    thumbnailPreview.innerHTML = oldThumbnail.outerHTML;
                }
            }
        });

        // Preview Media Foto
        const mediaInput = document.getElementById('mediaInput');
        const mediaPreview = document.getElementById('mediaPreview');
        
        mediaInput.addEventListener('change', function(e) {
            mediaPreview.innerHTML = '';
            const files = e.target.files;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-3';
                        col.innerHTML = `
                            <div class="border rounded p-2 text-center">
                                <img src="${e.target.result}" class="img-fluid rounded" style="max-width: 100%; max-height: 150px; object-fit: cover;" alt="preview">
                            </div>
                        `;
                        mediaPreview.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Video Links
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
