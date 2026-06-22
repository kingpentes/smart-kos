# SMART KOST

SMART KOST adalah platform web pencarian dan pengelolaan kos yang menghubungkan calon penyewa, penyewa aktif, pemilik kos, dan admin. Fokus produk adalah pencarian kos yang lebih relevan, data lokasi yang jelas, booking, pembayaran, pengingat tagihan, serta dashboard pengelolaan kos.

Tagline produk: "Cari Kost Cerdas, Rekomendasi AI untukmu".

## Status Proyek

Repositori ini masih berada pada tahap awal implementasi Laravel. Dokumen produk dan konsep sudah tersedia, sementara kode aplikasi utama masih menggunakan struktur dasar Laravel.

Dokumen utama:

- [PRD_smartkost.md](PRD_smartkost.md) - kebutuhan produk, aktor, halaman, integrasi, dan prioritas MVP.
- [konsep.md](konsep.md) - rencana implementasi teknis berbasis Laravel dan Tailwind CSS.
- [AGENTS.md](AGENTS.md) - aturan teknis pengembangan untuk Laravel Boost, PHP, Pint, dan PHPUnit.

## Stack Aktual

- PHP 8.3
- Laravel 13
- Laravel Boost 2
- PHPUnit 12
- Laravel Pint 1
- Vite 8
- Tailwind CSS 4
- MySQL untuk database lokal dan target produksi awal

## Fitur Produk

Prioritas produk SMART KOST meliputi:

- Listing kos dan pencarian.
- Rekomendasi kos berbasis input bahasa natural.
- Detail kos dengan foto, fasilitas, aturan, harga, dan lokasi.
- Booking kos oleh calon penyewa.
- Dashboard penyewa untuk status sewa dan tagihan.
- Dashboard pemilik untuk kamar, booking, keluhan, dan pendapatan.
- Pembayaran terintegrasi melalui payment gateway.
- Notifikasi jatuh tempo.
- Rating dan ulasan kesesuaian foto.
- Admin untuk verifikasi listing dan pengelolaan pengguna.

## Setup Lokal

Prasyarat:

- PHP 8.3+
- Composer
- Node.js dan npm
- MySQL 8+ atau MariaDB yang kompatibel

Instal dependensi:

```bash
composer install
npm install
```

Siapkan environment:

```bash
cp .env.example .env
php artisan key:generate
```

Siapkan database MySQL lokal:

```sql
CREATE DATABASE smart_kost CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Pastikan kredensial database di `.env` sesuai mesin lokal:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_kost
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration:

```bash
php artisan migrate
```

Jalankan aplikasi:

```bash
composer run dev
```

Atau jalankan proses secara terpisah:

```bash
php artisan serve
npm run dev
```

## Build Frontend

Untuk membuat asset produksi:

```bash
npm run build
```

Jika perubahan frontend tidak tampil di browser, pastikan Vite dev server berjalan melalui `npm run dev` atau jalankan build ulang.

## Testing dan Formatting

Jalankan test:

```bash
php artisan test --compact
```

Format kode PHP:

```bash
vendor/bin/pint --dirty --format agent
```

## Standar Implementasi

Implementasi harus modular dan mengikuti best practice Laravel:

- Auth memakai implementasi manual berbasis Laravel session, middleware, Form Request, dan controller internal. Jangan menambah starter kit auth tanpa persetujuan.
- Pisahkan tanggung jawab per modul: listing, search, booking, billing, payment, notification, complaint, review, dashboard, AI, dan map.
- Gunakan Form Request untuk validasi, Policy/Gate untuk otorisasi, dan Service/Action untuk business logic.
- Controller harus tetap tipis dan tidak berisi logic domain yang kompleks.
- Integrasi eksternal harus dibungkus service agar mudah diganti dan dites.
- Alur penting seperti menerima booking, membuat sewa, mengubah kamar, dan membuat invoice harus memakai database transaction.
- Setiap modul penting harus memiliki PHPUnit feature test.

## Arah Implementasi

Implementasi sebaiknya dimulai dari fondasi data dan alur utama:

1. Autentikasi dan role user.
2. Listing kos, kamar, foto, fasilitas, dan lokasi.
3. Pencarian dan detail kos.
4. Booking dan status sewa.
5. Billing dasar dan riwayat pembayaran.
6. Dashboard pemilik dan penyewa.
7. AI recommendation, peta POI, payment gateway, notifikasi, dan rating.

Detail teknis per modul ada di [konsep.md](konsep.md).
