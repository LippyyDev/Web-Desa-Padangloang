<?= $this->extend('Guest/layout') ?>

<?= $this->section('content') ?>
<div class="container py-5" style="max-width: 520px;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3 text-center">Reset Password</h4>
            <p class="text-muted small text-center mb-3">Masukkan email, kode OTP, dan password baru.</p>
            <?php if (!empty($previewOtp)): ?>
                <div class="alert alert-info text-center">OTP: <strong><?= esc($previewOtp) ?></strong></div>
            <?php endif; ?>
            <form method="post" action="<?= base_url('/reset-password') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= $pendingEmail ?? old('email') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kode OTP</label>
                    <input type="text" class="form-control" name="otp" maxlength="6" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Password Baru</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" name="password_confirm" required>
                    </div>
                </div>
                <button class="btn btn-primary w-100 mt-4" type="submit">Reset Password</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


