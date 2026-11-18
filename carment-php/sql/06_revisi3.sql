-- jadwal buka toko yang bisa diedit staff (jalanin kalau sql 01-05; aman diulang, edit staff tidak tertimpa)
CREATE TABLE IF NOT EXISTS jam_toko (
  hari SMALLINT PRIMARY KEY CHECK (hari BETWEEN 1 AND 7),   -- 1=Senin ... 7=Minggu
  buka TIME, 
  tutup TIME,                                               -- keduanya NULL = libur
  CHECK ((buka IS NULL AND tutup IS NULL) OR (buka IS NOT NULL AND tutup IS NOT NULL AND buka < tutup))
);

INSERT INTO jam_toko (hari, buka, tutup) VALUES 
(1, '09:00', '20:00'),
(2, '09:00', '20:00'),
(3, '09:00', '20:00'),
(4, '09:00', '20:00'),
(5, '09:00', '20:00'),
(6, '09:00', '20:00'),
(7, '10:00', '16:00')
ON CONFLICT (hari) DO NOTHING;