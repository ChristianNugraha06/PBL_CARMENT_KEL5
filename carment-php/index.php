<?php 
$judul = 'Katalog'; 
require __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? ''); 
$k = $_GET['kat'] ?? '';

$s = $pdo->prepare("
    SELECT * 
    FROM barang 
    WHERE (nama ILIKE ? OR spesifikasi ILIKE ?) 
      AND (?::text = '' OR kategori = ?::text) 
    ORDER BY nama
");
$s->execute(["%$q%", "%$q%", $k, $k]); 
$rows = $s->fetchAll(); 
?>

<h1>Katalog kamera &amp; peralatan</h1>

<div class="info">
    <b>Info store:</b> Jl. Contoh No. 10, Malang (09.00-20.00). Jaminan: KTP asli + deposit sesuai unit.
</div>

<form class="bar" method="get">
    <input name="q" value="<?= e($q) ?>" placeholder="Cari nama atau spesifikasi..." aria-label="Cari">
    <select name="kat" aria-label="Kategori" onchange="this.form.submit()">
        <option value="">Semua kategori</option>
        <?php foreach (['Kamera', 'Lensa', 'Aksesori'] as $c): ?>
            <option <?= $k === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn">Cari</button>
</form>

<div class="grid">
    <?php foreach ($rows as $b): ?>
        <a class="card item" href="barang/detail.php?id=<?= $b['id'] ?>">
            <div class="ph"><?= e(mb_substr($b['kategori'], 0, 1)) ?></div>
            <b><?= e($b['nama']) ?></b>
            <span class="mut"><?= e($b['kategori']) ?></span>
            <span class="price"><?= rp($b['harga']) ?> / hari</span>
            <span><?= badge($b['status']) ?></span>
        </a>
    <?php endforeach; ?>

    <?php if (!$rows): ?>
        <p>Tidak ada unit yang cocok. Ubah kata kunci atau kategori.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>