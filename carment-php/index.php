<?php
$judul = 'Katalog';

/* staff/owner dialihkan ke halaman utama akunnya */
require_once __DIR__ . '/includes/auth.php';
tamu_atau_customer();
require __DIR__ . '/includes/header.php';
$q = trim($_GET['q'] ?? '');
$k = $_GET['kat'] ?? '';
$s = $pdo->prepare("SELECT * FROM barang WHERE (nama ILIKE ? OR spesifikasi ILIKE ?) AND (?::text='' OR kategori=?::text) ORDER BY nama");
$s->execute(["%$q%", "%$q%", $k, $k]);
$rows = $s->fetchAll();
?>

<?php /* R2 #7 banner + pengenalan toko + jam buka/tutup, di awal halaman */ ?>
<?php
[$bukaSkrg, $teksBuka] = toko_status();
$hariIni = (int)date('N');
?>
<section class="banner"<?= is_file(__DIR__ . '/gambar/banner.jpg') 
? ' style="background-image:linear-gradient
(rgba(16,38,43,.7),rgba(16,38,43,.7)),url(\'' . BASE_URL . 'gambar/banner.jpg\')"' : '' ?>>

    <div class="banner-isi">
        <h2>Sewa kamera &amp; perlengkapan foto di <?= e(TOKO_NAMA) ?></h2>
        <p><?= e(TOKO_DESKRIPSI) ?></p>
        <ul class="poin">
            <li>Unit diinspeksi setiap kali dikembalikan</li>
            <li>Harga jelas: sewa + deposit yang dikembalikan</li>
            <li>Reservasi online, pembayaran lewat transfer</li>
        </ul>
        <p class="syarat"><b>Syarat sewa:</b> KTP asli + deposit sesuai unit.</p>
        <a class="btn" href="#katalog">Lihat katalog</a>
    </div>
    <div class="banner-jam">
        <h3>Jam operasional</h3>
        <p><span class="badge <?= $bukaSkrg ? 'Available' : 'Ditolak' ?>"><?= e($teksBuka) ?></span></p>

        <?php /* jam operasional dari database (bisa diedit staff) */ ?>
        <table class="jam">
            <?php foreach (jam_toko() as $n => $j): ?>
                <tr<?= $n === $hariIni ? ' class="hari-ini"' : '' ?>>
                    <td><?= NAMA_HARI[$n] ?></td>
                    <td><?= $j ? str_replace(':', '.', $j[0]) . ' - ' . str_replace(':', '.', $j[1]) : 'Tutup' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>
<?php ?>

<?php /* anchor untuk tombol Lihat katalog */ ?>
<h1 id="katalog">Katalog kamera &amp; peralatan</h1>
<form class="bar" method="get">
    <input name="q" value="<?= e($q) ?>" placeholder="Cari nama atau spesifikasi..." aria-label="Cari">
    <select name="kat" aria-label="Kategori" onchange="this.form.submit()">
        <option value="">Semua kategori</option>

        <?php /* kategori dari database */ ?>
        <?php foreach (daftar_kategori() as $c): ?>
            <option <?= $k === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn">Cari</button>
</form>
<div class="grid">
    <?php foreach ($rows as $b): ?>

        <?php /* thumbnail + lazy load */ ?>
        <?php /* badge status kembali di bawah harga */ ?>
        <a class="card item" href="barang/detail.php?id=<?= $b['id'] ?>">
            <div class="ph"><?php if ($b['foto_nama']): ?><img src="<?= foto_thumb($b['foto_nama']) ?>" alt="<?= e($b['nama']) ?>" loading="lazy" decoding="async"><?php else: echo e(preg_match('/^./u', $b['kategori'], $m) ? $m[0] : '?'); endif; ?></div>
            <b><?= e($b['nama']) ?></b>
            <span class="mut"><?= e($b['kategori']) ?></span>
            <span class="price"><?= rp($b['harga']) ?> / hari</span>
            <span><?= badge($b['status']) ?></span>
        </a>
        <?php ?>

    <?php endforeach; ?>
    <?php if (!$rows): ?>
        <p>Tidak ada unit yang cocok. Ubah kata kunci atau kategori.</p>
    <?php endif; ?>
</div>

<?php /* feedback & ulasan di bagian bawah halaman katalog */ ?>
<?php
$fbList = $pdo->query('SELECT nama, rating, pesan, created_at FROM feedback ORDER BY id DESC LIMIT 6')->fetchAll();
$fbRingkas = $pdo->query('SELECT COUNT(*) AS n, COALESCE(ROUND(AVG(rating), 1), 0) AS rata FROM feedback')->fetch();
$fbPesan = $_SESSION['fb_msg'] ?? '';
unset($_SESSION['fb_msg']);
$uFb = current_user();
?>
<section class="card feedback" id="feedback">
    <h2>Feedback &amp; ulasan</h2>
    <p class="mut"><?= (int)$fbRingkas['n'] ? '&#9733; ' . e($fbRingkas['rata']) . ' / 5 dari ' . (int)$fbRingkas['n'] . ' ulasan' : 'Jadilah yang pertama memberi ulasan.' ?></p>
    <?php if ($fbPesan): ?>
        <p class="info" role="status"><?= e($fbPesan) ?></p>
    <?php endif; ?>
    <form method="post" action="feedback/proses_kirim.php" data-popup="Kirim feedback ini?" data-ya="Ya, kirim">
        <?= csrf_field() ?>
        <label for="fn">Nama</label>
        <input id="fn" name="nama" maxlength="60" value="<?= e($uFb['nama'] ?? '') ?>" required>
        <fieldset class="bintang" aria-label="Rating 1 sampai 5">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" id="rt<?= $i ?>" name="rating" value="<?= $i ?>" required><label for="rt<?= $i ?>" title="<?= $i ?> bintang">&#9733;</label>
            <?php endfor; ?>
        </fieldset>
        <label for="fp">Pesan</label>
        <textarea id="fp" name="pesan" rows="3" minlength="5" maxlength="500" placeholder="Ceritakan pengalaman Anda menyewa di CARMENT" required></textarea>
        <input type="text" name="situs" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <p><button class="btn">Kirim feedback</button></p>
    </form>
    <?php if ($fbList): ?>
        <div class="ulasan">
            <?php foreach ($fbList as $f): ?>
                <blockquote>
                    <div class="bintang-tampil" aria-label="<?= (int)$f['rating'] ?> dari 5"><?= str_repeat('&#9733;', (int)$f['rating']) . str_repeat('&#9734;', 5 - (int)$f['rating']) ?></div>
                    <p><?= nl2br(e($f['pesan'])) ?></p>
                    <cite><?= e($f['nama']) ?></cite>
                </blockquote>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php ?>

<?php /* alamat + peta dipindah ke bagian bawah halaman */ ?>
<?php /* kartu lokasi kembali ke gaya sebelumnya */ ?>
<?php /* tambah tautan Chat WhatsApp pada kartu lokasi */ ?>
<section class="card lokasi">
    <h2>Lokasi toko</h2>
    <p><?= e(TOKO_ALAMAT) ?> &middot; <a href="https://www.google.com/maps?q=<?= TOKO_LAT ?>,<?= TOKO_LNG ?>" target="_blank" rel="noopener">Buka di Google Maps</a> &middot; <a href="<?= e(wa_link('Halo admin CARMENT, saya ingin bertanya.')) ?>" target="_blank" rel="noopener">Chat WhatsApp</a></p>
    <iframe class="peta" title="Lokasi toko" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox=<?= TOKO_LNG - 0.006 ?>,<?= TOKO_LAT - 0.004 ?>,<?= TOKO_LNG + 0.006 ?>,<?= TOKO_LAT + 0.004 ?>&amp;layer=mapnik&amp;marker=<?= TOKO_LAT ?>,<?= TOKO_LNG ?>"></iframe>
</section>
<?php ?>

<?php require __DIR__ . '/includes/footer.php'; ?>