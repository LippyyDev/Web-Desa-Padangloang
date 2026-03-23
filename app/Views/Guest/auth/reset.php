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
                        <button type="button" class="btn btn-link p-0 text-decoration-none small" id="btnResendOtp" style="font-size: 0.85rem; color: #3b82f6;">
                            Kirim Ulang OTP
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="auth-form-label">Password Baru</label>
                    <div class="password-wrapper">
                        <input type="password" class="auth-form-control" id="newPassword" name="password" placeholder="••••••••" required oninput="checkPasswordStrength(this.value)">
                        <button type="button" class="password-toggle">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <!-- Password strength indicator -->
                    <div id="pwStrengthBar" style="height:4px;border-radius:2px;margin-top:6px;background:#1e293b;overflow:hidden;">
                        <div id="pwStrengthFill" style="height:100%;width:0%;border-radius:2px;transition:width .3s,background .3s;"></div>
                    </div>
                    <small id="pwStrengthText" class="d-block mt-1" style="font-size:0.78rem;color:#64748b;">Minimal 8 karakter, mengandung huruf &amp; angka</small>
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
// --- Password Strength Checker ---
function checkPasswordStrength(val) {
    const fill = document.getElementById('pwStrengthFill');
    const text = document.getElementById('pwStrengthText');
    if (!fill || !text) return;

    const hasLetter = /[A-Za-z]/.test(val);
    const hasNumber = /[0-9]/.test(val);
    const hasSpecial = /[^A-Za-z0-9]/.test(val);
    const len = val.length;

    let score = 0;
    if (len >= 8) score++;
    if (len >= 12) score++;
    if (hasLetter && hasNumber) score++;
    if (hasSpecial) score++;

    const levels = [
        { pct: '0%',   color: '#1e293b', label: 'Minimal 8 karakter, mengandung huruf &amp; angka' },
        { pct: '25%',  color: '#ef4444', label: 'Terlalu lemah' },
        { pct: '50%',  color: '#f97316', label: 'Lemah — tambahkan angka' },
        { pct: '75%',  color: '#eab308', label: 'Sedang' },
        { pct: '100%', color: '#22c55e', label: 'Kuat ✓' },
    ];

    const level = len === 0 ? levels[0] : levels[Math.min(score, 4)];
    fill.style.width  = level.pct;
    fill.style.background = level.color;
    text.innerHTML = level.label;
    text.style.color = len === 0 ? '#64748b' : level.color;
}

// --- OTP Resend Logic ---
document.addEventListener('DOMContentLoaded', function() {
    const btnResend = document.getElementById('btnResendOtp');
    let timer;
    let resendCount = 0; // 0 = first click has no cooldown

    function startCooldown(seconds) {
        btnResend.disabled = true;
        btnResend.style.color = '#64748b';
        let remaining = seconds;
        btnResend.innerHTML = `Kirim ulang dalam <span>${remaining}</span>s`;

        clearInterval(timer);
        timer = setInterval(() => {
            remaining--;
            btnResend.innerHTML = `Kirim ulang dalam <span>${remaining}</span>s`;
            if (remaining <= 0) {
                clearInterval(timer);
                btnResend.disabled = false;
                btnResend.style.color = '#3b82f6';
                btnResend.innerHTML = 'Kirim Ulang OTP';
            }
        }, 1000);
    }

    btnResend.addEventListener('click', function() {
        if (btnResend.disabled) return;

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
                resendCount = data.resend_count ?? (resendCount + 1);
                alert(data.message);
                // From 2nd resend onwards, apply 90s cooldown
                startCooldown(90);
            } else {
                alert(data.message);
                // If server says wait, apply the remaining time
                if (data.wait) {
                    startCooldown(data.wait);
                } else {
                    btnResend.disabled = false;
                    btnResend.style.color = '#3b82f6';
                    btnResend.innerHTML = 'Kirim Ulang OTP';
                }
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan koneksi.');
            btnResend.disabled = false;
            btnResend.style.color = '#3b82f6';
            btnResend.innerHTML = 'Kirim Ulang OTP';
        });
    });
});
</script>
<?= $this->endSection() ?>


