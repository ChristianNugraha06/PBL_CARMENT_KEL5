<?php 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

$id = (int)($_POST['id'] ?? 0);

$pdo->prepare("UPDATE barang SET status='Available' WHERE id=(SELECT barang_id FROM maintenance WHERE id=? AND status='Dibuka')")
    ->execute([$id]);

$pdo->prepare("UPDATE maintenance SET status='Selesai' WHERE id=?")
    ->execute([$id]);

flash('Perbaikan selesai. Unit kembali Available.'); 
redirect('maintenance/list.php');