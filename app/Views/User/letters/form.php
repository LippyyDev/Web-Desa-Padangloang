<?= $this->extend('User/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/user/surat') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0"><?= isset($letter) ? 'Edit Surat' : 'Buat Surat Baru' ?></h4>
            <div class="text-muted small mt-1">Lengkapi detail surat yang akan dikirim ke staf desa.</div>
        </div>
    </div>
</div>

<?php if (!isset($letter)): // Hanya tampilkan section export untuk mode create ?>
<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon" style="background: #e0e7ff; color: #4f46e5;">
            <i class="bi bi-file-text"></i>
        </div>
        <div>
            <h4 class="mb-0">Hasilkan Berkas Surat</h4>
            <div class="text-muted small mt-1">Dapatkan dokumen Word atau PDF yang sudah sesuai dengan tipe surat dan isi surat yang Anda buat.</div>
        </div>
    </div>
    <div class="page-header-actions">
        <button type="button" class="page-header-icon page-header-icon-add" id="btnExportWord" title="Hasilkan Word">
            <i class="bi bi-file-earmark-word"></i>
        </button>
        <button type="button" class="page-header-icon page-header-icon-delete" id="btnExportPDF" title="Hasilkan PDF">
            <i class="bi bi-file-earmark-pdf"></i>
        </button>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= isset($letter) ? base_url('/user/surat/' . $letter['id']) : base_url('/user/surat') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Judul / Perihal</label>
                <input type="text" class="form-control" name="judul_perihal" value="<?= set_value('judul_perihal', $letter['judul_perihal'] ?? '') ?>" required maxlength="150" placeholder="Maksimal 150 karakter">
            </div>
            <div class="mb-3">
                <label class="form-label">Jenis / Tipe Surat</label>
                <select class="form-select" name="tipe_surat" required>
                    <option value="">-- Pilih Jenis Surat --</option>
                    <option value="Keterangan Usaha" <?= set_select('tipe_surat', 'Keterangan Usaha', (isset($letter['tipe_surat']) && $letter['tipe_surat'] === 'Keterangan Usaha')) ?>>Keterangan Usaha</option>
                    <option value="Keterangan Tidak Mampu" <?= set_select('tipe_surat', 'Keterangan Tidak Mampu', (isset($letter['tipe_surat']) && $letter['tipe_surat'] === 'Keterangan Tidak Mampu')) ?>>Keterangan Tidak Mampu</option>
                    <option value="Keterangan Belum Menikah" <?= set_select('tipe_surat', 'Keterangan Belum Menikah', (isset($letter['tipe_surat']) && $letter['tipe_surat'] === 'Keterangan Belum Menikah')) ?>>Keterangan Belum Menikah</option>
                    <option value="Keterangan Domisili" <?= set_select('tipe_surat', 'Keterangan Domisili', (isset($letter['tipe_surat']) && $letter['tipe_surat'] === 'Keterangan Domisili')) ?>>Keterangan Domisili</option>
                    <option value="Undangan" <?= set_select('tipe_surat', 'Undangan', (isset($letter['tipe_surat']) && $letter['tipe_surat'] === 'Undangan')) ?>>Undangan</option>
                    <option value="Lain Lain" <?= set_select('tipe_surat', 'Lain Lain', (isset($letter['tipe_surat']) && $letter['tipe_surat'] === 'Lain Lain')) ?>>Lain Lain</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Isi Surat</label>
                <textarea class="form-control" name="isi_surat" rows="6" required maxlength="3000" placeholder="Ketikkan isi surat Anda secara jelas... (Maksimal 3000 karakter)"><?= set_value('isi_surat', $letter['isi_surat'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Lampiran</label>
                <input type="file" id="fileInput" class="form-control" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                <div class="form-text">Dapat mengunggah maksimal 5 file. Maksimal ukuran per file 1MB.</div>
                
                <!-- Container untuk preview list file yang dipilih -->
                <ol class="ps-3 mt-3 mb-0 d-none" id="fileListPreview"></ol>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-<?= isset($letter) ? 'check-circle' : 'send' ?>"></i> <?= isset($letter) ? 'Update Surat' : 'Kirim Surat' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Menyimpan original state
let currentDataTransfer = new DataTransfer();

// Escape HTML untuk mencegah XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Fungsi untuk me-render list file
function renderFileList() {
    const fileListPreview = document.getElementById('fileListPreview');
    const fileInput = document.getElementById('fileInput');
    
    fileListPreview.innerHTML = '';
    
    if (currentDataTransfer.files.length === 0) {
        fileListPreview.classList.add('d-none');
        return;
    }
    
    fileListPreview.classList.remove('d-none');
    
    Array.from(currentDataTransfer.files).forEach((file, index) => {
        const ext = file.name.split('.').pop().toLowerCase();
        let iconClass = 'bi-file-earmark';
        
        if (ext === 'pdf') iconClass = 'bi-file-earmark-pdf-fill text-danger';
        else if (['doc', 'docx'].includes(ext)) iconClass = 'bi-file-earmark-word-fill text-primary';
        else if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) iconClass = 'bi-file-earmark-image-fill text-info';
        
        const sizeMb = (file.size / 1024 / 1024).toFixed(2);
        
        const li = document.createElement('li');
        li.className = 'mb-1';
        li.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <span class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 flex-grow-1 text-truncate">
                    <i class="bi ${iconClass} flex-shrink-0"></i>
                    <span class="attachment-filename text-truncate">${escapeHtml(file.name)}</span>
                    <span class="small text-muted flex-shrink-0 ms-1">(${sizeMb} MB)</span>
                </span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="removeFile(${index})" title="Hapus Lampiran">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        fileListPreview.appendChild(li);
    });
}

// Fungsi global untuk menghapus file dari DataTransfer dan update Input
window.removeFile = function(index) {
    const dt = new DataTransfer();
    const files = Array.from(currentDataTransfer.files);
    
    // Hapus file pada index terkait
    files.splice(index, 1);
    
    // Masukkan kembali sisa file ke dt baru
    files.forEach(file => dt.items.add(file));
    
    // Update data transfer utama dan input file
    currentDataTransfer = dt;
    document.getElementById('fileInput').files = currentDataTransfer.files;
    
    renderFileList();
};

// Event listener ganti file
document.getElementById('fileInput')?.addEventListener('change', function(e) {
    const newFiles = Array.from(e.target.files);
    let tempDt = new DataTransfer();
    
    // Salin file yang sudah ada di list
    Array.from(currentDataTransfer.files).forEach(file => {
        tempDt.items.add(file);
    });
    
    // Cek batas total file keseluruhan
    if (tempDt.items.length + newFiles.length > 5) {
        Swal.fire({
            icon: 'error',
            title: 'Terlalu Banyak File',
            text: 'Total lampiran maksimal hanya 5 file. Anda mencoba menyertakan ' + (tempDt.items.length + newFiles.length) + ' file.',
            confirmButtonColor: '#0d6efd'
        });
        // Kembalikan input ke state lama
        e.target.value = '';
        e.target.files = currentDataTransfer.files;
        return;
    }
    
    // Validsi dan tambahkan file baru
    for (let i = 0; i < newFiles.length; i++) {
        if (newFiles[i].size > 1048576) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran File Terlalu Besar',
                text: 'Ukuran file "' + newFiles[i].name + '" (' + (newFiles[i].size / 1024 / 1024).toFixed(2) + 'MB) terlalu besar. Maksimal 1MB per file.',
                confirmButtonColor: '#0d6efd'
            });
            e.target.value = '';
            e.target.files = currentDataTransfer.files;
            return;
        }
        
        // Mencegah duplicate file yang sama persis
        const isDuplicate = Array.from(tempDt.files).some(existingFile => 
            existingFile.name === newFiles[i].name && existingFile.size === newFiles[i].size
        );
        
        if (!isDuplicate) {
            tempDt.items.add(newFiles[i]);
        }
    }
    
    // Jika lolos validasi, komit state baru
    currentDataTransfer = tempDt;
    e.target.value = ''; // Reset value supaya elemen input merespon event change jika memilih file yang persis sama nantinya
    e.target.files = currentDataTransfer.files;
    
    renderFileList();
});
</script>

<?php if (!isset($letter)): // JavaScript hanya untuk mode create ?>
<script>
(function() {
    // Bersihkan localStorage saat halaman dimuat/refresh
    localStorage.removeItem('draft_surat');
    
    // Simpan data ke localStorage saat user mengetik
    const form = document.querySelector('form');
    const judulInput = document.querySelector('input[name="judul_perihal"]');
    const tipeSelect = document.querySelector('select[name="tipe_surat"]');
    const isiTextarea = document.querySelector('textarea[name="isi_surat"]');
    
    // Simpan ke localStorage saat ada perubahan
    function saveToLocalStorage() {
        const data = {
            judul_perihal: judulInput.value,
            tipe_surat: tipeSelect.value,
            isi_surat: isiTextarea.value
        };
        localStorage.setItem('draft_surat', JSON.stringify(data));
    }
    
    judulInput.addEventListener('input', saveToLocalStorage);
    tipeSelect.addEventListener('change', saveToLocalStorage);
    isiTextarea.addEventListener('input', saveToLocalStorage);
    
    // Hapus draft saat form disubmit
    form.addEventListener('submit', function() {
        localStorage.removeItem('draft_surat');
    });
    
    // Ambil CSRF token dari form
    function getCsrfToken() {
        const csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
        return csrfInput ? csrfInput.value : '';
    }
    
    // Handle export Word
    document.getElementById('btnExportWord').addEventListener('click', function() {
        const tipeSurat = tipeSelect.value;
        const isiSurat = isiTextarea.value;
        
        if (!tipeSurat) {
            alert('Pilih jenis surat terlebih dahulu!');
            return;
        }
        
        if (!isiSurat.trim()) {
            alert('Isi surat tidak boleh kosong!');
            return;
        }
        
        // Buat form sementara untuk POST
        const formData = new FormData();
        formData.append('tipe_surat', tipeSurat);
        formData.append('isi_surat', isiSurat);
        formData.append('<?= csrf_token() ?>', getCsrfToken());
        
        // Submit ke endpoint export Word
        fetch('<?= base_url('/user/surat/preview/word') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Export gagal');
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Surat_' + tipeSurat.replace(/\s+/g, '_') + '.docx';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat export Word',
                confirmButtonColor: '#0d6efd'
            });
        });
    });
    
    // Handle export PDF
    document.getElementById('btnExportPDF').addEventListener('click', function() {
        const tipeSurat = tipeSelect.value;
        const isiSurat = isiTextarea.value;
        
        if (!tipeSurat) {
            alert('Pilih jenis surat terlebih dahulu!');
            return;
        }
        
        if (!isiSurat.trim()) {
            alert('Isi surat tidak boleh kosong!');
            return;
        }
        
        // Buat form sementara untuk POST
        const formData = new FormData();
        formData.append('tipe_surat', tipeSurat);
        formData.append('isi_surat', isiSurat);
        formData.append('<?= csrf_token() ?>', getCsrfToken());
        
        // Submit ke endpoint export PDF
        fetch('<?= base_url('/user/surat/preview/pdf') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Export gagal');
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Surat_' + tipeSurat.replace(/\s+/g, '_') + '.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat export PDF',
                confirmButtonColor: '#0d6efd'
            });
        });
    });
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>


