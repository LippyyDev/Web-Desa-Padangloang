<?= $this->extend('Guest/layout') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/guest/auth.css') ?>">

<div class="auth-page-wrapper">
    <canvas id="auth-canvas"></canvas>

    <div class="auth-card">
        <div class="auth-card-body">
            <div class="text-center mb-4">
                <h1 class="auth-title">Daftar Akun</h1>
                <p class="auth-subtitle">Buat akun warga untuk mengajukan surat dan memantau status.</p>
            </div>

            <form method="post" action="<?= base_url('/register') ?>">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="auth-form-label">Username</label>
                    <input type="text" class="auth-form-control" name="username" value="<?= old('username') ?>" placeholder="username" required>
                </div>
                
                <div class="mb-4">
                    <label class="auth-form-label">Email</label>
                    <input type="email" class="auth-form-control" name="email" value="<?= old('email') ?>" placeholder="contoh@email.com" required>
                </div>
                
                <div class="mb-4">
                    <label class="auth-form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="auth-form-control" name="password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button class="btn-auth-primary mb-4" type="submit">Daftar Sekarang</button>
            </form>
            
            <div class="auth-divider">
                <span>atau daftar dengan</span>
            </div>
            
            <button type="button" class="btn-auth-google" id="btnGoogleSignIn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
            </button>
            
            <div class="auth-footer-text">
                Sudah punya akun? <a href="<?= base_url('/login') ?>" class="auth-link">Login disini</a>
            </div>

            <div class="text-center mt-3">
                <a href="<?= base_url('/') ?>" class="text-muted text-decoration-none small" style="font-size: 0.8rem;">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

<script type="module" src="<?= base_url('assets/js/firebase-auth.js') ?>"></script>
<script>
// Wait for module to load and attach event listener
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnGoogleSignIn');
    if (btn) {
        // Wait a bit for the module to load
        setTimeout(function() {
            if (window.signInWithGoogle) {
                btn.addEventListener('click', window.signInWithGoogle);
            } else {
                // Fallback: try again after a delay
                setTimeout(function() {
                    if (window.signInWithGoogle) {
                        btn.addEventListener('click', window.signInWithGoogle);
                    }
                }, 500);
            }
        }, 100);
    }
});
</script>
<script src="<?= base_url('assets/js/guest/auth-background.js') ?>"></script>
<script src="<?= base_url('assets/js/guest/password-toggle.js') ?>"></script>
<?= $this->endSection() ?>


