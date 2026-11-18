<?php
$judul = 'Riwayat sewa';
require __DIR__ . '/../includes/header.php';
$u = wajib_login(['customer']);

// hitung sisa waktu bayar
$s = $pdo->prepare('SELECT r.*, b.nama, EXTRACT(EPOCH FROM (r.batas_bayar - NOW()))::int AS sisa FROM reservasi r JOIN barang b ON b.id=r.barang_id WHERE r.user_id=? ORDER BY r.id DESC');
$s->execute([$u['id']]);
$rows = $s->fetchAll();
?>

<h1>Riwayat penyewaan</h1>

<?php if (!$rows): ?>
  <p>Belum ada reservasi. <a href="../index.php">Lihat katalog</a>.</p>
<?php else: ?>
  <?php /* kolom aksi */ ?>
  <div class="wrap">
    <table class="tbl">
      <tr>
        <th>Unit</th>
        <th>Tanggal</th>
        <th>Sewa</th>
        <th>Deposit</th>
        <th>Denda</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
      
      <?php 
      /* tombol bayar + hitung mundur */ 
      /* tombol lihat bukti + alasan penolakan */ 
      foreach ($rows as $r): 
      ?>
        <tr>
          <td><?= e($r['nama']) ?></td>
          <td><?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?></td>
          <td><?= rp($r['sewa']) ?></td>
          <td><?= rp($r['deposit']) ?></td>
          <td><?= rp($r['denda']) ?></td>
          <td><?= badge($r['status']) ?></td>
          <td>
            <?php if ($r['status'] === 'Menunggu Pembayaran'): ?>
              <a class="btn sm" href="bayar.php?id=<?= $r['id'] ?>">Bayar</a> 
              <span class="hitung err" data-sisa="<?= max(0, (int)$r['sisa']) ?>"></span>
            <?php endif; ?>
            
            <?php if ($r['bukti']): ?>
              <a class="btn sm alt" href="bukti.php?id=<?= $r['id'] ?>" target="_blank" rel="noopener">Lihat bukti</a>
            <?php endif; ?>
            
            <?php if ($r['status'] === 'Ditolak' && $r['alasan_tolak']): ?>
              <div class="err">Ditolak: <?= e($r['alasan_tolak']) ?></div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php
require __DIR__ . '/../includes/footer.php';
?>