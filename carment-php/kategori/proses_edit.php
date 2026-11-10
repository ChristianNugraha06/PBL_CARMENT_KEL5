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
    $pdo->prepare('UPDATE kategori SET nama=? WHERE id=?')->execute([$n, (int)($_POST['id'] ?? 0)]); 
    flash('Kategori diperbarui (unit ikut menyesuaikan).'); 
} catch (PDOException $e) { 
    flash('Nama kategori sudah dipakai.'); 
}

redirect('kategori/list.php');