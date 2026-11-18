<?php
/* melihat bukti pembayaran (customer pemilik, staff, owner) */
require_once __DIR__ . '/../includes/auth.php';
$u = wajib_login();
$id = (int)($_GET['id'] ?? 0);

$s = $pdo->prepare('SELECT user_id, bukti FROM reservasi WHERE id=?');
$s->execute([$id]);
$r = $s->fetch();

// Customer hanya boleh melihat bukti miliknya; staff dan owner boleh semua
if (!$r || !$r['bukti'] || ($u['role'] === 'customer' && (int)$r['user_id'] !== (int)$u['id'])) {
  http_response_code(404);
  exit('Bukti tidak ditemukan.');
}

if (!preg_match('/^([a-f0-9]{16}\.(jpg|jpeg|png|pdf)|demo-bukti\.png)$/', $r['bukti'])) {
  http_response_code(404);
  exit('Berkas tidak valid.');
}

$path = __DIR__ . '/../uploads/' . $r['bukti'];
if (!is_file($path)) {
  http_response_code(404);
  exit('Berkas tidak ada.');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = [
  'jpg' => 'image/jpeg',
  'jpeg' => 'image/jpeg',
  'png' => 'image/png',
  'pdf' => 'application/pdf'
][$ext];

while (ob_get_level()) {
  ob_end_clean(); // buang buffer dari koneksi.php agar berkas tidak rusak
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="bukti-' . $id . '.' . $ext . '"');
header('Cache-Control: private, max-age=300');

readfile($path);