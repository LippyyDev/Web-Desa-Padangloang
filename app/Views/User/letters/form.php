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
                <input type="text" class="form-control" name="judul_perihal" value="<?= set_value('judul_perihal', $letter['judul_perihal'] ?? '') ?>" required>
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
                <textarea class="form-control" name="isi_surat" rows="6" required><?= set_value('isi_surat', $letter['isi_surat'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Lampiran (opsional, bisa lebih dari satu)</label>
                <input type="file" class="form-control" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp">
                <div class="form-text">Dapat mengunggah lebih dari satu file. Maksimal ukuran per file 2MB. Format dokumen atau gambar (PDF, DOC/DOCX, XLS/XLSX, JPG/JPEG, PNG, WEBP).</div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-<?= isset($letter) ? 'check-circle' : 'send' ?>"></i> <?= isset($letter) ? 'Update Surat' : 'Kirim Surat' ?>
                </button>
            </div>
        </form>
    </div>
</div>

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


