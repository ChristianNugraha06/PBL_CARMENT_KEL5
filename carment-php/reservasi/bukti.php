<?php /* melihat bukti pembayaran (customer pemilik, staff, owner) */
require_once __DIR__ . '/../includes/auth.php';
$u = wajib_login();
$id = (int)($_GET['id'] ?? 0);

// mendukung ?lama=1 (bukti pembayaran sebelumnya) 
// mendukung ?refund=1 (bukti pengembalian dana): pemilik reservasi, staff, owner
$s = $pdo->prepare('SELECT user_id, bukti, bukti_lama, bukti_refund FROM reservasi WHERE id=?');
$s->execute([$id]);
$r = $s->fetch();

// Customer hanya boleh melihat bukti miliknya; staff dan owner boleh semua
if (!$r || !($berkas = isset($_GET['refund']) ? $r['bukti_refund'] : (isset($_GET['lama']) ? $r['bukti_lama'] : $r['bukti'])) || ($u['role'] === 'customer' && (int)$r['user_id'] !== (int)$u['id'])) {
    http_response_code(404);
    exit('Bukti tidak ditemukan.');
}
if (!preg_match('/^([a-f0-9]{16}\.(jpg|jpeg|png|pdf)|demo-bukti\.png)$/', $berkas)) {
    http_response_code(404);
    exit('Berkas tidak valid.');
}
$path = __DIR__ . '/../uploads/' . $berkas;
if (!is_file($path)) {
    http_response_code(404);
    exit('Berkas tidak ada.');
}
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'][$ext];
while (ob_get_level()) {
    ob_end_clean(); // buang buffer dari koneksi.php agar berkas tidak rusak
}
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="bukti-' . $id . '.' . $ext . '"');
header('Cache-Control: private, max-age=300');
readfile($path);