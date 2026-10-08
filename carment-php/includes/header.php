<?php 
require_once __DIR__ . '/auth.php'; 
$u = current_user(); 
$judul = $judul ?? 'CARMENT'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($judul) ?> - CARMENT</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<header>
    <a class="brand" href="<?= BASE_URL ?>index.php">CAR<b>MENT</b></a>
    <nav>
        <a href="<?= BASE_URL ?>index.php">Katalog</a>
        <?php if ($u && $u['role'] === 'customer'): ?>
            <a href="<?= BASE_URL ?>reservasi/riwayat.php">Riwayat sewa</a>
        <?php endif; ?>
        <?php if ($u && $u['role'] === 'staff'): ?>
            <a href="<?= BASE_URL ?>reservasi/list.php">Reservasi</a>
            <a href="<?= BASE_URL ?>barang/list.php">Kelola barang</a>
            <a href="<?= BASE_URL ?>maintenance/list.php">Maintenance</a>
        <?php endif; ?>
        <?php if ($u && in_array($u['role'], ['owner','staff'])): ?>
            <a href="<?= BASE_URL ?>laporan/index.php">Laporan</a>
        <?php endif; ?>
    </nav>
    
    <?php if ($u): ?>
        <span><?= e($u['nama']) ?> (<?= e($u['role']) ?>)</span>
        <a class="btn alt sm" href="<?= BASE_URL ?>auth/logout.php">Keluar</a>
    <?php else: ?>
        <a class="btn alt sm" href="<?= BASE_URL ?>auth/login.php">Masuk</a>
        <a class="btn sm" href="<?= BASE_URL ?>auth/register.php">Daftar</a>
    <?php endif; ?>
</header>
<main>
    <?php if ($f = flash()): ?>
        <p class="info" role="status"><?= e($f) ?></p>
    <?php endif; ?>