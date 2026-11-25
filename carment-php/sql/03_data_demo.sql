-- =============================================================================
-- CARMENT - Data demo (opsional). Jalanin setelah 01_schema.sql dan 02_data_awal.sql.
-- Isi: 2 pelanggan, 3 unit tambahan, 13 reservasi berbagai status, hasil inspeksi, tiket maintenance.
-- Aman dijalankan ulang (data demo lama diganti). Semua tanggal relatif terhadap hari ini.
-- Sandi semua akun: "password".
-- =============================================================================

INSERT INTO users (nama, email, hp, password, role) VALUES
    ('Faiq Rifqy',  'faiq@carment.id', '0800000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
    ('Widi Wisuda', 'widi@carment.id', '0800000005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer')
ON CONFLICT (email) DO NOTHING;

-- Bersihkan data demo lama (id >= 101) dan kembalikan status unit awal
DELETE FROM inspeksi    WHERE id >= 101 OR reservasi_id >= 101;
DELETE FROM maintenance WHERE id >= 101;
DELETE FROM reservasi   WHERE id >= 101;

INSERT INTO barang (id, nama, kategori, harga, deposit, spesifikasi, kondisi, status) VALUES
    (6, 'Fujifilm X-T4',        'Kamera',   200000, 900000, 'APS-C 26MP, IBIS, video 4K60',          'Sedang diperbaiki: shutter macet.',           'Maintenance'),
    (7, 'Godox AD200 Flash',    'Aksesori',  90000, 400000, 'Flash portabel 200Ws, baterai lithium', 'Sedang diperbaiki: head flash tidak menyala.', 'Maintenance'),
    (8, 'Manfrotto 055 Tripod', 'Aksesori',  60000, 300000, 'Tripod aluminium, beban maks 9 kg',     'Sangat baik. Lengkap dengan head.',           'Available')
ON CONFLICT (id) DO UPDATE SET
    nama        = EXCLUDED.nama,
    kategori    = EXCLUDED.kategori,
    harga       = EXCLUDED.harga,
    deposit     = EXCLUDED.deposit,
    spesifikasi = EXCLUDED.spesifikasi,
    kondisi     = EXCLUDED.kondisi,
    status      = EXCLUDED.status;

UPDATE barang SET status = 'Available' WHERE id <= 5;

-- 7 selesai (grafik bulanan), 2 sedang dipinjam, 1 dikonfirmasi, 2 menunggu verifikasi, 1 ditolak
INSERT INTO reservasi (id, user_id, barang_id, tgl_mulai, tgl_selesai, sewa, deposit, denda, status, bukti, alasan_tolak, created_at)
SELECT v.id, u.id, v.barang_id, v.mulai, v.selesai, v.sewa, v.deposit, v.denda, v.status, 'demo-bukti.png', v.alasan, v.dibuat
FROM (VALUES
    -- id, pelanggan,              unit, mulai,              selesai,            sewa,   deposit,  denda,  status,         alasan penolakan,                      dibuat
    (101, 'customer@carment.id', 1, CURRENT_DATE - 100, CURRENT_DATE - 98,  750000, 1000000,      0, 'Selesai',      NULL,                                  NOW() - INTERVAL '105 days'),
    (102, 'faiq@carment.id',     2, CURRENT_DATE - 75,  CURRENT_DATE - 73,  900000, 1200000, 150000, 'Selesai',      NULL,                                  NOW() - INTERVAL '80 days'),
    (103, 'widi@carment.id',     3, CURRENT_DATE - 50,  CURRENT_DATE - 49,  300000,  800000,      0, 'Selesai',      NULL,                                  NOW() - INTERVAL '53 days'),
    (104, 'customer@carment.id', 4, CURRENT_DATE - 40,  CURRENT_DATE - 36,  500000,  500000,      0, 'Selesai',      NULL,                                  NOW() - INTERVAL '44 days'),
    (105, 'faiq@carment.id',     5, CURRENT_DATE - 25,  CURRENT_DATE - 24,  240000,  600000, 120000, 'Selesai',      NULL,                                  NOW() - INTERVAL '28 days'),
    (106, 'widi@carment.id',     6, CURRENT_DATE - 12,  CURRENT_DATE - 10,  600000,  900000,      0, 'Selesai',      NULL,                                  NOW() - INTERVAL '15 days'),
    (107, 'customer@carment.id', 7, CURRENT_DATE - 9,   CURRENT_DATE - 8,   180000,  400000,      0, 'Selesai',      NULL,                                  NOW() - INTERVAL '11 days'),
    (108, 'faiq@carment.id',     2, CURRENT_DATE - 3,   CURRENT_DATE - 1,   900000, 1200000,      0, 'Dipinjam',     NULL,                                  NOW() - INTERVAL '6 days'),
    (109, 'widi@carment.id',     5, CURRENT_DATE - 1,   CURRENT_DATE + 1,   360000,  600000,      0, 'Dipinjam',     NULL,                                  NOW() - INTERVAL '4 days'),
    (110, 'customer@carment.id', 1, CURRENT_DATE + 3,   CURRENT_DATE + 5,   750000, 1000000,      0, 'Dikonfirmasi', NULL,                                  NOW() - INTERVAL '1 day'),
    (111, 'widi@carment.id',     3, CURRENT_DATE + 6,   CURRENT_DATE + 8,   450000,  800000,      0, 'Menunggu',     NULL,                                  NOW()),
    (112, 'customer@carment.id', 8, CURRENT_DATE + 10,  CURRENT_DATE + 11,  120000,  300000,      0, 'Menunggu',     NULL,                                  NOW()),
    (113, 'faiq@carment.id',     4, CURRENT_DATE + 2,   CURRENT_DATE + 3,   200000,  500000,      0, 'Ditolak',      'Bukti transfer tidak sesuai nominal.', NOW() - INTERVAL '2 days')
) AS v (id, email, barang_id, mulai, selesai, sewa, deposit, denda, status, alasan, dibuat)
JOIN users u ON u.email = v.email;

UPDATE barang SET status = 'Rented' WHERE id IN (2, 5); -- unit yang sedang dipinjam (reservasi 108 & 109)

INSERT INTO inspeksi (id, reservasi_id, kondisi, catatan, tgl) VALUES
    (101, 101, 'Baik',        'Lengkap, tanpa kerusakan.',                 CURRENT_DATE - 98),
    (102, 102, 'Kurang Baik', 'Terlambat 1 hari. Karet grip sedikit aus.', CURRENT_DATE - 72),
    (103, 103, 'Baik',        '',                                          CURRENT_DATE - 49),
    (104, 104, 'Baik',        'Tutup lensa belakang hilang.',              CURRENT_DATE - 36),
    (105, 105, 'Baik',        'Terlambat 2 hari. Plate dan tas lengkap.',  CURRENT_DATE - 22),
    (106, 106, 'Rusak',       'Shutter macet saat dites.',                 CURRENT_DATE - 10),
    (107, 107, 'Rusak',       'Head flash tidak menyala.',                 CURRENT_DATE - 8);

INSERT INTO maintenance (id, barang_id, masalah, status, tgl) VALUES
    (101, 6, 'Shutter macet saat dites setelah pengembalian',    'Dibuka',  CURRENT_DATE - 10),
    (102, 7, 'Head flash tidak menyala setelah dipakai outdoor', 'Dibuka',  CURRENT_DATE - 8),
    (103, 4, 'Penggantian tutup lensa belakang yang hilang',     'Selesai', CURRENT_DATE - 35);

-- Samakan nomor urut (sequence) dengan id eksplisit di atas agar INSERT dari aplikasi tidak bentrok
DO $$
DECLARE
    t TEXT;
BEGIN
    FOREACH t IN ARRAY ARRAY['users', 'barang', 'reservasi', 'inspeksi', 'maintenance'] LOOP
        EXECUTE format(
            'SELECT setval(pg_get_serial_sequence(%L, ''id''), COALESCE((SELECT MAX(id) FROM %I), 0) + 1, false)',
            t,
            t
        );
    END LOOP;
END $$;