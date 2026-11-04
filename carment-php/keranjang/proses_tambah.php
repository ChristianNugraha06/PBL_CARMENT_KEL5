<?php 
/* >>> REVISI [FILE BARU] #17 (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
$u = wajib_login(['customer']); 
csrf_cek();

$id = (int)($_POST['barang_id'] ?? 0); 
$a = $_POST['tgl_mulai'] ?? ''; 
$b = $_POST['tgl_selesai'] ?? ''; 
$back = "barang/detail.php?id=$id";

if (!tgl_valid($a) || !tgl_valid($b) || $a < date('Y-m-d') || $b < $a) { 
    flash('Tanggal tidak valid.'); 
    redirect($back); 
}

$s = $pdo->prepare('SELECT status FROM barang WHERE id=?'); 
$s->execute([$id]); 
$st = $s->fetchColumn();

if (!$st || $st === 'Maintenance') { 
    flash('Unit tidak dapat disewa.'); 
    redirect($back); 
}

$c = $pdo->prepare("SELECT COUNT(*) FROM reservasi WHERE barang_id=? AND status IN ('Menunggu Pembayaran', 'Menunggu', 'Dikonfirmasi', 'Dipinjam') AND tgl_mulai<=? AND tgl_selesai>=?");
$c->execute([$id, $b, $a]);

if ($c->fetchColumn() > 0) { 
    flash('Tanggal tersebut sudah dibooking atau sedang terkunci. Pilih tanggal lain.'); 
    redirect($back); 
}

$d = $pdo->prepare('SELECT 1 FROM keranjang WHERE user_id=? AND barang_id=? AND tgl_mulai=? AND tgl_selesai=?'); 
$d->execute([$u['id'], $id, $a, $b]);

if (!$d->fetchColumn()) {
    $pdo->prepare('INSERT INTO keranjang(user_id, barang_id, tgl_mulai, tgl_selesai) VALUES(?, ?, ?, ?)')->execute([$u['id'], $id, $a, $b]);
}

flash('Ditambahkan ke keranjang. Tanggal belum dikunci sampai Anda mengajukan reservasi.'); 
redirect('keranjang/list.php');