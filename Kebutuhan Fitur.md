# Rancangan Fitur Sistem  
## Website Desa Padang Loang

---

## 1. Komponen Global (Navbar)
Navigasi utama yang tampil pada seluruh halaman publik.

- **Home** → Halaman Awal (Landing Page)
- **Profil** → Halaman Profil Desa
- **Galeri** → Halaman Galeri Desa
- **Berita** → Halaman Berita Desa
- **Project** → Halaman Project Desa
- **Login** → Halaman Autentikasi Pengguna

---

## 2. Halaman Awal (Landing Page)

### 2.1 Hero Section
- Background gambar desa
- Teks sambutan: *“Selamat Datang di Website Resmi Desa Padang Loang”*

### 2.2 About Section
- Deskripsi singkat mengenai Desa Padang Loang

### 2.3 Navigation Section
- Ajakan eksplorasi website desa
- Tombol navigasi:
  - Profil Desa
  - Galeri Desa
  - Berita Desa
  - Project Desa

### 2.4 Map Section
- Peta lokasi desa menggunakan Google Maps Embedded

### 2.5 Structure Organization Section
- Struktur perangkat desa
- Data yang ditampilkan:
  - Foto
  - Nama
  - Jabatan

### 2.6 Galeri, Berita, dan Project Section
- Menampilkan masing-masing **6 data terbaru**
- Tombol **“Lihat Lebih Banyak”**

---

## 3. Halaman Profil Desa (Publik)
Menampilkan informasi resmi Desa Padang Loang.

- Visi dan Misi Desa
- Administrasi Penduduk:
  - Jumlah Penduduk
  - Jumlah Kepala Keluarga
  - Penduduk Sementara
  - Jumlah Laki-laki
  - Jumlah Perempuan
  - Mutasi Penduduk
- Kontak Desa
- Peta dan Lokasi Desa

---

## 4. Halaman Galeri Desa (Publik)

### 4.1 Tampilan
- Galeri berbentuk **card album**
- Setiap album dapat berisi foto dan/atau video

---

## 5. Halaman Berita Desa (Publik)

### 5.1 Tampilan
- Berita ditampilkan dalam bentuk **card**
- Klik card → halaman detail berita

---

## 6. Halaman Project Desa (Publik)

### 6.1 Tampilan
- Project ditampilkan dalam bentuk **card**
- Klik card → halaman detail project

---

## 7. Sistem Autentikasi

### 7.1 Halaman Login
**Input:**
- Email / Username
- Password

**Fungsi:**
- Autentikasi pengguna
- Redirect berdasarkan role:
  - User → Dashboard User
  - Staf → Dashboard Staf
  - Admin → Dashboard Admin

---

### 7.2 Halaman Register
**Input:**
- Username
- Email
- Password
- Konfirmasi Password

---

### 7.3 Halaman Konfirmasi Register
**Input:**
- Kode OTP atau Link Verifikasi Email

**Output:**
- Aktivasi akun
- Redirect ke Dashboard User

---

### 7.4 Halaman Lupa Password
**Input:**
- Email

**Output:**
- Pengiriman OTP ke email pengguna

---

### 7.5 Halaman Reset Password
**Input:**
- Password Baru
- Konfirmasi Password

**Output:**
- Redirect ke halaman Login

---

## 8. Fitur Role User

### 8.1 Dashboard User
- Ringkasan aktivitas surat
- Notifikasi status surat

---

### 8.2 Edit Profil User (CRUD)
**Input Data:**
- Foto Profil
- Username
- Nama Lengkap
- Tempat Lahir
- Tanggal Lahir
- Agama
- Pekerjaan
- NIK
- Alamat

---

### 8.3 Kelola Surat User (CRUD)

#### 8.3.1 Fungsi
- Membuat, melihat, mengubah, dan menghapus surat
- Mengirim surat ke staf desa

#### 8.3.2 Input Data Surat
- Judul / Perihal Surat
- Jenis / Tipe Surat
- Isi Surat
- File Lampiran (opsional)

#### 8.3.3 Fitur Tambahan
- Template surat otomatis dengan kop resmi:
  - Pemerintah Kabupaten Bulukumba
  - Kecamatan Ujung Loe
  - Desa Padang Loang
- Export surat ke format:
  - PDF
  - Word

#### 8.3.4 Status Surat
- Terkirim
- Dibaca
- Dibalas

---

### 8.4 Halaman Notifikasi User
**Informasi yang Ditampilkan:**
- Status surat
- Balasan dari staf

---

## 9. Fitur Role Staf

### 9.1 Dashboard Staf
- Ringkasan surat masuk
- Notifikasi terbaru

---

### 9.2 Edit Profil Staf (CRUD)
**Input Data:**
- Foto Profil
- Username
- Nama Lengkap
- Tempat Lahir
- Tanggal Lahir
- Agama
- Pekerjaan
- NIK
- Alamat

---

### 9.3 Kelola Surat Masuk (CRUD)

#### 9.3.1 Fungsi
- Melihat surat masuk dari user
- Memberikan balasan surat

#### 9.3.2 Input Balasan Surat
- Isi Balasan (teks)
- File Lampiran Balasan (opsional)

---

### 9.4 Kelola Profil Desa (CRUD)

#### 9.4.1 Fungsi
- Mengelola informasi resmi desa
- Memperbarui konten halaman Profil Desa

#### 9.4.2 Input Data Profil Desa
- Visi Desa
- Misi Desa
- Administrasi Penduduk:
  - Jumlah Penduduk
  - Jumlah Kepala Keluarga
  - Penduduk Sementara
  - Jumlah Laki-laki
  - Jumlah Perempuan
  - Mutasi Penduduk
- Kontak Desa:
  - Nomor Telepon / WhatsApp
  - Email Desa
  - Alamat Kantor Desa
- Informasi Lokasi:
  - Link Google Maps
  - Deskripsi lokasi (opsional)

---

### 9.5 Kelola Galeri Desa (CRUD)
**Input Data Album:**
- Nama Album
- Deskripsi Album
- Tanggal dan Waktu
- Thumbnail Album
- Foto Album (lebih dari satu)
- Link Video (YouTube / Google Drive)

---

### 9.6 Kelola Berita Desa (CRUD)
**Input Data Berita:**
- Judul Berita
- Tanggal dan Waktu
- Thumbnail Berita
- Foto Berita (lebih dari satu)
- Isi Berita

---

### 9.7 Kelola Project Desa (CRUD)
**Input Data Project:**
- Judul Project
- Tanggal dan Waktu
- Thumbnail Project
- Foto Project (lebih dari satu)
- Deskripsi Project
- Jumlah Anggaran
- Status Project:
  - Perencanaan
  - Proses
  - Ditunda
  - Selesai

---

### 9.8 Halaman Notifikasi Staf
**Informasi yang Ditampilkan:**
- Notifikasi surat masuk dari user

---

## 10. Fitur Role Admin

### 10.1 Dashboard Admin
- Ringkasan data sistem

---

### 10.2 Edit Profil Admin (CRUD)
**Input Data:**
- Foto Profil
- Username
- Nama Lengkap
- Tempat Lahir
- Tanggal Lahir
- Agama
- Pekerjaan
- NIK
- Alamat

---

### 10.3 Kelola Akun (CRUD)

**Jenis Akun:**
- Admin
- Staf
- User

**Input Data Akun:**
- Foto Profil
- Username
- Email
- Password
- Role Akun
- Status Akun (Aktif / Nonaktif)

---
