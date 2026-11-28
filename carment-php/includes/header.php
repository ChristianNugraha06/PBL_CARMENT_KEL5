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
    <?php /* tampilan dikembalikan ke gaya sebelum tema Uniqlo: font Sora + Public Sans, tanpa tema.css */ ?>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<?php /* kelas peran pada body untuk membedakan tampilan tiap akun */ ?>
<body class="role-<?= $u ? e($u['role']) : 'tamu' ?>">

    <?php /* logo mengarah ke halaman utama sesuai peran */ ?>
    <header>
        <a class="brand" href="<?= BASE_URL . ($u ? halaman_utama($u['role']) : 'index.php') ?>">CAR<b>MENT</b></a>
        <button class="burger" type="button" aria-label="Menu" aria-expanded="false">☰</button>
        <nav>
            <?php /* menu Katalog hanya untuk pengunjung/pelanggan  */ ?>
            <?php if (!$u || $u['role'] === 'customer'): ?>
                <a href="<?= BASE_URL ?>index.php">Katalog</a>
            <?php endif; ?>

            <?php /* menu keranjang + jumlah isi  */ ?>
            <?php if ($u && $u['role'] === 'customer'):
                $kc = $pdo->prepare('SELECT COUNT(*) FROM keranjang WHERE user_id=?');
                $kc->execute([$u['id']]);
            ?>
                <a href="<?= BASE_URL ?>keranjang/list.php">Keranjang (<?= (int)$kc->fetchColumn() ?>)</a>
                <a href="<?= BASE_URL ?>reservasi/riwayat.php">Riwayat sewa</a>
            <?php endif; ?>

            <?php /* menu kategori (staff)  */ ?>
            <?php /* menu Jadwal toko (staff)  */ ?>
            <?php if ($u && $u['role'] === 'staff'): ?>
                <a href="<?= BASE_URL ?>reservasi/list.php">Reservasi</a>
                <a href="<?= BASE_URL ?>barang/list.php">Kelola barang</a>
                <a href="<?= BASE_URL ?>kategori/list.php">Kategori</a>
                <a href="<?= BASE_URL ?>toko/jam.php">Jadwal toko</a>
                <a href="<?= BASE_URL ?>maintenance/list.php">Maintenance</a>
            <?php endif; ?>

            <?php /* menu Feedback untuk staff & owner  */ ?>
            <?php if ($u && in_array($u['role'], ['owner', 'staff'])): ?>
                <a href="<?= BASE_URL ?>laporan/index.php">Laporan</a>
                <a href="<?= BASE_URL ?>feedback/list.php">Feedback</a>
            <?php endif; ?>
        </nav>

        <?php /* nama user menjadi tautan profil  */ ?>
        <?php /* label peran + popup konfirmasi keluar  */ ?>
        <?php if ($u): ?>
            <span class="peran peran-<?= e($u['role']) ?>"><?= e(nama_peran($u['role'])) ?></span>
            <a class="user-link" href="<?= BASE_URL ?>auth/profil.php"><?= e($u['nama']) ?></a>
            <a class="btn alt sm" href="<?= BASE_URL ?>auth/logout.php" data-popup="Keluar dari akun ini?" data-ya="Ya, keluar">Keluar</a>
        <?php else: ?>
            <a class="btn alt sm" href="<?= BASE_URL ?>auth/login.php">Masuk</a>
            <a class="btn sm" href="<?= BASE_URL ?>auth/register.php">Daftar</a>
        <?php endif; ?>
    </header>
    <main>
        <?php if ($f = flash()): ?>
            <p class="info" role="status"><?= e($f) ?></p>
        <?php endif; ?>