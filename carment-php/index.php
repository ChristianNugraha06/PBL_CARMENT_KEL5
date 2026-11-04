<?php 
$judul = 'Katalog'; 
require __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? ''); 
$k = $_GET['kat'] ?? '';

$s = $pdo->prepare("SELECT * FROM barang WHERE (nama ILIKE ? OR spesifikasi ILIKE ?) AND (?::text='' OR kategori=?::text) ORDER BY nama");
$s->execute(["%$q%", "%$q%", $k, $k]); 
$rows = $s->fetchAll(); 
?>

<h1>Katalog kamera &amp; peralatan</h1>

<?php /* >>> REVISI #15 [UBAH] alamat + peta toko (blok sampai tanda AKHIR) */ ?>
<div class="info">
    <b>Info store:</b> <?= e(TOKO_ALAMAT) ?> (09.00-20.00). Jaminan: KTP asli + deposit sesuai unit. 
    <a href="https://www.google.com/maps?q=<?= TOKO_LAT ?>,<?= TOKO_LNG ?>" target="_blank" rel="noopener">Buka di Google Maps</a>
    <iframe class="peta" title="Lokasi toko" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox=<?= TOKO_LNG - 0.006 ?>,<?= TOKO_LAT - 0.004 ?>,<?= TOKO_LNG + 0.006 ?>,<?= TOKO_LAT + 0.004 ?>&amp;layer=mapnik&amp;marker=<?= TOKO_LAT ?>,<?= TOKO_LNG ?>"></iframe>
</div>
<?php /* <<< AKHIR REVISI */ ?>

<form class="bar" method="get">
    <input name="q" value="<?= e($q) ?>" placeholder="Cari nama atau spesifikasi..." aria-label="Cari">
    <select name="kat" aria-label="Kategori" onchange="this.form.submit()">
        <option value="">Semua kategori</option>
        <?php /* >>> REVISI #5 [UBAH] kategori dari database (baris di bawah ini) */ ?>
        <?php foreach (daftar_kategori() as $c): ?>
            <option <?= $k === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn">Cari</button>
</form>

<div class="grid">
    <?php foreach ($rows as $b): ?>
        <?php /* >>> REVISI #14 [UBAH] thumbnail + lazy load (baris di bawah ini) */ ?>
        <a class="card item" href="barang/detail.php?id=<?= $b['id'] ?>">
            <div class="ph">
                <?php if ($b['foto_nama']): ?>
                    <img src="<?= foto_thumb($b['foto_nama']) ?>" alt="<?= e($b['nama']) ?>" loading="lazy" decoding="async">
                <?php else: 
                    echo e(preg_match('/^./u', $b['kategori'], $m) ? $m[0] : '?'); 
                endif; ?>
            </div>
            <b><?= e($b['nama']) ?></b>
            <span class="mut"><?= e($b['kategori']) ?></span>
            <span class="price"><?= rp($b['harga']) ?> / hari</span>
            <span><?= badge($b['status']) ?></span>
        </a>
    <?php endforeach; ?>
    
    <?php 
    if (!$rows) {
        echo '<p>Tidak ada unit yang cocok. Ubah kata kunci atau kategori.</p>'; 
    }
    ?>
</div>

<?php 
require __DIR__ . '/includes/footer.php'; 
?>