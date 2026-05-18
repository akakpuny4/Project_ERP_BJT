<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


<div align="center">

# 🚀 ERP CV BJT — Laravel + Filament

### Panduan Instalasi & Menjalankan Project dari GitHub

<img src="https://img.shields.io/badge/Laravel-12-red?style=for-the-badge&logo=laravel">
<img src="https://img.shields.io/badge/Filament-Admin%20Panel-orange?style=for-the-badge">
<img src="https://img.shields.io/badge/PHP-8+-blue?style=for-the-badge&logo=php">
<img src="https://img.shields.io/badge/MySQL-Database-blue?style=for-the-badge&logo=mysql">

</div>

---

# 📌 Tentang Project

ERP CV BJT adalah sistem ERP berbasis **Laravel + Filament** yang digunakan untuk membantu pengelolaan:

- 📦 Manajemen Barang
- 💰 Keuangan & Kas
- 🧾 Penjualan & Pembelian
- 🏦 Rekening & Mutasi Saldo
- 📄 Cetak PDF Kuitansi
- 👤 Panel Admin Modern

Project ini sudah menggunakan:

- ✅ Laravel Framework
- ✅ Filament Admin Panel
- ✅ Database MySQL
- ✅ Seeder Data Awal
- ✅ Sistem Storage Laravel

---

# ⚙️ Persiapan (Prasyarat)

Pastikan software berikut sudah ter-install di komputer Anda:

| Software | Fungsi |
|---|---|
| XAMPP / Laragon | Menjalankan Apache, PHP, dan MySQL |
| Composer | Install dependency Laravel |
| Git Bash | Clone repository & terminal command |
| VS Code | Code editor |

---

# 📥 Langkah 1 — Clone Repository

Buka terminal/Git Bash lalu jalankan:

```bash
git clone https://github.com/akakpuny4/Project_ERP_BJT.git
```

Masuk ke folder project:

```bash
cd Project_ERP_BJT
```

---

# 📦 Langkah 2 — Install Dependencies

Install seluruh dependency Laravel dan Filament:

```bash
composer install
```

> ⏳ Tunggu hingga proses selesai dan pastikan internet aktif.

---

# 🔐 Langkah 3 — Setup File Environment (.env)

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Lalu buka file `.env` dan ubah konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_bjt
DB_USERNAME=root
DB_PASSWORD=
```

### 📌 Keterangan

| Config | Penjelasan |
|---|---|
| DB_DATABASE | Nama database yang akan dibuat |
| DB_USERNAME | Username MySQL |
| DB_PASSWORD | Password MySQL |

---

# 🗄️ Langkah 4 — Buat Database

1. Jalankan **Apache** dan **MySQL**
2. Buka browser:

```text
http://localhost/phpmyadmin
```

3. Buat database baru:

```text
erp_bjt
```

> ⚠️ Nama database harus sama dengan isi `DB_DATABASE` pada file `.env`

---

# 🔑 Langkah 5 — Generate Application Key

Laravel membutuhkan application key untuk sistem keamanan.

Jalankan:

```bash
php artisan key:generate
```

---

# 🏗️ Langkah 6 — Migrasi & Seeder Database

Perintah ini akan:

- Membuat seluruh tabel database
- Mengisi data awal
- Membuat akun admin
- Menambahkan daftar barang
- Menambahkan rekening default

Jalankan:

```bash
php artisan migrate:fresh --seed
```

---

# 🔗 Langkah 7 — Hubungkan Storage Laravel

Agar upload file & PDF berjalan dengan baik:

```bash
php artisan storage:link
```

---

# 🎨 Langkah 8 — Install / Upgrade Filament Assets

Install asset CSS & JS Filament:

```bash
php artisan filament:upgrade
```

---

# 🚀 Langkah 9 — Jalankan Server

Nyalakan Laravel server:

```bash
php artisan serve
```

Buka browser:

```text
http://127.0.0.1:8000/admin
```

---

# 👤 Login Default

```text
Email    : admin@cvbjt.com
Password : password
```

---

# 📁 Struktur Penting Project

```text
app/
database/
public/
resources/
routes/
storage/
.env
composer.json
artisan
```

---

# 🛠️ Tech Stack

| Technology | Description |
|---|---|
| Laravel | Backend Framework |
| Filament | Admin Panel |
| MySQL | Database |
| PHP | Programming Language |
| TailwindCSS | Styling |
| Livewire | Reactive Component |

---

# 🎉 Selesai!

Project ERP Laravel + Filament Anda sekarang sudah siap digunakan 🚀

Jika ada error saat instalasi, biasanya penyebabnya:

- Composer belum ter-install
- PHP belum masuk PATH
- Database belum dibuat
- Apache/MySQL belum running
- Versi PHP tidak sesuai

---

<div align="center">

## ⭐ Jangan lupa kasih star repository ini ⭐

Made with ❤️ using Laravel & Filament

</div>