# CARMENT (PHP native + PostgreSQL, DBeaver)
1. Pastikan PHP punya ekstensi `pdo_pgsql` (`php -m | grep pgsql`).
2. DBeaver: koneksi ke PostgreSQL, klik kanan Databases > Create New Database > `carment`.
3. Buka SQL Editor di database carment, jalankan berurutan dengan Alt+X: `sql/01_schema.sql`, `02_users.sql`, `03_demo_data.sql`.
4. Sesuaikan user/sandi di `includes/koneksi.php`, lalu dari folder proyek: `php -S localhost:8000`, buka http://localhost:8000
Akun demo (sandi `password`): customer@carment.id, staff@carment.id, owner@carment.id, faiq@carment.id, widi@carment.id
Versi MySQL lama ada di `sql/_mysql_lama/` (tidak dipakai).

## Revisi (langkah tambahan)
- Jalankan `sql/04_revisi.sql` setelah 01, 02, 03.
- Folder `gambar/asli` dan `gambar/thumb` harus bisa ditulis PHP; thumbnail butuh ekstensi `gd` (`php -m | grep gd`).
- Ganti alamat, koordinat, dan rekening toko di bagian atas revisi `includes/helpers.php` (TOKO_*, REK_*).
- `MODE_DEMO_EMAIL=true` membuat pemulihan akun langsung membuka halaman atur ulang (tanpa email). Set `false` + konfigurasi mail untuk produksi.

## Menemukan bagian revisi
Cari teks `REVISI` (VS Code: Ctrl+Shift+F). Penanda: `[FILE BARU]` = seluruh file baru, `[BARU]` = blok baru di file lama, `[UBAH]` = bagian lama yang dimodifikasi; angka `#n` = nomor revisi. Blok diawali `>>> REVISI` dan diakhiri `<<< AKHIR REVISI`; jika hanya ada penanda awal, yang berubah adalah baris tepat di bawahnya.

## Revisi putaran 2
- Jalankan `sql/05_revisi2.sql` setelah 04.
- Penanda baru memakai awalan `R2 #n` (cari `R2 #` untuk melihat semua bagian putaran ini).
- Isi profil dan jam buka toko di bagian `R2 #7` pada `includes/helpers.php` (TOKO_NAMA, TOKO_DESKRIPSI, TOKO_JAM). Opsional: taruh `gambar/banner.jpg` untuk gambar latar banner.
- Bukti pembayaran hanya bisa dibuka lewat `reservasi/bukti.php` (cek hak akses). Di Apache, `uploads/.htaccess` memblokir akses langsung.

## Revisi putaran 3
- Jalankan `sql/06_revisi3.sql` setelah 05. Penanda baru memakai awalan `R3 #` (cari `R3 #`).
- Jadwal buka toko kini diedit staff lewat menu **Jadwal toko** (tabel `jam_toko`); konstanta `TOKO_JAM` di `helpers.php` hanya cadangan jika tabel belum ada.
- Kalender unit berupa kalender bulanan; bulan dan tahun dipilih lewat dropdown (tahun ini sampai 3 tahun ke depan).
