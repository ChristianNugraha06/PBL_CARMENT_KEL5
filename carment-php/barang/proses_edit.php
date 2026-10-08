<?php 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

$nama = trim($_POST['nama'] ?? ''); 
$harga = (int)($_POST['harga'] ?? 0); 
$id = (int)($_POST['id'] ?? 0);

if ($nama === '' || $harga <= 0) { 
    flash('Nama dan harga sewa wajib diisi.'); 
    redirect("barang/edit.php?id=$id"); 
}

$pdo->prepare('UPDATE barang SET nama=?,kategori=?,harga=?,deposit=?,spesifikasi=?,kondisi=? WHERE id=?')
    ->execute([
        $nama, 
        $_POST['kategori'] ?? 'Kamera', 
        $harga, 
        (int)($_POST['deposit'] ?? 0), 
        trim($_POST['spesifikasi'] ?? ''), 
        trim($_POST['kondisi'] ?? ''), 
        $id
    ]);

flash('Unit diperbarui.'); 
redirect('barang/list.php');