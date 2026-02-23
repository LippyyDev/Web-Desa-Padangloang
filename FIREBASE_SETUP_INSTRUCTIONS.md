# Panduan Konfigurasi Firebase untuk Google Sign-In

Dokumen ini berisi langkah-langkah lengkap untuk mengkonfigurasi Firebase Authentication dengan Google Sign-In pada website Firebase Console.

## Prasyarat

1. Akun Google (untuk mengakses Firebase Console)
2. Proyek Firebase sudah dibuat (webpadangloang)

## Langkah 1: Aktifkan Google Authentication di Firebase Console

### 1.1 Buka Firebase Console

1. Buka browser dan kunjungi: https://console.firebase.google.com/
2. Login dengan akun Google Anda
3. Pilih proyek **webpadangloang** (atau buat proyek baru jika belum ada)

### 1.2 Aktifkan Authentication

1. Di menu sidebar kiri, klik **"Authentication"** (atau "Autentikasi")
2. Jika ini pertama kali, klik tombol **"Get Started"** (Mulai)
3. Klik pada tab **"Sign-in method"** (Metode masuk) atau **"Sign-in providers"**

### 1.3 Aktifkan Google Provider

1. Di daftar provider yang tersedia, cari **"Google"** dan klik pada baris tersebut
2. Klik tombol **"Enable"** (Aktifkan) untuk mengaktifkan Google Sign-In
3. Anda akan melihat form konfigurasi:

   **Project support email** (Email dukungan proyek):
   - Pilih atau masukkan email yang akan digunakan sebagai email dukungan proyek
   - Email ini akan digunakan untuk notifikasi Firebase

4. Klik **"Save"** (Simpan) untuk menyimpan konfigurasi

## Langkah 2: Konfigurasi OAuth Consent Screen (Jika Diperlukan)

Jika menggunakan Google Sign-In untuk pertama kali, Anda mungkin perlu mengkonfigurasi OAuth consent screen:

1. Firebase akan mengarahkan Anda ke Google Cloud Console
2. Atau buka: https://console.cloud.google.com/apis/credentials/consent
3. Pilih proyek **webpadangloang**
4. Pilih **"External"** untuk pengguna eksternal (atau "Internal" jika hanya untuk pengguna dalam organisasi)
5. Isi informasi yang diperlukan:
   - **App name**: Web Padang Loang (atau nama aplikasi Anda)
   - **User support email**: Email dukungan
   - **Developer contact information**: Email developer
6. Klik **"Save and Continue"**
7. Di halaman **Scopes**, klik **"Save and Continue"** (default sudah cukup)
8. Di halaman **Test users** (jika External), tambahkan email test jika diperlukan, lalu klik **"Save and Continue"**
9. Di halaman **Summary**, klik **"Back to Dashboard"**

## Langkah 3: Konfigurasi Authorized Domains

Firebase perlu mengetahui domain mana yang diizinkan untuk melakukan autentikasi:

1. Di Firebase Console, kembali ke **Authentication** > **Settings** (Pengaturan)
2. Scroll ke bawah ke bagian **"Authorized domains"** (Domain yang diizinkan)
3. Pastikan domain berikut sudah terdaftar:
   - `localhost` (untuk development)
   - `webpadangloang.firebaseapp.com` (otomatis)
   - `webpadangloang.web.app` (otomatis)
   - Domain production Anda (misalnya: `www.padangloang.id` atau `padangloang.id`)

4. Jika domain production belum ada:
   - Klik **"Add domain"** (Tambah domain)
   - Masukkan domain Anda (contoh: `padangloang.id`)
   - Klik **"Add"**

## Langkah 4: Verifikasi Firebase Configuration

Pastikan konfigurasi Firebase sudah benar:

1. Di Firebase Console, klik ikon **Settings** (⚙️) di sidebar kiri
2. Pilih **"Project settings"**
3. Scroll ke bawah ke bagian **"Your apps"**
4. Pilih aplikasi web Anda atau klik **"Add app"** > **"Web"** (</> icon)
5. Jika membuat baru, beri nama aplikasi (contoh: "Web Padang Loang")
6. Salin atau verifikasi Firebase configuration object:

```javascript
const firebaseConfig = {
    apiKey: "AIzaSyACfXY4LQq04am5-ONx1S_U4bXitcFYyFo",
    authDomain: "webpadangloang.firebaseapp.com",
    projectId: "webpadangloang",
    storageBucket: "webpadangloang.firebasestorage.app",
    messagingSenderId: "166226496374",
    appId: "1:166226496374:web:f641ec2a961a583a8b8857",
    measurementId: "G-VFS3730759"
};
```

**Catatan**: Konfigurasi ini sudah ada di file `public/assets/js/firebase-auth.js` dan sudah benar.

## Langkah 5: Testing Google Sign-In

### 5.1 Testing di Localhost

1. Pastikan aplikasi CodeIgniter berjalan di localhost
2. Buka halaman login atau register
3. Klik tombol **"Masuk dengan Google"** atau **"Daftar dengan Google"**
4. Popup Google Sign-In akan muncul
5. Pilih akun Google atau login dengan akun Google
6. Berikan izin untuk aplikasi
7. Setelah berhasil, Anda akan diarahkan ke dashboard

### 5.2 Testing di Production

1. Pastikan domain production sudah ditambahkan ke Authorized domains (Langkah 3)
2. Deploy aplikasi ke server production
3. Pastikan HTTPS aktif (Firebase memerlukan HTTPS untuk production)
4. Test Google Sign-In dari domain production

## Troubleshooting

### Error: "Popup blocked"

**Solusi**: 
- Izinkan popup untuk domain website Anda di browser settings
- Coba gunakan mode incognito/private browsing
- Gunakan browser lain untuk test

### Error: "auth/unauthorized-domain"

**Solusi**:
- Pastikan domain sudah ditambahkan ke Authorized domains di Firebase Console
- Pastikan menggunakan format domain yang benar (tanpa http:// atau https://)

### Error: "auth/popup-closed-by-user"

**Solusi**:
- User menutup popup sebelum menyelesaikan autentikasi
- Tidak ada masalah, user bisa mencoba lagi

### Error: Token verification failed

**Solusi**:
- Pastikan server time sync dengan benar
- Pastikan Firebase config sudah benar
- Check browser console untuk error detail

### Google Sign-In tidak muncul

**Solusi**:
- Pastikan file `firebase-auth.js` sudah ter-load (check Network tab di browser DevTools)
- Pastikan Google provider sudah diaktifkan di Firebase Console
- Check console browser untuk error JavaScript

## Catatan Penting

1. **Keamanan**: 
   - API Key Firebase aman untuk diekspos di client-side (frontend)
   - Namun, pastikan Firebase Security Rules sudah dikonfigurasi dengan benar

2. **Production**:
   - Pastikan menggunakan HTTPS di production
   - Update Authorized domains dengan domain production Anda

3. **User Management**:
   - User yang mendaftar melalui Google akan otomatis terverifikasi
   - User dapat login baik dengan Google atau dengan email/password (jika sudah membuat akun manual)
   - Jika email Google sama dengan email akun manual, akun akan otomatis di-link

4. **Database**:
   - Pastikan migration sudah dijalankan untuk menambahkan kolom `firebase_uid` ke tabel `users`
   - Jalankan: `php spark migrate`

## Langkah Selanjutnya

Setelah konfigurasi selesai:

1. Jalankan migration database:
   ```bash
   php spark migrate
   ```

2. Test Google Sign-In di halaman login dan register

3. Monitor penggunaan di Firebase Console > Authentication > Users

4. (Opsional) Setup Firebase Admin SDK di server untuk verifikasi token yang lebih aman (untuk production)

## Dukungan

Jika mengalami masalah, check:
- Firebase Console logs
- Browser console untuk error JavaScript
- Server logs untuk error backend
- Dokumentasi Firebase: https://firebase.google.com/docs/auth/web/google-signin

