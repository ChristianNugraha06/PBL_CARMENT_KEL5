# CARMENT (PHP native + PostgreSQL, DBeaver)
1. Pastikan PHP punya ekstensi `pdo_pgsql` (`php -m | grep pgsql`).
2. DBeaver: koneksi ke PostgreSQL, klik kanan Databases > Create New Database > `carment`.
3. Buka SQL Editor di database carment, jalankan berurutan dengan Alt+X: `sql/01_schema.sql`, `02_users.sql`, `03_demo_data.sql`.
4. Sesuaikan user/sandi di `includes/koneksi.php`, lalu dari folder proyek: `php -S localhost:8000`, buka http://localhost:8000
Akun demo (sandi `password`): customer@carment.id, staff@carment.id, owner@carment.id, faiq@carment.id, widi@carment.id
Versi MySQL lama ada di `sql/_mysql_lama/` (tidak dipakai).

# Struktur folder
carment-php/
├── index.php                  Katalog (cari + filter kategori), halaman utama
│
├── assets/
│   ├── css/style.css          Seluruh gaya tampilan
│   └── js/app.js              Hitung biaya sewa langsung + konfirmasi hapus
│
├── auth/
│   ├── login.php              Form masuk
│   ├── proses_login.php       Verifikasi sandi, buat sesi, arahkan sesuai peran
│   ├── register.php           Form daftar pelanggan
│   ├── proses_register.php    Simpan akun baru (sandi di-hash)
│   └── logout.php             Hapus sesi
│
├── barang/                    Katalog dan kelola unit
│   ├── detail.php             Detail unit, kalender ketersediaan, form reservasi
│   ├── list.php               Tabel kelola barang (staff)
│   ├── tambah.php             Form tambah unit
│   ├── proses_tambah.php
│   ├── edit.php               Form edit unit
│   ├── proses_edit.php
│   ├── hapus.php
│   └── _form.php              Form bersama untuk tambah dan edit
│
├── reservasi/
│   ├── proses_tambah.php      Validasi tanggal, cek bentrok, simpan bukti transfer
│   ├── riwayat.php            Riwayat sewa pelanggan
│   ├── list.php               Reservasi aktif dan serah-terima (staff)
│   └── proses_status.php      Konfirmasi, tolak, serahkan barang
│
├── inspeksi/
│   ├── form.php               Checklist inspeksi pengembalian
│   └── proses.php             Hitung denda, set unit Available atau Maintenance
│
├── maintenance/
│   ├── list.php               Daftar tiket maintenance
│   └── proses_selesai.php     Tandai perbaikan selesai
│
├── laporan/
│   └── index.php              Laporan Owner: pendapatan, denda, grafik, transaksi
│
├── includes/                  File bersama yang di-require halaman lain
│   ├── koneksi.php            Koneksi PostgreSQL (PDO), sesi, BASE_URL
│   ├── auth.php               Cek login dan peran (wajib_login)
│   ├── csrf.php               Token CSRF
│   ├── helpers.php            e(), rp(), redirect(), flash(), badge(), konstanta denda
│   ├── header.php             Pembuka HTML + navigasi per peran
│   └── footer.php             Penutup HTML + pemanggil app.js
│
├── sql/
│   ├── 01_schema.sql          Tabel + data unit awal (PostgreSQL)
│   ├── 02_users.sql           Akun staff, owner, customer
│   ├── 03_demo_data.sql       Data demo: reservasi, inspeksi, maintenance
│   └── _mysql_lama/           Versi MySQL sebelumnya (tidak dipakai)
│
├── uploads/
│   ├── demo-bukti.png         Gambar placeholder bukti transfer demo
│   └── .gitkeep               Bukti transfer asli tersimpan di sini
│
└── docs/
    └── README.md              Panduan setup singkat