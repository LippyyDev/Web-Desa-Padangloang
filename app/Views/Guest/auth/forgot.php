<?= $this->extend('Guest/layout') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/guest/auth.css') ?>">

<div class="auth-page-wrapper">
    <canvas id="auth-canvas"></canvas>

    <div class="auth-card">
        <div class="auth-card-body">
            <div class="text-center mb-4">
                <h1 class="auth-title">Lupa Password</h1>
                <p class="auth-subtitle">Masukkan email Anda untuk menerima kode OTP reset.</p>
            </div>

            <form method="post" action="<?= base_url('/forgot-password') ?>">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="auth-form-label">Email</label>
                    <input type="email" class="auth-form-control" name="email" placeholder="contoh@email.com" required>
                </div>
                <button class="btn-auth-primary mb-4" type="submit">Kirim OTP</button>
            </form>

            <div class="text-center mt-3">
                <a href="<?= base_url('/login') ?>" class="text-muted text-decoration-none small" style="font-size: 0.8rem;">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/guest/auth-background.js') ?>"></script>
<?= $this->endSection() ?>


