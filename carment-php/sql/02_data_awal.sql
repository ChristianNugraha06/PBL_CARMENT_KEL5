-- =============================================================================
-- CARMENT - Data awal (wajib untuk pemasangan baru). Jalankan setelah 01_schema.sql.
-- Aman kalau dijalankan ulang. Sandi semua akun awal adalah "password": GANTI setelah login pertama.
-- =============================================================================

INSERT INTO kategori (nama) VALUES ('Kamera'), ('Lensa'), ('Aksesori')
ON CONFLICT (nama) DO NOTHING;

INSERT INTO users (nama, email, hp, password, role) VALUES
    ('Customer Demo', 'customer@carment.id', '0800000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
    ('Staff Demo',    'staff@carment.id',    '0800000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'),
    ('Owner Demo',    'owner@carment.id',    '0800000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner')
ON CONFLICT (email) DO NOTHING;

-- Jadwal buka (1 = Senin ... 7 = Minggu). Bisa diubah staff lewat menu "Jadwal toko".
INSERT INTO jam_toko (hari, buka, tutup) VALUES
    (1, '09:00', '20:00'),
    (2, '09:00', '20:00'),
    (3, '09:00', '20:00'),
    (4, '09:00', '20:00'),
    (5, '09:00', '20:00'),
    (6, '09:00', '20:00'),
    (7, '10:00', '16:00')
ON CONFLICT (hari) DO NOTHING;

-- Unit awal (hanya diisi jika katalog masih kosong)
INSERT INTO barang (nama, kategori, harga, deposit, spesifikasi, kondisi)
SELECT * FROM (VALUES
    ('Sony A7 III',              'Kamera',   250000, 1000000, 'Full-frame 24MP, 4K30, dual SD',   'Baik. Goresan halus di body bawah.'),
    ('Canon EOS R6',             'Kamera',   300000, 1200000, 'Full-frame 20MP, IBIS, 4K60',      'Baik. Karet grip sedikit aus.'),
    ('Sony FE 24-70mm f/2.8 GM', 'Lensa',    150000,  800000, 'Zoom standar f/2.8, mount Sony E', 'Sangat baik.'),
    ('Sigma 35mm f/1.4 Art',     'Lensa',    100000,  500000, 'Prime 35mm f/1.4',                 'Baik. Tutup lensa belakang hilang.'),
    ('DJI RS 3 Gimbal',          'Aksesori', 120000,  600000, 'Gimbal 3-axis, beban maks 3 kg',   'Baik. Lengkap dengan plate & tas.')
) AS v (nama, kategori, harga, deposit, spesifikasi, kondisi)
WHERE NOT EXISTS (SELECT 1 FROM barang);