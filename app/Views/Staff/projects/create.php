<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/staff/projects') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Tambah Project</h4>
            <div class="text-muted small mt-1">Isi detail project pembangunan.</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/projects') ?>">
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
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="Perencanaan">Perencanaan</option>
                        <option value="Proses">Proses</option>
                        <option value="Ditunda">Ditunda</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Anggaran</label>
                    <input type="number" class="form-control" name="anggaran">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Thumbnail</label>
                    <input type="file" class="form-control" id="thumbnailInput" name="thumbnail" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <div id="thumbnailPreview" class="mt-2"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Upload Foto</label>
                    <input type="file" class="form-control" id="mediaInput" name="media[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp">
                    <div id="mediaPreview" class="row g-2 mt-2"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Video</label>
                    <div id="video-list"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-video">Tambah Link</button>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="4" required></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Simpan Project
                </button>
            </div>
        </form>
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
                thumbnailPreview.innerHTML = '';
            }
        });

        // Preview Media Foto (DataTransfer + tombol hapus)
        const mediaInput = document.getElementById('mediaInput');
        const mediaPreview = document.getElementById('mediaPreview');
        let selectedFiles = [];
        let fileIdCounter = 0;
        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(f => dataTransfer.items.add(f.file));
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
                    if (!isDuplicate) selectedFiles.push({ id: 'file_' + (fileIdCounter++), file });
                }
            });
            updateFileInput();
            renderPreview();
        });

        // Video links
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


