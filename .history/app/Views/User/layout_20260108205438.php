<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'User Portal - Desa Padang Loang' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/topbar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/style.css') ?>">
</head>
<body class="bg-light">
<div class="app-wrapper">
    <?= $this->include('Components/Sidebar') ?>
    <div class="main-content">
        <?= $this->include('Components/TopBar') ?>
        <main class="content-area">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger mb-4"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success mb-4"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('info')): ?>
                <div class="alert alert-info mb-4"><?= session()->getFlashdata('info') ?></div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/user/main.js') ?>"></script>
<script>
// Process email queue in background (non-blocking)
(function() {
    if (document.readyState === 'complete') {
        processEmailQueue();
    } else {
        window.addEventListener('load', processEmailQueue);
    }
    
    function processEmailQueue() {
        fetch('<?= base_url('/api/email-queue/process') ?>', {
            method: 'POST',
            keepalive: true,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).catch(function(err) {
            console.debug('Email queue processing failed (non-critical):', err);
        });
    }
})();
</script>
</body>
</html>


