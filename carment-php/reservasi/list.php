<?php
$judul = 'Reservasi';
require __DIR__ . '/../includes/header.php';
wajib_login(['staff']);

$rows = $pdo->query("SELECT r.*, b.nama AS barang, u.nama AS pelanggan FROM reservasi r JOIN barang b ON b.id=r.barang_id JOIN users u ON u.id=r.user_id WHERE r.status IN ('Menunggu', 'Dikonfirmasi', 'Dipinjam') ORDER BY r.tgl_mulai")->fetchAll();

function aksi_btn($id, $aksi, $label, $cls = '') {
  return '<form method="post" action="proses_status.php" style="display:inline">' . csrf_field() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="aksi" value="' . $aksi . '"><button class="btn sm ' . $cls . '">' . $label . '</button></form> ';
}
?>

<h1>Reservasi aktif &amp; serah-terima</h1>

<div class="wrap">
  <table class="tbl">
    <tr>
      <th>Pelanggan</th>
      <th>Unit</th>
      <th>Tanggal</th>
      <th>Bukti</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['pelanggan']) ?></td>
        <td><?= e($r['barang']) ?></td>
        <td><?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?></td>
        
        <?php /* bukti lewat halaman terproteksi */ ?>
        <td>
          <a href="<?= BASE_URL ?>reservasi/bukti.php?id=<?= $r['id'] ?>" target="_blank" rel="noopener">Lihat</a>
        </td>
        
        <td><?= badge($r['status']) ?></td>
        
        <td>
          <?php 
          /* tombol Periksa pembayaran (menggantikan Konfirmasi/Tolak langsung) */ 
          if ($r['status'] === 'Menunggu') {
            echo '<button type="button" class="btn sm" data-verif data-id="' . $r['id'] . '" data-pelanggan="' . e($r['pelanggan']) . '" data-unit="' . e($r['barang']) . '" data-tgl="' . e($r['tgl_mulai']) . ' s/d ' . e($r['tgl_selesai']) . '" data-total="' . e(rp($r['sewa'] + $r['deposit'])) . '" data-bukti="' . BASE_URL . 'reservasi/bukti.php?id=' . $r['id'] . '" data-pdf="' . (substr($r['bukti'], -4) === '.pdf' ? '1' : '0') . '">Periksa pembayaran</button>';
          }
          if ($r['status'] === 'Dikonfirmasi') {
            echo aksi_btn($r['id'], 'serahkan', 'Serahkan barang');
          }
          if ($r['status'] === 'Dipinjam') {
            echo '<a class="btn sm" href="../inspeksi/form.php?id=' . $r['id'] . '">Inspeksi pengembalian</a>';
          } 
          ?>
        </td>
      </tr>
    <?php endforeach; 
    if (!$rows) {
      echo '<tr><td colspan="6">Tidak ada reservasi aktif.</td></tr>';
    } ?>
  </table>
</div>

<?php /* popup verifikasi: bukti + Terima/Tolak + konfirmasi tambahan */ ?>
<dialog id="dlg-verif" aria-labelledby="v-judul">
  <div class="dlg">
    <h3 id="v-judul">Verifikasi pembayaran</h3>
    <p id="v-info" class="mut"></p>
    
    <div id="v-bukti" class="v-bukti"></div>
    
    <div id="v-aksi" class="dlg-aksi">
      <button type="button" class="btn" id="v-terima">Terima</button>
      <button type="button" class="btn bad" id="v-tolak">Tolak</button>
      <button type="button" class="btn alt" data-tutup>Tutup</button>
    </div>
    
    <form id="v-form-terima" method="post" action="proses_status.php" hidden>
      <?= csrf_field() ?>
      <input type="hidden" name="id" class="v-id">
      <input type="hidden" name="aksi" value="konfirmasi">
      <p><b>Konfirmasi:</b> terima pembayaran ini? Reservasi menjadi Dikonfirmasi dan pelanggan dapat mengambil unit.</p>
      <div class="dlg-aksi">
        <button class="btn">Ya, terima</button>
        <button type="button" class="btn alt" data-kembali>Batal</button>
      </div>
    </form>
    
    <form id="v-form-tolak" method="post" action="proses_status.php" hidden>
      <?= csrf_field() ?>
      <input type="hidden" name="id" class="v-id">
      <input type="hidden" name="aksi" value="tolak">
      <label for="v-alasan">Alasan penolakan (wajib, akan dilihat pelanggan)</label>
      <textarea id="v-alasan" name="alasan" rows="3" minlength="5" required></textarea>
      <p><b>Konfirmasi:</b> yakin menolak pembayaran ini? Tanggal akan dibuka kembali untuk pelanggan lain.</p>
      <div class="dlg-aksi">
        <button class="btn bad">Ya, tolak</button>
        <button type="button" class="btn alt" data-kembali>Batal</button>
      </div>
    </form>
  </div>
</dialog>
<?php ?>

<?php
require __DIR__ . '/../includes/footer.php';
?>