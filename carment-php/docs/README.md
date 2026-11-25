# CARMENT (PHP native + PostgreSQL)

## Pemasangan
1. PHP butuh ekstensi `pdo_pgsql` dan `gd` (`php -m | grep -E "pgsql|gd"`).
2. DBeaver: koneksi ke PostgreSQL, buat database `carment`.
3. Jalankan file di folder `sql/` berurutan (DBeaver: buka file, tekan **Alt+X**):

| File | Fungsi |
|---|---|
| `01_schema.sql` | Struktur seluruh tabel (menghapus tabel lama lalu membuatnya ulang) |
| `02_data_awal.sql` | Kategori, akun awal, jam toko, 5 unit awal (aman diulang) |
| `03_data_demo.sql` | Opsional: reservasi, inspeksi, maintenance contoh (aman diulang) |
| `migrasi.sql` | Hanya untuk database lama yang sudah berisi data: menyamakan strukturnya tanpa menghapus data (aman diulang) |

4. Sesuaikan user/sandi database di `includes/koneksi.php`, lalu dari folder proyek jalankan `php -S localhost:8000` dan buka http://localhost:8000
5. Folder `gambar/asli`, `gambar/thumb`, dan `uploads` harus bisa ditulis PHP.

Akun awal (sandi `password`, segera ganti): `customer@carment.id`, `staff@carment.id`, `owner@carment.id`. Data demo menambah `faiq@carment.id` dan `widi@carment.id`.

## Pengaturan
- **Toko** (`includes/helpers.php`): `TOKO_NAMA`, `TOKO_DESKRIPSI`, `TOKO_ALAMAT`, `TOKO_LAT/LNG`, `REK_BANK/NO/AN`, `DENDA_PERSEN`, `BATAS_BAYAR_MENIT`, `BATAS_PERBAIKAN_JAM`. Jam buka diedit staff lewat menu **Jadwal toko**.
- **Email & WhatsApp**: salin `includes/config.local.php.contoh` menjadi `includes/config.local.php`, isi SMTP (mis. Gmail + sandi aplikasi), set `MODE_DEMO_EMAIL` ke `false`, dan isi `WA_STAFF` (format `62812...`, tanpa `+`). Selama `MODE_DEMO_EMAIL` bernilai `true`, kode verifikasi pemulihan tampil di layar (tanpa email).
- Opsional: `gambar/banner.jpg` untuk gambar latar banner beranda.
- Bukti pembayaran hanya dibuka lewat `reservasi/bukti.php` (cek hak akses); di Apache `uploads/.htaccess` memblokir akses langsung.

## Peran & halaman utama
Pelanggan: `index.php` (katalog). Staff: `reservasi/list.php`. Owner: `laporan/index.php`. Halaman peran lain otomatis dialihkan ke halaman utama akun yang sedang login.

## Menemukan bagian revisi di kode
Cari teks `REVISI` (VS Code: Ctrl+Shift+F). `[FILE BARU]` = seluruh file baru, `[BARU]` = blok baru di file lama, `[UBAH]` = bagian lama yang dimodifikasi; `R2 #n` ... `R7 #n` = putaran dan nomor revisi. Blok diawali `>>> REVISI` dan diakhiri `<<< AKHIR REVISI`; jika hanya ada penanda awal, yang berubah adalah baris tepat di bawahnya.
