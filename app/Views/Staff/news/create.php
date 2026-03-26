<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/staff/berita') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Tambah Berita</h4>
            <div class="text-muted small mt-1">Tulis berita desa beserta media pendukung.</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/berita') ?>" id="beritaForm">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="judul" id="judulInput" maxlength="150" required>
                    <div class="d-flex justify-content-end mt-1">
                        <small class="text-muted"><span id="judulCount">0</span>/150 karakter</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="datetime-local" class="form-control" name="tanggal_waktu">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Thumbnail</label>
                    <input type="file" class="form-control" id="thumbnailInput" name="thumbnail" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <small class="text-muted">Maks. 1 MB (JPEG, JPG, PNG, WEBP)</small>
                    <div class="text-danger small mt-1" id="thumbnailError" style="display:none;"></div>
                    <div id="thumbnailPreview" class="mt-2"></div>
                </div>
                <div class="col-12">
                    <label class="form-label">Isi Berita <span class="text-danger">*</span></label>
                    <div class="quill-wrapper">
                        <div id="editor"></div>
                    </div>
                    <textarea name="isi" style="display: none;"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Foto (opsional, multiple)</label>
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
                    <i class="bi bi-check-circle"></i> Simpan Berita
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Quill Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .quill-wrapper { margin-bottom: 1rem; }
    .quill-wrapper #editor { min-height: 300px; max-height: 500px; overflow-y: auto; }
    .quill-wrapper .ql-container { border-bottom-left-radius: 0.375rem; border-bottom-right-radius: 0.375rem; border: 1px solid #ced4da; }
    .quill-wrapper .ql-toolbar { border-top-left-radius: 0.375rem; border-top-right-radius: 0.375rem; border: 1px solid #ced4da; border-bottom: none; }
    .quill-wrapper .ql-container.ql-snow { border: 1px solid #ced4da; }
    .quill-wrapper .ql-toolbar.ql-snow { border: 1px solid #ced4da; border-bottom: none; }
</style>
<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1 MB

        // ── Quill Editor ─────────────────────────────────────────────────
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean'],
                    ['link', 'image', 'video']
                ]
            }
        });

        const textarea = document.querySelector('textarea[name="isi"]');

        // ── Judul character counter ──────────────────────────────────────
        const judulInput = document.getElementById('judulInput');
        const judulCount = document.getElementById('judulCount');
        judulInput.addEventListener('input', function () {
            judulCount.textContent = judulInput.value.length;
        });

        // ── Thumbnail preview + size validation ─────────────────────────
        const thumbnailInput   = document.getElementById('thumbnailInput');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const thumbnailError   = document.getElementById('thumbnailError');
        thumbnailInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            thumbnailError.style.display = 'none';
            thumbnailPreview.innerHTML   = '';
            if (!file) return;
            if (file.size > MAX_FILE_SIZE) {
                thumbnailError.textContent = 'Ukuran thumbnail terlalu besar (' + (file.size / 1024 / 1024).toFixed(2) + ' MB). Maksimal 1 MB.';
                thumbnailError.style.display = 'block';
                thumbnailInput.value = '';
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
        document.getElementById('beritaForm').addEventListener('submit', function (e) {
            // Sync Quill to textarea
            textarea.value = quill.root.innerHTML;

            var valid = true;

            // Isi Berita empty check
            if (!quill.getText().trim()) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Isi Berita tidak boleh kosong!', confirmButtonColor: '#0d6efd' });
                return false;
            }

            // Thumbnail size
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
                    title: 'Validasi Gagal',
                    text: 'Harap perbaiki kesalahan pada form sebelum menyimpan.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>
