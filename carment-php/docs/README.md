# CARMENT (PHP native + PostgreSQL, DBeaver)
1. Pastikan PHP punya ekstensi `pdo_pgsql` (`php -m | grep pgsql`).
2. DBeaver: koneksi ke PostgreSQL, klik kanan Databases > Create New Database > `carment`.
3. Buka SQL Editor di database carment, jalankan berurutan dengan Alt+X: `sql/01_schema.sql`, `02_users.sql`, `03_demo_data.sql`.
4. Sesuaikan user/sandi di `includes/koneksi.php`, lalu dari folder proyek: `php -S localhost:8000`, buka http://localhost:8000
Akun demo (sandi `password`): customer@carment.id, staff@carment.id, owner@carment.id, faiq@carment.id, widi@carment.id
Versi MySQL lama ada di `sql/_mysql_lama/` (tidak dipakai).
