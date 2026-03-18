<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Portal Staf - Desa Padang Loang' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/topbar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/staff/style.css') ?>">
</head>
<body class="bg-light">
<div class="app-wrapper">
    <?= $this->include('Components/Sidebar') ?>
    <div class="main-content">
        <?= $this->include('Components/TopBar') ?>
        <main class="content-area">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/staff/main.js') ?>"></script>
<script>
// SweetAlert2 Flash Messages Handler
<?php if (session()->getFlashdata('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= addslashes(session()->getFlashdata('success')) ?>',
        confirmButtonColor: '#0d6efd',
        timer: 3000,
        timerProgressBar: true
    });
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?= addslashes(session()->getFlashdata('error')) ?>',
        confirmButtonColor: '#0d6efd'
    });
<?php endif; ?>

<?php if (session()->getFlashdata('info')): ?>
    Swal.fire({
        icon: 'info',
        title: 'Informasi',
        text: '<?= addslashes(session()->getFlashdata('info')) ?>',
        confirmButtonColor: '#0d6efd'
    });
<?php endif; ?>

// Global SweetAlert2 Confirmation Function
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
    return Swal.fire({
        title: 'Konfirmasi Hapus',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        return result.isConfirmed;
    });
}

// Global Delete Confirmation Handler (Event Delegation)
document.addEventListener('click', async function(e) {
    // Check if clicked element or its parent is a delete link
    const deleteLink = e.target.closest('a[href*="/hapus"]');
    if (!deleteLink) return;
    
    // Skip if it's already handled by a custom handler
    if (deleteLink.classList.contains('delete-reply-btn') || deleteLink.dataset.skipGlobal) return;
    
    e.preventDefault();
    const url = deleteLink.href;
    
    // Determine message based on URL
    let message = 'Apakah Anda yakin ingin menghapus data ini?';
    if (url.includes('/surat/')) {
        if (url.includes('/balasan/')) {
            message = 'Yakin ingin menghapus balasan ini?';
        } else {
            message = 'Yakin ingin menghapus surat ini? Semua balasan dan lampiran juga akan dihapus.';
        }
    } else if (url.includes('/galeri/')) {
        message = url.includes('/media/') ? 'Hapus media ini?' : 'Hapus album ini?';
    } else if (url.includes('/berita/')) {
        message = url.includes('/media/') ? 'Hapus media ini?' : 'Hapus berita ini?';
    } else if (url.includes('/projects/')) {
        message = url.includes('/media/') ? 'Hapus media ini?' : 'Hapus project ini?';
    } else if (url.includes('/perangkat-desa/')) {
        message = 'Hapus data ini?';
    }
    
    const result = await Swal.fire({
        title: 'Konfirmasi Hapus',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });
    
    if (result.isConfirmed) {
        window.location.href = url;
    }
});

<?= $this->renderSection('scripts') ?>
</script>
<script>
// Process email queue in background (non-blocking)
(function() {
    if (document.readyState === 'complete') {
        processEmailQueue();
    } else {
        window.addEventListener('load', processEmailQueue);
    }
    
    function processEmailQueue() {
        // Delay 5 detik agar tidak bersaing dengan AJAX DataTables/konten utama
        setTimeout(function() {
            fetch('<?= base_url('/api/email-queue/process') ?>', {
                method: 'GET',
                keepalive: true,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(function(err) {
                // Email queue processing failed (non-critical)
            });
        }, 5000);
    }
})();
</script>
</body>
</html>
