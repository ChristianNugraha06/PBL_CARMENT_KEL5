<?php 
$judul = 'Riwayat sewa'; 
require __DIR__ . '/../includes/header.php'; 
$u = wajib_login(['customer']);

$s = $pdo->prepare('SELECT r.*, b.nama FROM reservasi r JOIN barang b ON b.id=r.barang_id WHERE r.user_id=? ORDER BY r.id DESC'); 
$s->execute([$u['id']]); 
$rows = $s->fetchAll(); 
?>

<h1>Riwayat penyewaan</h1>

<?php if (!$rows): ?>
    <p>Belum ada reservasi. <a href="../index.php">Lihat katalog</a>.</p>
<?php else: ?>
    <div class="wrap">
        <table class="tbl">
            <tr>
                <th>Unit</th>
                <th>Tanggal</th>
                <th>Sewa</th>
                <th>Deposit</th>
                <th>Denda</th>
                <th>Status</th>
            </tr>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nama']) ?></td>
                    <td><?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?></td>
                    <td><?= rp($r['sewa']) ?></td>
                    <td><?= rp($r['deposit']) ?></td>
                    <td><?= rp($r['denda']) ?></td>
                    <td><?= badge($r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>