<?= $this->extend('Guest/layout') ?>
 
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/guest/auth.css') ?>">
 
<div class="auth-page-wrapper">
    <canvas id="auth-canvas"></canvas>
 
    <div class="auth-card">
        <div class="auth-card-body">
            <div class="text-center mb-4">
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">Masukkan kode OTP yang dikirim ke email Anda dan buat password baru.</p>
            </div>
            
            <?php
            // Mask email
            $maskedEmail = $pendingEmail;
            if (filter_var($pendingEmail, FILTER_VALIDATE_EMAIL)) {
                $parts = explode('@', $pendingEmail);
                $name = $parts[0];
                $domain = $parts[1];
                $len = strlen($name);
                if ($len > 3) {
                    $maskedName = substr($name, 0, 3) . str_repeat('*', $len - 3);
                } else {
                    $maskedName = substr($name, 0, 1) . str_repeat('*', $len - 1);
                }
                $maskedEmail = $maskedName . '@' . $domain;
            }
            ?>

            <div class="otp-info-box mb-4">
                <i class="bi bi-envelope-check"></i>
                <div class="otp-info-text">
                    Kami telah mengirimkan OTP ke email <strong><?= esc($maskedEmail) ?></strong>.<br>
                    Silakan cek inbox atau spam email Anda.
                </div>
            </div>

            <form method="post" action="<?= base_url('/reset-password') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= esc($pendingEmail) ?>">
                
                <div class="mb-4">
                    <label class="auth-form-label">Kode OTP</label>
                    <input type="text" class="auth-form-control text-center text-uppercase" name="otp" maxlength="6" placeholder="XXXXXX" required style="letter-spacing: 4px; font-size: 1.2rem;">
                    <div class="text-end mt-2">
                        <button type="button" class="btn btn-link p-0 text-decoration-none small" id="btnResendOtp" disabled style="font-size: 0.85rem; color: #64748b;">
                            Kirim ulang dalam <span id="otpCountdown">60</span>s
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="auth-form-label">Password Baru</label>
                    <div class="password-wrapper">
                        <input type="password" class="auth-form-control" name="password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button class="btn-auth-primary mb-4" type="submit">Reset Password</button>
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
<script src="<?= base_url('assets/js/guest/password-toggle.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let countdown = 60;
    const btnResend = document.getElementById('btnResendOtp');
    const countdownSpan = document.getElementById('otpCountdown');
    let timer;

    // Start countdown immediately
    startTimer();

    function startTimer() {
        btnResend.disabled = true;
        btnResend.style.color = '#64748b';
        countdown = 60;
        countdownSpan.textContent = countdown;
        btnResend.innerHTML = `Kirim ulang dalam <span id="otpCountdown">${countdown}</span>s`;
        
        clearInterval(timer);
        timer = setInterval(() => {
            countdown--;
            document.getElementById('otpCountdown').textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                btnResend.disabled = false;
                btnResend.style.color = '#3b82f6'; // Blue color
                btnResend.innerHTML = 'Kirim Ulang OTP';
            }
        }, 1000);
    }

    btnResend.addEventListener('click', function() {
        if(btnResend.disabled) return;
        
        btnResend.disabled = true;
        btnResend.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';

        fetch('<?= base_url('/auth/resend-reset-otp') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                startTimer();
            } else {
                alert(data.message);
                btnResend.disabled = false;
                btnResend.innerHTML = 'Kirim Ulang OTP';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
            btnResend.disabled = false;
            btnResend.innerHTML = 'Kirim Ulang OTP';
        });
    });
});
</script>
<?= $this->endSection() ?>


