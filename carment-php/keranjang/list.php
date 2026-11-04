<?php 
/* >>> REVISI [FILE BARU] #17 (seluruh file ini baru) */ 
$judul = 'Keranjang'; 
require __DIR__ . '/../includes/header.php'; 
$u = wajib_login(['customer']);

$s = $pdo->prepare('SELECT k.*, b.nama, b.harga, b.deposit, b.foto_nama FROM keranjang k JOIN barang b ON b.id=k.barang_id WHERE k.user_id=? ORDER BY k.id');
$s->execute([$u['id']]); 
$rows = $s->fetchAll(); 
$total = 0; 
?>

<h1>Keranjang</h1>

<?php if (!$rows): ?>
    <p>Keranjang kosong. <a href="../index.php">Pilih unit di katalog</a>.</p>
<?php else: ?>
    <div class="wrap">
        <table class="tbl">
            <tr>
                <th>Unit</th>
                <th>Tanggal</th>
                <th>Sewa</th>
                <th>Deposit</th>
                <th>Aksi</th>
            </tr>
            
            <?php foreach ($rows as $k): 
                $sewa = (hari($k['tgl_mulai'], $k['tgl_selesai']) + 1) * $k['harga']; 
                $total += $sewa + $k['deposit']; 
            ?>
                <tr>
                    <td>
                        <?php if ($k['foto_nama']): ?>
                            <img src="<?= foto_thumb($k['foto_nama']) ?>" alt="" width="48" style="vertical-align:middle;border-radius:6px" loading="lazy"> 
                        <?php endif; ?>
                        <?= e($k['nama']) ?>
                    </td>
                    <td><?= e($k['tgl_mulai']) ?> s/d <?= e($k['tgl_selesai']) ?></td>
                    <td><?= rp($sewa) ?></td>
                    <td><?= rp($k['deposit']) ?></td>
                    <td>
                        <form method="post" action="proses_hapus.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $k['id'] ?>">
                            <button class="btn sm bad">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <p style="margin-top:1rem">
        <b>Total pelunasan: <?= rp($total) ?></b> 
        <span class="mut">(deposit dikembalikan setelah unit kembali dalam kondisi baik)</span>
    </p>

    <?= info_rekening() ?>

    <form method="post" action="proses_ajukan.php">
        <?= csrf_field() ?>
        <button class="btn">Ajukan reservasi</button>
    </form>
<?php endif; ?>

<?php 
require __DIR__ . '/../includes/footer.php'; 
?>