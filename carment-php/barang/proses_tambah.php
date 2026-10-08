<?php 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

$nama = trim($_POST['nama'] ?? ''); 
$harga = (int)($_POST['harga'] ?? 0);

if ($nama === '' || $harga <= 0) { 
    flash('Nama dan harga sewa wajib diisi.'); 
    redirect('barang/tambah.php'); 
}

$pdo->prepare("INSERT INTO barang(nama,kategori,harga,deposit,spesifikasi,kondisi,status) VALUES(?,?,?,?,?,?,'Available')")
    ->execute([
        $nama, 
        $_POST['kategori'] ?? 'Kamera', 
        $harga, 
        (int)($_POST['deposit'] ?? 0), 
        trim($_POST['spesifikasi'] ?? ''), 
        trim($_POST['kondisi'] ?? 'Baik.')
    ]);

flash('Unit ditambahkan.'); 
redirect('barang/list.php');