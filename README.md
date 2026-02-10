# Warnet Bahagia - Sistem Manajemen Internet Cafe

Aplikasi web untuk manajemen warnet yang lengkap dengan fitur billing, booking PC, pemesanan F&B, top-up saldo, dan laporan keuangan.

## 🚀 Fitur Utama

### Admin

- ✅ Dashboard Real-time dengan Grafik Pendapatan (Chart.js)
- ✅ Manajemen Unit PC (Status: Tersedia, Digunakan, Rusak)
- ✅ Kelola Paket Billing & Harga Per Jam
- ✅ Manajemen Menu F&B dengan Stok Otomatis
- ✅ Monitoring Pesanan Masuk (Update Status: Pending -> Selesai)
- ✅ Manajemen Data Konsumen & Top Up Saldo Manual
- ✅ Laporan Keuangan Harian & Export ke CSV
- ✅ Manajemen Tiket Bantuan Teknis

### Konsumen

- ✅ Dashboard Personal (Info Saldo & Status Main)
- ✅ Booking PC Online dengan Pilihan Durasi
- ✅ Kantin Digital (F&B) dengan Sistem Keranjang Belanja
- ✅ Top-Up Saldo (Simulasi QRIS/Tunai)
- ✅ Riwayat Aktivitas (Log Billing & Riwayat Jajan)
- ✅ Kirim Tiket Bantuan ke Admin

## 📋 Requirements

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Browser modern (Chrome, Firefox, Edge, Safari)

## 🛠️ Instalasi

### 1. Setup Database

```sql
-- Buat database baru
CREATE DATABASE manajemen_warnet;

-- Import struktur database
USE manajemen_warnet;
SOURCE manajemen_warnet.sql;

-- Import sample data (opsional)
SOURCE install_sample_data.sql;
```

### 2. Konfigurasi

Edit file `includes/config.php` sesuai dengan konfigurasi server Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'manajemen_warnet');
define('BASE_URL', 'http://localhost/warnet-bahagia/');
```

### 3. Deploy

- Copy seluruh folder ke direktori web server Anda
- Untuk XAMPP: `C:\xampp\htdocs\warnet-bahagia\`
- Untuk Linux: `/var/www/html/warnet-bahagia/`

### 4. Akses Aplikasi

Buka browser dan akses:

```
http://localhost/warnet-bahagia/
```

## 👤 Default Login

### Admin

- Username: `admin`
- Password: `password`

### Konsumen (Sample User)

- Username: `user1`
- Password: `password`

**PENTING:** Segera ganti password default setelah login pertama!

## 📂 Struktur Folder

```
warnet-bahagia/
├── includes/
│   ├── config.php          # Konfigurasi database
│   └── functions.php       # Helper functions
├── pages/
│   ├── admin/             # Halaman admin
│   │   ├── dashboard.php
│   │   ├── pc.php
│   │   ├── billing.php
│   │   ├── fnb.php
│   │   ├── pesanan.php
│   │   ├── konsumen.php
│   │   ├── laporan.php
│   │   └── bantuan.php
│   └── konsumen/          # Halaman konsumen
│       ├── dashboard.php
│       ├── booking.php
│       ├── fnb.php
│       ├── topup.php
│       ├── pesanan.php
│       └── bantuan.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── index.php              # Halaman login
├── register.php           # Halaman registrasi
└── logout.php            # Logout handler
```

## 🎨 Teknologi yang Digunakan

- **Backend**: PHP dengan MySQLi
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Tailwind CSS
- **Libraries**: Chart.js (Grafik), SweetAlert2 (Notifikasi)
- **Icons**: Font Awesome 6
- **Database**: MySQL/MariaDB

## 💡 Cara Penggunaan

### Untuk Admin

1. Login menggunakan akun admin
2. Di dashboard, Anda bisa melihat statistik warnet
3. Kelola PC melalui menu "Kelola PC"
4. Tambah/edit item F&B di menu "F&B"
5. Monitor pesanan di menu "Pesanan"
6. Lihat laporan keuangan di menu "Laporan"

### Untuk Konsumen

1. Registrasi akun baru atau login
2. Top-up saldo melalui menu "Top Up"
3. Booking PC yang tersedia
4. Pesan makanan/minuman
5. Lihat riwayat transaksi di dashboard

## 🔐 Keamanan

- Password di-hash menggunakan PHP `password_hash()`
- Sanitasi input untuk mencegah SQL Injection
- Session management untuk autentikasi
- Role-based access control (Admin vs Konsumen)

## 📊 Database Schema

### Tabel Utama:

- `admin` - Data administrator
- `konsumen` - Data konsumen
- `pc` - Data komputer
- `billing` - Data billing/waktu sewa
- `item_fnb` - Item makanan & minuman
- `keranjang` - Keranjang belanja
- `pesanan` - Data pesanan
- `detail_pesanan` - Rincian item per pesanan
- `transaksi_pc` - Log riwayat penggunaan PC
- `bantuan_teknis` - Request bantuan

### Trigger:

- `kurangi_stok` - Otomatis mengurangi stok saat item dipesan
- `kurangi_saldo` - Otomatis mengurangi saldo saat pembayaran
- `update_status_pc` - Update status PC saat pesanan selesai

## 🐛 Troubleshooting

### Database Connection Error

- Pastikan MySQL service berjalan
- Cek kredensial database di `config.php`
- Pastikan database sudah dibuat

### Permission Error

- Pastikan folder memiliki permission yang tepat
- Linux: `chmod 755 warnet-bahagia/`

### Session Error

- Pastikan PHP session enabled
- Cek permission folder temp PHP

## 📝 Lisensi

Proyek ini dibuat untuk keperluan akademik oleh Kelompok 4:

- Nopan Rizki Ramdani (10123199)
- Sendi Fauzan (10123180)
- Muhammad Ziddan Aryan (10123185)
- Arswandi Raditya R. Sunusi (10123198)
- Viezal Nabil Dzaikra (10123213)

Universitas Komputer Indonesia - Jurusan Teknik Informatika

## 🤝 Kontribusi

Untuk bug report atau feature request, silakan hubungi tim pengembang.
