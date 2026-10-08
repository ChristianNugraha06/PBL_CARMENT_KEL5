<?php 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

$id = (int)($_POST['id'] ?? 0); 
$kondisi = ($_POST['kondisi'] ?? '') === 'Rusak' ? 'Rusak' : 'Baik'; 
$cat = trim($_POST['catatan'] ?? '');

$pdo->beginTransaction();

$s = $pdo->prepare("SELECT r.*, b.harga FROM reservasi r JOIN barang b ON b.id=r.barang_id WHERE r.id=? AND r.status='Dipinjam' FOR UPDATE OF r"); 
$s->execute([$id]); 
$r = $s->fetch();

if (!$r) { 
    $pdo->rollBack(); 
    redirect('reservasi/list.php'); 
}

$denda = (int)round(max(0, hari($r['tgl_selesai'], date('Y-m-d'))) * $r['harga'] * DENDA_PERSEN);

$pdo->prepare("UPDATE reservasi SET status='Selesai', denda=? WHERE id=?")
    ->execute([$denda, $id]);

$pdo->prepare('INSERT INTO inspeksi(reservasi_id,kondisi,catatan,tgl) VALUES(?,?,?,CURRENT_DATE)')
    ->execute([$id, $kondisi, $cat]);

$pdo->prepare("UPDATE barang SET status=?, kondisi=CASE WHEN ?::text<>'' THEN ?::text ELSE kondisi END WHERE id=?")
    ->execute([$kondisi === 'Rusak' ? 'Maintenance' : 'Available', $cat, $cat, $r['barang_id']]);

if ($kondisi === 'Rusak') {
    $pdo->prepare("INSERT INTO maintenance(barang_id,masalah,status,tgl) VALUES(?,?,'Dibuka',CURRENT_DATE)")
        ->execute([$r['barang_id'], $cat ?: 'Rusak saat pengembalian']);
}

$pdo->commit(); 

flash(
    $kondisi === 'Rusak' 
        ? 'Inspeksi disimpan. Tiket maintenance dibuat.' 
        : 'Inspeksi disimpan. Unit kembali Available.' . ($denda ? ' Denda ' . rp($denda) . '.' : '')
);

redirect('reservasi/list.php');