<?php 
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

$f = $_FILES['bukti'] ?? null; 
$ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));

if (!$f || $f['error'] !== UPLOAD_ERR_OK || !in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true) || $f['size'] > 2 * 1024 * 1024) { 
    flash('Unggah bukti transfer (jpg/png/pdf, maks 2 MB).'); 
    redirect($back); 
}

$pdo->beginTransaction();

$s = $pdo->prepare('SELECT * FROM barang WHERE id=? FOR UPDATE'); 
$s->execute([$id]); 
$brg = $s->fetch(); // kunci baris: cegah double booking

if (!$brg || $brg['status'] === 'Maintenance') { 
    $pdo->rollBack(); 
    flash('Unit tidak dapat disewa.'); 
    redirect($back); 
}

$c = $pdo->prepare("SELECT COUNT(*) FROM reservasi WHERE barang_id=? AND status IN ('Menunggu','Dikonfirmasi','Dipinjam') AND tgl_mulai<=? AND tgl_selesai>=?");
$c->execute([$id, $b, $a]);

if ($c->fetchColumn() > 0) { 
    $pdo->rollBack(); 
    flash('Unit sudah dipesan pada tanggal tersebut. Pilih tanggal lain.'); 
    redirect($back); 
}

$nama = bin2hex(random_bytes(8)) . '.' . $ext; 
move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $nama);

$sewa = (hari($a, $b) + 1) * $brg['harga'];

$pdo->prepare("INSERT INTO reservasi(user_id,barang_id,tgl_mulai,tgl_selesai,sewa,deposit,denda,status,bukti) VALUES(?,?,?,?,?,?,0,'Menunggu',?)")
    ->execute([
        $u['id'], 
        $id, 
        $a, 
        $b, 
        $sewa, 
        $brg['deposit'], 
        $nama
    ]);

$pdo->commit(); 

flash('Reservasi diajukan. Menunggu konfirmasi staff.'); 
redirect('reservasi/riwayat.php');