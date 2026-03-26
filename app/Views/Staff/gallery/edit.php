<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/staff/galeri') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Edit Album</h4>
            <div class="text-muted small mt-1">Perbarui informasi album.</div>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="<?= base_url('/staff/galeri/' . $album['id'] . '/hapus') ?>" class="page-header-icon page-header-icon-delete" title="Hapus Album">
            <i class="bi bi-trash"></i>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/galeri/' . $album['id']) ?>" id="albumForm">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Album <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_album" id="namaAlbumInput" maxlength="100" value="<?= esc($album['nama_album']) ?>" required>
                    <div class="d-flex justify-content-end mt-1">
                        <small class="text-muted"><span id="namaAlbumCount">0</span>/100 karakter</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal &amp; Waktu</label>
                    <input type="datetime-local" class="form-control" name="tanggal_waktu" value="<?= date('Y-m-d\TH:i', strtotime($album['tanggal_waktu'])) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Thumbnail (opsional)</label>
                    <input type="file" class="form-control" id="thumbnailInput" name="thumbnail" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <small class="text-muted">Maks. 1 MB (JPEG, JPG, PNG, WEBP)</small>
                    <div class="text-danger small mt-1" id="thumbnailError" style="display:none;"></div>
                    <div id="thumbnailPreview" class="mt-2">
                        <?php if (!empty($album['thumbnail'])): ?>
                            <img src="<?= base_url($album['thumbnail']) ?>" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" id="deskripsiInput" rows="2" maxlength="500"><?= esc($album['deskripsi'] ?? '') ?></textarea>
                    <div class="d-flex justify-content-end mt-1">
                        <small class="text-muted"><span id="deskripsiCount">0</span>/500 karakter</small>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Tambah Foto</label>
                    <input type="file" class="form-control" id="mediaInput" name="media[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp">
                    <small class="text-muted">Maks. 1 MB per foto (JPEG, JPG, PNG, WEBP)</small>
                    <div class="text-danger small mt-1" id="mediaError" style="display:none;"></div>
                    <div id="mediaPreview" class="row g-2 mt-2"></div>
                </div>
                <div class="col-12">
                    <label class="form-label">Link Video</label>
                    <div id="video-list"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-video">Tambah Link</button>
                    <small class="text-muted d-block mt-1">Hanya link YouTube (youtube.com / youtu.be) yang diizinkan</small>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($media)): ?>
<div class="card mt-4">
    <div class="card-body">
        <div class="fw-semibold mb-3 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
            <i class="bi bi-images" style="font-size: 1rem;"></i>
            Media Saat Ini
        </div>
        <div class="row g-2">
            <?php foreach ($media as $m): ?>
                <div class="col-md-3">
                    <div class="border rounded p-2 text-center position-relative" style="overflow: visible;">
                        <a href="<?= base_url('/staff/galeri/media/' . $m['id'] . '/hapus') ?>" class="btn btn-sm btn-danger position-absolute" style="top: 4px; right: 4px; z-index: 1000; width: 28px; height: 28px; padding: 0; line-height: 1; border-radius: 50%; display: flex; align-items: center; justify-content: center;">&times;</a>
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
</div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1 MB

        // ── Nama Album character counter (pre-fill on load) ──────────────
        const namaAlbumInput = document.getElementById('namaAlbumInput');
        const namaAlbumCount = document.getElementById('namaAlbumCount');
        namaAlbumCount.textContent = namaAlbumInput.value.length;
        namaAlbumInput.addEventListener('input', function () {
            namaAlbumCount.textContent = namaAlbumInput.value.length;
        });

        // ── Deskripsi character counter (pre-fill on load) ───────────────
        const deskripsiInput = document.getElementById('deskripsiInput');
        const deskripsiCount = document.getElementById('deskripsiCount');
        deskripsiCount.textContent = deskripsiInput.value.length;
        deskripsiInput.addEventListener('input', function () {
            deskripsiCount.textContent = deskripsiInput.value.length;
        });

        // ── Thumbnail preview + size validation ─────────────────────────
        const thumbnailInput   = document.getElementById('thumbnailInput');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const thumbnailError   = document.getElementById('thumbnailError');
        const oldThumbnailHTML = thumbnailPreview.innerHTML;
        thumbnailInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            thumbnailError.style.display = 'none';
            if (!file) {
                thumbnailPreview.innerHTML = oldThumbnailHTML;
                return;
            }
            if (file.size > MAX_FILE_SIZE) {
                thumbnailError.textContent = 'Ukuran thumbnail terlalu besar (' + (file.size / 1024 / 1024).toFixed(2) + ' MB). Maksimal 1 MB.';
                thumbnailError.style.display = 'block';
                thumbnailInput.value = '';
                thumbnailPreview.innerHTML = oldThumbnailHTML;
                return;
            }
            const reader = new FileReader();
            reader.onload = function (ev) {
                thumbnailPreview.innerHTML = '<img src="' + ev.target.result + '" class="img-thumbnail" style="max-width:200px;max-height:200px;object-fit:cover;">';
            };
            reader.readAsDataURL(file);
        });

        // ── Media Foto preview + size validation ────────────────────────
        const mediaInput   = document.getElementById('mediaInput');
        const mediaPreview = document.getElementById('mediaPreview');
        const mediaError   = document.getElementById('mediaError');
        let selectedFiles  = [];
        let fileIdCounter  = 0;

        function updateFileInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(function (f) { dt.items.add(f.file); });
            mediaInput.files = dt.files;
        }
        function renderPreview() {
            mediaPreview.innerHTML = '';
            selectedFiles.forEach(function (fileObj) {
                if (!fileObj.file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function (ev) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3';
                    col.setAttribute('data-file-id', fileObj.id);
                    col.innerHTML =
                        '<div class="border rounded p-2 text-center position-relative">' +
                            '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="width:28px;height:28px;padding:0;line-height:1;border-radius:50%;" onclick="removeFile(\'' + fileObj.id + '\')" title="Hapus">' +
                                '<span style="font-size:18px;">&times;</span>' +
                            '</button>' +
                            '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-width:100%;max-height:150px;object-fit:cover;" alt="preview">' +
                        '</div>';
                    mediaPreview.appendChild(col);
                };
                reader.readAsDataURL(fileObj.file);
            });
        }
        function removeFile(fileId) {
            selectedFiles = selectedFiles.filter(function (f) { return f.id !== fileId; });
            updateFileInput();
            renderPreview();
        }
        window.removeFile = removeFile;

        mediaInput.addEventListener('change', function (e) {
            mediaError.style.display = 'none';
            var oversized = [];
            Array.from(e.target.files).forEach(function (file) {
                if (!file.type.startsWith('image/')) return;
                if (file.size > MAX_FILE_SIZE) {
                    oversized.push('"' + file.name + '" (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)');
                    return;
                }
                const isDuplicate = selectedFiles.some(function (f) {
                    return f.file.name === file.name && f.file.size === file.size && f.file.lastModified === file.lastModified;
                });
                if (!isDuplicate) selectedFiles.push({ id: 'file_' + (fileIdCounter++), file: file });
            });
            if (oversized.length) {
                mediaError.textContent = 'File berikut melebihi 1 MB dan tidak ditambahkan: ' + oversized.join(', ');
                mediaError.style.display = 'block';
            }
            updateFileInput();
            renderPreview();
        });

        // ── Video links – YouTube validation ────────────────────────────
        const videoList = document.getElementById('video-list');
        const addBtn    = document.getElementById('add-video');

        function isValidYouTube(url) {
            var u = url.trim().toLowerCase();
            return u === '' || u.includes('youtube.com') || u.includes('youtu.be');
        }

        function addField(value) {
            value = value || '';
            const group = document.createElement('div');
            group.className = 'd-flex gap-2 mb-2 video-item';
            const wrapper = document.createElement('div');
            wrapper.className = 'flex-grow-1';
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control video-link-input';
            input.name = 'video_links[]';
            input.placeholder = 'https://youtube.com/...';
            input.value = value;
            const errMsg = document.createElement('div');
            errMsg.className = 'text-danger small mt-1 video-link-error';
            errMsg.style.display = 'none';
            errMsg.textContent = 'Link harus mengandung youtube.com atau youtu.be.';
            input.addEventListener('input', function () {
                if (input.value.trim() !== '' && !isValidYouTube(input.value)) {
                    errMsg.style.display = 'block';
                    input.classList.add('is-invalid');
                } else {
                    errMsg.style.display = 'none';
                    input.classList.remove('is-invalid');
                }
            });
            wrapper.appendChild(input);
            wrapper.appendChild(errMsg);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-danger';
            btn.title = 'Hapus';
            btn.innerHTML = '<i class="bi bi-trash"></i>';
            btn.addEventListener('click', function () { group.remove(); });
            group.appendChild(wrapper);
            group.appendChild(btn);
            videoList.appendChild(group);
        }

        addBtn.addEventListener('click', function () { addField(''); });
        addField('');

        // ── Form submit validation ───────────────────────────────────────
        document.getElementById('albumForm').addEventListener('submit', function (e) {
            var valid = true;

            // Thumbnail
            if (thumbnailInput.files[0] && thumbnailInput.files[0].size > MAX_FILE_SIZE) {
                valid = false;
            }

            // YouTube links
            document.querySelectorAll('.video-link-input').forEach(function (inp) {
                if (inp.value.trim() !== '' && !isValidYouTube(inp.value)) {
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Validasi Form',
                    text: 'Harap perbaiki kesalahan pada form sebelum menyimpan.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>
