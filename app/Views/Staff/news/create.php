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
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/berita') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul</label>
                    <input type="text" class="form-control" name="judul" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="datetime-local" class="form-control" name="tanggal_waktu">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Thumbnail</label>
                    <input type="file" class="form-control" id="thumbnailInput" name="thumbnail" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <div id="thumbnailPreview" class="mt-2"></div>
                </div>
                <div class="col-12">
                    <label class="form-label">Isi Berita</label>
                    <div class="quill-wrapper">
                        <div id="editor"></div>
                    </div>
                    <textarea name="isi" style="display: none;"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto (opsional, multiple)</label>
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
                    <i class="bi bi-check-circle"></i> Simpan Berita
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Quill Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .quill-wrapper {
        margin-bottom: 1rem;
    }
    .quill-wrapper #editor {
        min-height: 300px;
        max-height: 500px;
        overflow-y: auto;
    }
    .quill-wrapper .ql-container {
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
        border: 1px solid #ced4da;
    }
    .quill-wrapper .ql-toolbar {
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
        border: 1px solid #ced4da;
        border-bottom: none;
    }
    .quill-wrapper .ql-container.ql-snow {
        border: 1px solid #ced4da;
    }
    .quill-wrapper .ql-toolbar.ql-snow {
        border: 1px solid #ced4da;
        border-bottom: none;
    }
</style>
<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Quill Editor
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

        // Sync Quill content to textarea before form submit
        const form = document.querySelector('form');
        const textarea = document.querySelector('textarea[name="isi"]');
        
        form.addEventListener('submit', function(e) {
            // Get Quill content and clean it
            let content = quill.root.innerHTML;
            
            // Check if content is empty (only whitespace or empty tags)
            const textContent = quill.getText().trim();
            if (!textContent) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Isi Berita tidak boleh kosong!',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
            
            // Set content to textarea
            textarea.value = content;
        });

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
                thumbnailPreview.innerHTML = '';
            }
        });

        // Preview Media Foto (dengan DataTransfer + tombol hapus)
        const mediaInput = document.getElementById('mediaInput');
        const mediaPreview = document.getElementById('mediaPreview');
        let selectedFiles = [];
        let fileIdCounter = 0;

        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(fileObj => dataTransfer.items.add(fileObj.file));
            mediaInput.files = dataTransfer.files;
        }

        function renderPreview() {
            mediaPreview.innerHTML = '';
            selectedFiles.forEach((fileObj) => {
                if (fileObj.file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-3';
                        col.setAttribute('data-file-id', fileObj.id);
                        col.innerHTML = `
                            <div class="border rounded p-2 text-center position-relative">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="width: 28px; height: 28px; padding: 0; line-height: 1; border-radius: 50%;" onclick="removeFile('${fileObj.id}')" title="Hapus">
                                    <span style="font-size: 18px;">×</span>
                                </button>
                                <img src="${e.target.result}" class="img-fluid rounded" style="max-width: 100%; max-height: 150px; object-fit: cover;" alt="preview">
                            </div>
                        `;
                        mediaPreview.appendChild(col);
                    };
                    reader.readAsDataURL(fileObj.file);
                }
            });
        }

        function removeFile(fileId) {
            selectedFiles = selectedFiles.filter(f => f.id !== fileId);
            updateFileInput();
            renderPreview();
        }
        window.removeFile = removeFile;

        mediaInput.addEventListener('change', function(e) {
            Array.from(e.target.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const isDuplicate = selectedFiles.some(f =>
                        f.file.name === file.name && f.file.size === file.size && f.file.lastModified === file.lastModified
                    );
                    if (!isDuplicate) {
                        selectedFiles.push({ id: 'file_' + (fileIdCounter++), file });
                    }
                }
            });
            updateFileInput();
            renderPreview();
        });

        // Video links functionality
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


