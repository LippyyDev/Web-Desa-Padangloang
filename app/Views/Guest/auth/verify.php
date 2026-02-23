<?= $this->extend('Guest/layout') ?>

<?= $this->section('content') ?>
<div class="container py-5" style="max-width: 520px;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3 text-center">Verifikasi Akun</h4>
            <p class="text-muted small text-center mb-3">Masukkan email dan kode OTP yang dikirim. Untuk pengujian, OTP juga ditampilkan di bawah.</p>
            <?php if (!empty($previewOtp)): ?>
                <div class="alert alert-info text-center">OTP: <strong><?= esc($previewOtp) ?></strong></div>
            <?php endif; ?>
            <form method="post" action="<?= base_url('/verify') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= $pendingEmail ?? old('email') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kode OTP</label>
                    <input type="text" class="form-control" name="otp" maxlength="6" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Verifikasi</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


