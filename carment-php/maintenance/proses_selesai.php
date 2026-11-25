<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_login(['staff']);
csrf_cek();
$id = (int)($_POST['id'] ?? 0);

// tutup tiket dulu; unit baru kembali Available jika tidak ada tiket lain yang masih terbuka
$pdo->beginTransaction();
$pdo->prepare("UPDATE maintenance SET status='Selesai' WHERE id=? AND status='Dibuka'")->execute([$id]);
$pdo->prepare("UPDATE barang SET status='Available' WHERE status='Maintenance' AND id=(SELECT barang_id FROM maintenance WHERE id=?) AND NOT EXISTS (SELECT 1 FROM maintenance m WHERE m.barang_id=barang.id AND m.status='Dibuka')")->execute([$id]);
$pdo->commit();

flash('Perbaikan selesai. Unit kembali Available.');
redirect('maintenance/list.php');