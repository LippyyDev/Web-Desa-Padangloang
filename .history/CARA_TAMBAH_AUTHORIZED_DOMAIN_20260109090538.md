# Cara Menambahkan Authorized Domain di Firebase

## Langkah-langkah Menambahkan Domain ke Authorized Domains

### 1. Buka Firebase Console
- Buka [Firebase Console](https://console.firebase.google.com/)
- Pilih project **webpadangloang**

### 2. Buka Authentication Settings
- Di sidebar kiri, klik **"Authentication"** atau **"Autentikasi"**
- Klik tab **"Settings"** atau **"Pengaturan"** (ikon gear di bagian atas)
- Scroll ke bawah ke bagian **"Authorized domains"** atau **"Domain yang Diizinkan"**

### 3. Tambahkan Domain
- Klik tombol **"Add domain"** atau **"Tambah domain"**
- Masukkan domain sesuai kebutuhan:

#### Untuk Development (Localhost):
```
localhost
```
atau
```
localhost:8080
```

**Catatan:** 
- Biasanya `localhost` sudah otomatis ditambahkan
- Jika tidak ada, tambahkan `localhost` (tanpa port)
- Firebase biasanya mengizinkan localhost dengan port apapun secara default

#### Untuk Production:
```
yourdomain.com
```
atau
```
www.yourdomain.com
```

### 4. Simpan
- Klik **"Add"** atau **"Tambah"**
- Domain akan muncul di daftar Authorized domains

## Format Domain yang Benar

✅ **Format yang BENAR:**
- `localhost` (tanpa port - akan bekerja untuk semua port)
- `yourdomain.com`
- `www.yourdomain.com`
- `subdomain.yourdomain.com`

❌ **Format yang SALAH:**
- `http://localhost:8080` (jangan pakai protocol)
- `https://yourdomain.com` (jangan pakai protocol)
- `localhost:8080` (sebaiknya pakai `localhost` saja)

## Catatan Penting

1. **Localhost biasanya sudah otomatis:**
   - Firebase biasanya sudah menambahkan `localhost` secara default
   - Jika sudah ada `localhost` di daftar, tidak perlu menambahkan lagi

2. **Port tidak perlu disebutkan:**
   - `localhost` akan bekerja untuk `localhost:8080`, `localhost:3000`, dll
   - Tidak perlu menambahkan setiap port secara terpisah

3. **Untuk Production:**
   - Pastikan domain production sudah ditambahkan sebelum deploy
   - Domain harus menggunakan HTTPS (kecuali localhost)

## Troubleshooting

### Jika masih error setelah menambahkan domain:

1. **Cek apakah domain sudah benar:**
   - Pastikan tidak ada typo
   - Pastikan tidak ada `http://` atau `https://`

2. **Clear cache browser:**
   - Kadang browser cache bisa menyebabkan masalah
   - Coba hard refresh (Ctrl+Shift+R atau Cmd+Shift+R)

3. **Cek console browser:**
   - Buka Developer Tools (F12)
   - Lihat apakah ada error terkait domain authorization

4. **Pastikan Google Authentication aktif:**
   - Authentication > Sign-in method
   - Pastikan Google sudah diaktifkan (Enable)

## Screenshot Lokasi Authorized Domains

Lokasi Authorized Domains ada di:
```
Firebase Console > Authentication > Settings > Authorized domains
```

Di bagian bawah halaman Settings, Anda akan melihat:
- Daftar domain yang sudah diizinkan
- Tombol "Add domain" untuk menambahkan domain baru

## Verifikasi

Setelah menambahkan domain, coba:
1. Refresh halaman login
2. Klik tombol "Masuk dengan Google"
3. Jika berhasil, popup Google akan muncul
4. Setelah login, akan diarahkan ke dashboard

Jika masih error, pastikan:
- Domain sudah ditambahkan dengan benar
- Google Authentication sudah diaktifkan
- Konfigurasi Firebase di `app/Config/Firebase.php` sudah benar

