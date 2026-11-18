<?php
$judul = 'Katalog';
require __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$k = $_GET['kat'] ?? '';

$s = $pdo->prepare("SELECT * FROM barang WHERE (nama ILIKE ? OR spesifikasi ILIKE ?) AND (?::text='' OR kategori=?::text) ORDER BY nama");
$s->execute(["%$q%", "%$q%", $k, $k]);
$rows = $s->fetchAll();
?>

<?php /* banner + pengenalan toko + jam buka/tutup, di awal halaman */ ?>
<?php 
[$bukaSkrg, $teksBuka] = toko_status(); 
$hariIni = (int)date('N'); 
?>

<section class="banner"<?= is_file(__DIR__ . '/gambar/banner.jpg') ? ' style="background-image:linear-gradient(rgba(16,38,43,.7),rgba(16,38,43,.7)),url(\'' . BASE_URL . 'gambar/banner.jpg\')"' : '' ?>>
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
  <?php endforeach; 
  if (!$rows) {
    echo '<p>Tidak ada unit yang cocok. Ubah kata kunci atau kategori.</p>';
  } ?>
</div>

<?php /* alamat + peta dipindah ke bagian bawah halaman */ ?>
<section class="card lokasi">
  <h2>Lokasi toko</h2>
  <p><?= e(TOKO_ALAMAT) ?> &middot; <a href="https://www.google.com/maps?q=<?= TOKO_LAT ?>,<?= TOKO_LNG ?>" target="_blank" rel="noopener">Buka di Google Maps</a></p>
  
  <iframe class="peta" title="Lokasi toko" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox=<?= TOKO_LNG - 0.006 ?>,<?= TOKO_LAT - 0.004 ?>,<?= TOKO_LNG + 0.006 ?>,<?= TOKO_LAT + 0.004 ?>&amp;layer=mapnik&amp;marker=<?= TOKO_LAT ?>,<?= TOKO_LNG ?>"></iframe>
</section>
<?php ?>

<?php
require __DIR__ . '/includes/footer.php';
?>