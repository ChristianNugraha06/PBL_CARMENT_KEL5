<?php 
/* >>> REVISI [FILE BARU] #5 (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

$n = trim($_POST['nama'] ?? ''); 

if ($n === '') { 
    flash('Nama kategori wajib diisi.'); 
    redirect('kategori/list.php'); 
}

try { 
    $pdo->prepare('INSERT INTO kategori(nama) VALUES(?)')->execute([$n]); 
    flash('Kategori ditambahkan.'); 
} catch (PDOException $e) { 
    flash('Kategori sudah ada.'); 
}

redirect('kategori/list.php');