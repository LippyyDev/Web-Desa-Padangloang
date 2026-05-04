// Firebase Configuration — diinject oleh PHP view dari .env
// Lihat: app/Views/Guest/auth/login.php & register.php
if (!window.firebaseConfig) {
  console.error('[Firebase] window.firebaseConfig tidak ditemukan. Pastikan PHP view sudah menginjeknya.');
}
const firebaseConfig = window.firebaseConfig || {};

// Initialize Firebase
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-app.js";
import {
  getAuth,
  signInWithPopup,
  GoogleAuthProvider,
  signOut,
} from "https://www.gstatic.com/firebasejs/12.7.0/firebase-auth.js";

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();

// Force account selection every time
googleProvider.setCustomParameters({
  prompt: "select_account",
});

// Google Sign-In Function
async function signInWithGoogle(event) {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }

  let button = document.getElementById("btnGoogleSignIn");
  if (!button && event) {
    button = event.target.closest("button") || event.target;
  }

  const originalText = button ? button.innerHTML : "";

  if (button) {
    button.disabled = true;
    button.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
  }

  try {
    // Sign out first to ensure fresh account selection
    await signOut(auth);

    // Sign in with popup
    const result = await signInWithPopup(auth, googleProvider);
    const user = result.user;

    // Get ID token
    const idToken = await user.getIdToken(true);

    // Baca CSRF token dari cookie (selalu fresh, tidak terpengaruh regenerate)
    function getCsrfCookie() {
      const name = 'csrf_cookie_name=';
      const cookies = document.cookie.split(';');
      for (let c of cookies) {
        c = c.trim();
        if (c.startsWith(name)) return decodeURIComponent(c.substring(name.length));
      }
      return '';
    }
    const csrfToken = getCsrfCookie();

    // Send token to server
    const response = await fetch("/auth/firebase", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": csrfToken,
      },
      body: new URLSearchParams({ idToken: idToken }),
    });

    const data = await response.json();

    // Sign out from Firebase (we use CodeIgniter session)
    await signOut(auth);

    if (data.success) {
      window.location.href = data.redirect || "/dashboard";
    } else {
      alert(data.message || "Autentikasi gagal.");
      if (button) {
        button.disabled = false;
        button.innerHTML = originalText;
      }
    }
  } catch (error) {
    try {
      await signOut(auth);
    } catch (e) {}

    let errorMessage = "Terjadi kesalahan saat autentikasi.";

    if (
      error.code === "auth/popup-closed-by-user" ||
      error.code === "auth/cancelled-popup-request"
    ) {
      errorMessage = "Login dibatalkan.";
    } else if (error.code === "auth/popup-blocked") {
      errorMessage = "Popup diblokir browser. Izinkan popup untuk situs ini.";
    } else if (error.code === "auth/unauthorized-domain") {
      errorMessage = "Domain tidak diizinkan di Firebase.";
    }

    alert(errorMessage);

    if (button) {
      button.disabled = false;
      button.innerHTML = originalText;
    }
  }
}

window.signInWithGoogle = signInWithGoogle;
