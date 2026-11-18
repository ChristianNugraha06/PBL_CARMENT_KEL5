<?php
const DENDA_PERSEN = 0.5; // denda/hari = 50% harga sewa (konfirmasi ke Owner)

function e($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function rp($n) {
  return 'Rp' . number_format((float)$n, 0, ',', '.');
}

function redirect($p) {
  header('Location: ' . BASE_URL . $p);
  exit;
}

function flash($m = null) {
  if ($m !== null) {
    $_SESSION['flash'] = $m;
    return;
  }
  $f = $_SESSION['flash'] ?? '';
  unset($_SESSION['flash']);
  return $f;
}

function badge($s) {
  return '<span class="badge ' . e($s) . '">' . e($s) . '</span>';
}

function hari($a, $b) {
  return (int)round((strtotime($b) - strtotime($a)) / 86400);
}

function tgl_valid($d) {
  return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
}

const TOKO_ALAMAT = 'Jl. Kyai Parseh jaya Rt05, Rw03, Bumiayu, Malang'; // GANTI dengan alamat asli toko
const TOKO_LAT = -8.014179063419938;
const TOKO_LNG = 112.63522495310939; // GANTI koordinat toko (klik kanan di Google Maps)
const REK_BANK = 'BCA';
const REK_NO = '1234567890';
const REK_AN = 'CARMENT Rental'; // GANTI rekening toko
const BATAS_BAYAR_MENIT = 10;
const MODE_DEMO_EMAIL = true; // true: tautan pemulihan langsung dibuka (tanpa SMTP). Produksi: false + mail()

function info_rekening() {
  return '<div class="info">Pembayaran via transfer ke <b>' . e(REK_BANK) . ' ' . e(REK_NO) . '</b> a.n. <b>' . e(REK_AN) . '</b>. Pelunasan (sewa + deposit) harus masuk dalam <b>' . BATAS_BAYAR_MENIT . ' menit</b> setelah reservasi diajukan; lewat dari itu reservasi dibatalkan otomatis.</div>';
}

function daftar_kategori() {
  global $pdo;
  return $pdo->query('SELECT nama FROM kategori ORDER BY nama')->fetchAll(PDO::FETCH_COLUMN);
}

function sedang_disewa($id) {
  global $pdo;
  $s = $pdo->prepare("SELECT 1 FROM reservasi WHERE barang_id=? AND (status='Dipinjam' OR (status='Dikonfirmasi' AND CURRENT_DATE BETWEEN tgl_mulai AND tgl_selesai)) LIMIT 1");
  $s->execute([$id]);
  return (bool)$s->fetchColumn();
}

function foto_thumb($n) {
  return $n ? BASE_URL . 'gambar/thumb/' . rawurlencode($n) : '';
}

function foto_asli($n) {
  return $n ? BASE_URL . 'gambar/asli/' . rawurlencode($n) : '';
}

function simpan_foto($f) { // return [nama_file|null, pesan_error|null]; asli -> gambar/asli, thumbnail 320px -> gambar/thumb
  if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
    return [null, null];
  }
  
  $info = $f['error'] === UPLOAD_ERR_OK ? @getimagesize($f['tmp_name']) : false;
  $tipe = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
  
  if (!$info || !isset($tipe[$info[2]]) || $f['size'] > 3 * 1024 * 1024) {
    return [null, 'Foto harus jpg/png/webp, maks 3 MB.'];
  }
  
  $ext = $tipe[$info[2]];
  $nama = bin2hex(random_bytes(8)) . '.' . $ext;
  $root = __DIR__ . '/../gambar/';
  
  if (!move_uploaded_file($f['tmp_name'], $root . 'asli/' . $nama)) {
    return [null, 'Gagal menyimpan foto (cek izin folder gambar/).'];
  }
  
  $buat = ['jpg' => 'imagecreatefromjpeg', 'png' => 'imagecreatefrompng', 'webp' => 'imagecreatefromwebp'][$ext];
  $tulis = ['jpg' => 'imagejpeg', 'png' => 'imagepng', 'webp' => 'imagewebp'][$ext];
  
  if (function_exists($buat) && function_exists('imagescale') && ($im = @$buat($root . 'asli/' . $nama))) {
    $t = imagescale($im, min(320, $info[0]));
    $tulis($t, $root . 'thumb/' . $nama);
  } else {
    copy($root . 'asli/' . $nama, $root . 'thumb/' . $nama); // tanpa ekstensi GD: thumbnail = salinan asli
  }
  
  return [$nama, null];
}

function hapus_foto($n) {
  if (!preg_match('/^[a-f0-9]+\.(jpg|png|webp)$/', (string)$n)) {
    return;
  }
  foreach (['asli', 'thumb'] as $d) {
    @unlink(__DIR__ . '/../gambar/' . $d . '/' . $n);
  }
}

// profil toko + jam operasional
const TOKO_NAMA = 'CARMENT';
const TOKO_DESKRIPSI = 'CARMENT (Camera Rent and Equipment) menyewakan kamera, lensa, dan aksesori fotografi untuk kebutuhan foto, video, dan produksi konten. Pilih unit, atur tanggal, dan ambil di toko kami.'; // GANTI sesuai profil toko
const TOKO_JAM = [1 => ['09:00', '20:00'], 2 => ['09:00', '20:00'], 3 => ['09:00', '20:00'], 4 => ['09:00', '20:00'], 5 => ['09:00', '20:00'], 6 => ['09:00', '20:00'], 7 => ['10:00', '16:00']]; // 1=Senin ... 7=Minggu; null = tutup. GANTI sesuai jam asli
const NAMA_HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

function toko_status() { // [bool buka, teks status] berdasarkan jam server (Asia/Jakarta)
  // jam diambil dari database
  $j = jam_toko()[(int)date('N')];
  $now = date('H:i');
  
  if ($j && $now >= $j[0] && $now < $j[1]) {
    return [true, 'Buka sekarang, tutup pukul ' . str_replace(':', '.', $j[1])];
  }
  
  return [false, 'Tutup sekarang' . ($j && $now < $j[0] ? ', buka pukul ' . str_replace(':', '.', $j[0]) : '')];
}

// nama bulan + jadwal buka toko dari database 
const NAMA_BULAN = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

function jam_toko() { // [1..7 => ['HH:MM','HH:MM'] | null(libur)]; diatur staff di toko/jam.php, fallback ke TOKO_JAM bila tabel belum ada
  static $cache = null;
  if ($cache !== null) {
    return $cache;
  }
  
  global $pdo;
  try {
    $rows = $pdo->query('SELECT hari, buka, tutup FROM jam_toko ORDER BY hari')->fetchAll();
  } catch (PDOException $e) {
    $rows = [];
  }
  
  if (count($rows) !== 7) {
    return $cache = TOKO_JAM;
  }
  
  $cache = [];
  foreach ($rows as $r) {
    $cache[(int)$r['hari']] = $r['buka'] ? [substr($r['buka'], 0, 5), substr($r['tutup'], 0, 5)] : null;
  }
  
  return $cache;
}