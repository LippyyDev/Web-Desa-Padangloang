# Cara Mengaktifkan Google Authentication di Firebase Console

## Langkah-langkah Detail:

### 1. Buka Firebase Console
```
URL: https://console.firebase.google.com/
```

### 2. Login
- Login dengan akun Google yang sama yang digunakan untuk membuat project Firebase

### 3. Pilih Project
- Di halaman utama Firebase Console, Anda akan melihat daftar project
- **Klik project "webpadangloang"**

### 4. Buka Menu Authentication
Di sidebar kiri (menu sebelah kiri), cari dan klik:
```
📱 Authentication
```
atau dalam bahasa Indonesia:
```
📱 Autentikasi
```

### 5. Buka Tab Sign-in Method
Setelah masuk ke halaman Authentication:
- Klik tab **"Sign-in method"** di bagian atas (di sebelah "Users")
- Atau dalam bahasa Indonesia: **"Metode masuk"**

### 6. Aktifkan Google Provider
Di halaman Sign-in method, Anda akan melihat daftar provider authentication:
- Scroll ke bawah atau cari **"Google"** dalam daftar
- Klik baris **"Google"** (bukan toggle, tapi klik seluruh barisnya)

### 7. Enable Google Sign-In
Setelah klik Google, akan muncul dialog/popup dengan pengaturan:
- **Toggle "Enable"** ke posisi ON (biru/hijau)
- Pilih **"Project support email"** - pilih email yang akan digunakan untuk komunikasi (biasanya email project owner)
- Klik tombol **"Save"** (Simpan) di bagian bawah dialog

### 8. Selesai!
Setelah klik Save, Google Authentication sudah aktif. Anda akan melihat:
- Status Google berubah menjadi "Enabled" (Diaktifkan)
- Ada tombol "Edit" dan "Delete" di sebelah kanan

## Lokasi di Firebase Console:

```
Firebase Console
├── Dashboard (Home)
├── Authentication  ← KLIK DI SINI
│   ├── Users (tab)
│   └── Sign-in method (tab) ← KLIK TAB INI
│       └── Google ← KLIK INI UNTUK AKTIFKAN
├── Firestore Database
├── Storage
└── ... (menu lainnya)
```

## Visual Guide:

### Setelah Klik Authentication:
```
┌─────────────────────────────────────────┐
│  Authentication                         │
├─────────────────────────────────────────┤
│  [Users] [Sign-in method] ← KLIK INI    │
├─────────────────────────────────────────┤
│                                         │
│  Provider          Status               │
│  ─────────────────────────────────────  │
│  Email/Password    Enabled              │
│  Google            Disabled  ← KLIK INI │
│  Facebook          Disabled             │
│  Twitter           Disabled             │
│  ...                                    │
└─────────────────────────────────────────┘
```

### Dialog yang Muncul Setelah Klik Google:
```
┌─────────────────────────────────────┐
│  Google                             │
├─────────────────────────────────────┤
│                                     │
│  Enable             [Toggle ON]     │
│                                     │
│  Project support email:             │
│  [Select email ▼]                   │
│                                     │
│  [Cancel]  [Save] ← KLIK INI       │
│                                     │
└─────────────────────────────────────┘
```

## Catatan Penting:

1. **Pastikan sudah login dengan akun Google yang benar**
2. **Pastikan memilih project yang benar** (webpadangloang)
3. **Toggle Enable harus ON** (berwarna biru/hijau, bukan abu-abu)
4. **Pilih Project support email** sebelum klik Save

## Troubleshooting:

### Jika tidak melihat "Authentication" di sidebar:
- Pastikan sudah memilih project yang benar
- Pastikan project sudah dibuat dengan benar
- Refresh halaman (F5)

### Jika "Google" tidak muncul di daftar provider:
- Scroll ke bawah, biasanya ada di bawah Email/Password
- Pastikan sudah di tab "Sign-in method"

### Jika tombol Save tidak bisa diklik:
- Pastikan sudah toggle Enable ke ON
- Pastikan sudah memilih Project support email

## Setelah Diaktifkan:

Setelah Google Authentication diaktifkan:
1. Status Google akan berubah menjadi "Enabled"
2. Anda bisa test login dengan Google di aplikasi
3. User bisa login/register menggunakan akun Google mereka

## Test:

Setelah aktifkan, test dengan:
1. Buka aplikasi: `http://localhost:8080/login`
2. Klik tombol "Masuk dengan Google"
3. Pilih akun Google
4. Jika berhasil, akan diarahkan ke dashboard

## Screenshot Lokasi:

Jika masih bingung, lokasi tepatnya adalah:
- **Menu kiri**: Authentication
- **Tab atas**: Sign-in method  
- **Daftar provider**: Klik "Google"
- **Dialog**: Toggle Enable, pilih email, klik Save

Selamat! Google Authentication sudah aktif! 🎉

