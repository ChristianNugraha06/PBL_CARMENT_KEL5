<?php 
$judul = 'Riwayat sewa'; 
require __DIR__ . '/../includes/header.php'; 
$u = wajib_login(['customer']);

// >>> REVISI #18 [UBAH] hitung sisa waktu bayar (baris di bawah ini)
$s = $pdo->prepare('SELECT r.*, b.nama, EXTRACT(EPOCH FROM (r.batas_bayar - NOW()))::int AS sisa FROM reservasi r JOIN barang b ON b.id=r.barang_id WHERE r.user_id=? ORDER BY r.id DESC'); 
$s->execute([$u['id']]); 
$rows = $s->fetchAll(); 
?>

<h1>Riwayat penyewaan</h1>

<?php if (!$rows): ?>
    <p>Belum ada reservasi. <a href="../index.php">Lihat katalog</a>.</p>
<?php else: ?>
    <?php /* >>> REVISI #17 #18 [UBAH] kolom aksi (baris di bawah ini) */ ?>
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
            
            <?php /* >>> REVISI #17 #18 [UBAH] tombol bayar + hitung mundur (baris di bawah ini) */ ?>
            <?php foreach ($rows as $r): ?>
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
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<?php 
require __DIR__ . '/../includes/footer.php'; 
?>