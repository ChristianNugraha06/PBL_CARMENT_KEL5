<?php 
/* >>> REVISI [FILE BARU] #17 #18 pelunasan (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
$u = wajib_login(['customer']); 
csrf_cek();

$id = (int)($_POST['id'] ?? 0); 
$f = $_FILES['bukti'] ?? null; 
$ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));

if (!$f || $f['error'] !== UPLOAD_ERR_OK || !in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true) || $f['size'] > 2 * 1024 * 1024) { 
    flash('Unggah bukti transfer (jpg/png/pdf, maks 2 MB).'); 
    redirect("reservasi/bayar.php?id=$id"); 
}

$pdo->beginTransaction();

$s = $pdo->prepare("SELECT id FROM reservasi WHERE id=? AND user_id=? AND status='Menunggu Pembayaran' AND batas_bayar > NOW() FOR UPDATE"); 
$s->execute([$id, $u['id']]); 

if (!$s->fetch()) { 
    $pdo->rollBack(); 
    flash('Waktu pembayaran habis, reservasi dibatalkan otomatis.'); 
    redirect('reservasi/riwayat.php'); 
}

$nama = bin2hex(random_bytes(8)) . '.' . $ext; 
move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $nama);

$pdo->prepare("UPDATE reservasi SET status='Menunggu', bukti=?, batas_bayar=NULL WHERE id=?")
    ->execute([$nama, $id]);

$pdo->commit(); 

flash('Pelunasan diterima. Menunggu verifikasi staff.'); 
redirect('reservasi/riwayat.php');