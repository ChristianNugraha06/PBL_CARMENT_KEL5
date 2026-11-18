-- migrasi putaran revisi 2 (jalanin kalau sql 01 - 04. aman diulang)
ALTER TABLE reservasi ADD COLUMN IF NOT EXISTS alasan_tolak TEXT;           -- alasan penolakan pembayaran oleh staff

ALTER TABLE inspeksi DROP CONSTRAINT IF EXISTS inspeksi_kondisi_check;
ALTER TABLE inspeksi ALTER COLUMN kondisi TYPE VARCHAR(12);
ALTER TABLE inspeksi ADD CONSTRAINT inspeksi_kondisi_check CHECK (kondisi IN ('Baik', 'Kurang Baik', 'Rusak'));   -- kondisi baru: Kurang Baik

-- Penyesuaian data demo (ga ngaruh kalau 03_demo_data.sql belum dijalankan)
UPDATE inspeksi SET kondisi = 'Kurang Baik' WHERE reservasi_id = 102;
UPDATE reservasi SET alasan_tolak = 'Bukti transfer tidak sesuai nominal.' WHERE id = 113;