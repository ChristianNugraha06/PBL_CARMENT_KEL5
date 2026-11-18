<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_login(['staff']);
csrf_cek();

$id = (int)($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';
$map = [
  'konfirmasi' => ['Menunggu', 'Dikonfirmasi'],
  'tolak' => ['Menunggu', 'Ditolak'],
  'serahkan' => ['Dikonfirmasi', 'Dipinjam']
];

if (!isset($map[$aksi])) {
  flash('Aksi tidak dikenal.');
  redirect('reservasi/list.php');
}

[$dari, $ke] = $map[$aksi];

// alasan penolakan wajib + simpan
$alasan = trim($_POST['alasan'] ?? '');
if ($aksi === 'tolak' && strlen($alasan) < 5) {
  flash('Alasan penolakan wajib diisi (minimal 5 karakter).');
  redirect('reservasi/list.php');
}

$s = $pdo->prepare('UPDATE reservasi SET status=?, alasan_tolak=? WHERE id=? AND status=?');
$s->execute([$ke, $aksi === 'tolak' ? $alasan : null, $id, $dari]);

if ($ke === 'Dipinjam' && $s->rowCount()) {
  $pdo->prepare("UPDATE barang SET status='Rented' WHERE id=(SELECT barang_id FROM reservasi WHERE id=?)")->execute([$id]);
}

flash("Status reservasi diubah: $ke.");
redirect('reservasi/list.php');