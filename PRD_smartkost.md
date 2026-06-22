# PRD SMART KOST

**Product Requirements Document**  
Tagline: "Cari Kost Cerdas, Rekomendasi AI untukmu"

## 1. Deskripsi Proyek

SMART KOST adalah platform web untuk mempertemukan calon penyewa kos, penyewa aktif, pemilik kos, dan admin platform. Produk ini membantu calon penyewa menemukan kos yang sesuai kebutuhan, membantu pemilik mengelola listing dan pembayaran, serta memberi admin kontrol verifikasi dan pengawasan.

Keunggulan utama:

1. Rekomendasi kos berbasis input bahasa natural.
2. Detail lokasi dengan peta in-app dan informasi sekitar kos.
3. Booking, tagihan, pembayaran, dan pengingat jatuh tempo dalam satu platform.

Platform berbasis website responsif untuk desktop dan mobile browser.

## 2. Aktor

| Aktor | Deskripsi |
|---|---|
| Calon Penyewa | Pengguna yang mencari, memfilter, melihat detail, dan booking kos. |
| Penyewa Aktif | Pengguna yang sudah memiliki sewa aktif dan mengelola tagihan, pembayaran, serta keluhan. |
| Pemilik Kos | Pengguna yang mendaftarkan kos, mengelola kamar, booking, tagihan, dan keluhan. |
| Admin | Pengelola platform yang memverifikasi listing, memantau transaksi, dan mengelola pengguna. |

## 3. Sasaran Produk

- Mengurangi waktu pencarian kos dengan pencarian terstruktur dan rekomendasi.
- Meningkatkan kepercayaan calon penyewa melalui data foto, fasilitas, lokasi, dan ulasan.
- Mengurangi pekerjaan manual pemilik kos dalam pencatatan booking, tagihan, dan pembayaran.
- Menyediakan fondasi data yang siap dikembangkan ke AI recommendation, payment gateway, dan notifikasi.

## 4. Halaman dan Fitur

### 4.1 Halaman Publik

#### `/`

Landing page.

- Hero dengan nama SMART KOST, tagline, dan tombol cari kos.
- Pencarian cepat berdasarkan lokasi dan rentang harga.
- Ringkasan fitur unggulan: AI Finder, GPS Map, Smart Billing.
- Listing kos populer atau terbaru.
- Footer navigasi.

#### `/cari`

Halaman pencarian kos.

- Input prompt bebas, contoh: "kos dekat Telkom University, budget 800rb, ada wifi, tenang".
- Filter manual: lokasi, harga, tipe kos, fasilitas, dan jarak.
- Hasil berupa card listing:
  - Foto utama.
  - Nama kos.
  - Alamat ringkas.
  - Harga per bulan.
  - Tag fasilitas.
  - Skor rekomendasi jika query AI digunakan.
  - Tombol lihat detail.
- Toggle list view dan map view dapat masuk fase setelah data lokasi stabil.

#### `/kos/{kos}`

Halaman detail kos.

- Galeri foto kos.
- Nama, alamat, deskripsi, aturan, fasilitas, dan harga.
- Informasi kamar tersedia.
- Skor rekomendasi dan breakdown jika berasal dari pencarian AI.
- Peta lokasi kos.
- POI sekitar seperti minimarket, kampus, klinik, ATM, dan tempat makan.
- Rating dan ulasan.
- Tombol booking.

#### `/login` dan `/register`

Autentikasi.

- Login email dan password.
- Register dengan pilihan role penyewa atau pemilik.
- Google OAuth bersifat opsional setelah auth dasar stabil.

### 4.2 Halaman Penyewa

#### `/dashboard/penyewa`

Dashboard penyewa aktif.

- Status sewa aktif: kos, kamar, tanggal mulai, tanggal jatuh tempo.
- Peringatan jika mendekati jatuh tempo.
- Tombol bayar sekarang jika ada tagihan belum lunas.
- Riwayat pembayaran.
- Akses ke form keluhan.

#### `/booking/{kos}`

Halaman booking.

- Ringkasan kos dan kamar yang dipilih.
- Form data penyewa.
- Tanggal mulai sewa.
- Durasi sewa.
- Konfirmasi booking sebelum pembayaran.

#### `/pembayaran/{invoice}`

Halaman pembayaran.

- Ringkasan tagihan.
- Status invoice.
- Metode pembayaran.
- Integrasi payment gateway pada fase lanjutan.

#### `/pembayaran/{invoice}/berhasil`

Konfirmasi pembayaran.

- Status transaksi sukses.
- Detail pembayaran.
- Link kembali ke dashboard.
- Unduh bukti bayar PDF bersifat nice-to-have.

#### `/keluhan`

Form keluhan.

- Pilih sewa aktif.
- Kategori keluhan.
- Deskripsi.
- Upload foto opsional.
- Status tindak lanjut.

### 4.3 Halaman Pemilik Kos

#### `/dashboard/pemilik`

Dashboard pemilik.

- Ringkasan total kos, kamar, kamar terisi, kamar kosong, dan pendapatan bulan berjalan.
- Tabel status kamar.
- Notifikasi booking masuk, keluhan, dan pembayaran.
- Grafik pendapatan bulanan pada fase lanjutan.

#### `/listing/tambah`

Tambah listing kos.

- Form multi-step:
  - Info dasar: nama, deskripsi, tipe kos.
  - Alamat dan koordinat.
  - Fasilitas.
  - Aturan kos.
  - Harga dan deposit.
  - Foto kos.
  - Kamar dan ketersediaan.

#### `/listing/{kos}/edit`

Edit listing.

- Form sama seperti tambah listing dengan data existing.
- Simpan perubahan.
- Nonaktifkan listing.
- Hapus listing memerlukan aturan otorisasi dan validasi tidak ada sewa aktif.

#### `/booking/masuk`

Kelola booking masuk.

- Daftar booking.
- Detail calon penyewa.
- Konfirmasi atau tolak booking.
- Status booking berubah otomatis.

#### `/keluhan/masuk`

Kelola keluhan.

- Daftar keluhan penyewa.
- Detail keluhan dan foto.
- Balasan pemilik.
- Status: baru, diproses, selesai.

#### `/laporan`

Laporan keuangan.

- Filter periode.
- Tabel pemasukan.
- Total pendapatan.
- Export PDF atau Excel sebagai nice-to-have.

### 4.4 Halaman Admin

#### `/admin/dashboard`

Dashboard admin.

- Total pengguna.
- Total listing.
- Total booking.
- Total transaksi.
- Listing menunggu verifikasi.

#### `/admin/listing`

Kelola listing.

- Daftar listing semua status.
- Verifikasi, tolak, nonaktifkan.

#### `/admin/pengguna`

Kelola pengguna.

- Daftar pengguna dengan filter role.
- Suspend akun.
- Aktivasi ulang.

## 5. Integrasi Eksternal

| Fitur | Teknologi yang Disarankan | Catatan |
|---|---|---|
| AI recommendation | OpenAI API atau Gemini API melalui Laravel HTTP Client | Gunakan setelah data listing dan fasilitas cukup stabil. |
| Peta | Leaflet.js + OpenStreetMap | Cocok untuk fase awal karena gratis dan ringan. |
| POI sekitar | Overpass API atau provider places berbayar | Cache hasil agar tidak lambat dan tidak boros request. |
| Payment gateway | Midtrans atau Xendit | Perlu webhook dan validasi signature. |
| Notifikasi | Laravel Notifications, email, WhatsApp provider | Mulai dari email/log, lanjut WhatsApp setelah billing stabil. |
| File upload | Laravel Storage | Mulai lokal/public disk, lanjut S3-compatible storage jika perlu. |

## 6. Stack Proyek

Stack aktual repositori:

| Layer | Teknologi |
|---|---|
| Backend | Laravel 13 |
| Bahasa | PHP 8.3 |
| Frontend | Blade, Vite, Tailwind CSS 4 |
| Database | MySQL |
| Testing | PHPUnit 12 |
| Formatting | Laravel Pint |
| Dev tooling | Laravel Boost, Laravel Pail |

Catatan: PRD ini mengikuti stack Laravel yang sudah ada di repositori. Auth memakai implementasi manual berbasis Laravel session dan middleware. Jika kelak memakai starter kit auth, SPA, atau mobile app, itu diperlakukan sebagai ekspansi terpisah dan perlu persetujuan.

## 7. Desain dan UI

- Font: Inter atau Plus Jakarta Sans.
- Gaya: clean, modern, padat, dan mudah dipindai.
- Responsif dari mobile 375px sampai desktop 1440px.
- Komponen utama:
  - Card kos.
  - Badge skor rekomendasi.
  - Filter pencarian.
  - Form multi-step.
  - Tabel dashboard.
  - Status tagihan dan booking.
  - Peta interaktif.

## 8. Alur Utama

Calon penyewa:

```text
Landing -> Cari Kos -> Detail Kos -> Booking -> Pembayaran -> Dashboard Penyewa
```

Penyewa aktif:

```text
Dashboard -> Lihat Tagihan -> Bayar -> Riwayat Pembayaran -> Keluhan jika diperlukan
```

Pemilik kos:

```text
Register -> Tambah Listing -> Listing Diverifikasi -> Terima Booking -> Kelola Kamar dan Tagihan
```

Admin:

```text
Login -> Review Listing -> Verifikasi/Tolak -> Pantau Pengguna dan Transaksi
```

## 9. Prioritas MVP

### Fase 1 - Fondasi Listing dan Pencarian

Wajib:

- Auth dasar.
- Role penyewa, pemilik, admin.
- CRUD listing kos oleh pemilik.
- Upload foto kos.
- Data fasilitas, aturan, harga, kamar, dan lokasi.
- Halaman landing, cari, dan detail kos.
- Admin verifikasi listing.

Tujuan fase: pengguna bisa melihat kos valid dan pemilik bisa mengelola listing.

### Fase 2 - Booking dan Dashboard

Wajib:

- Booking kos.
- Konfirmasi atau tolak booking oleh pemilik.
- Dashboard penyewa.
- Dashboard pemilik.
- Data sewa aktif.
- Keluhan penyewa.
- Tagihan dasar tanpa payment gateway penuh.

Tujuan fase: alur calon penyewa menjadi penyewa aktif berjalan end-to-end.

### Fase 3 - Pembayaran dan Smart Billing

Wajib:

- Invoice/tagihan.
- Riwayat pembayaran.
- Integrasi payment gateway.
- Webhook pembayaran.
- Pengingat jatuh tempo.

Tujuan fase: pencatatan pembayaran tidak lagi manual.

### Fase 4 - AI, Map, Rating, dan Optimasi

Wajib setelah data utama stabil:

- AI recommendation dari prompt natural language.
- Skor kesesuaian listing.
- Peta interaktif dan POI sekitar.
- Rating dan ulasan kesesuaian foto.
- Laporan keuangan.

Nice-to-have:

- Export PDF/Excel.
- Chat penyewa dan pemilik.
- Boost listing/premium.
- Google OAuth.
- Aplikasi mobile.

## 10. Kriteria Penerimaan MVP Awal

MVP awal dianggap layak jika:

- Pengguna bisa register dan login sesuai role.
- Pemilik bisa membuat listing kos lengkap dengan harga, fasilitas, lokasi, kamar, dan foto.
- Admin bisa memverifikasi listing sebelum tampil publik.
- Calon penyewa bisa mencari dan melihat detail listing terverifikasi.
- Calon penyewa bisa membuat booking.
- Pemilik bisa menerima atau menolak booking.
- Penyewa aktif bisa melihat status sewa.
- Semua alur utama memiliki test feature PHPUnit.
- Implementasi setiap fitur modular, mengikuti konvensi Laravel, dan tidak menaruh business logic kompleks di controller atau Blade.
- Validasi, otorisasi, transaksi database, dan integrasi eksternal mengikuti best practice yang dirinci di `konsep.md`.
