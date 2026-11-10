<?php 
/* >>> REVISI [FILE BARU] #11 ekspor Excel (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['owner', 'staff']);

$dari = tgl_valid($_GET['dari'] ?? '') ? $_GET['dari'] : '1900-01-01'; 
$sampai = tgl_valid($_GET['sampai'] ?? '') ? $_GET['sampai'] : '2999-12-31';

$s = $pdo->prepare('SELECT r.*, b.nama AS barang, u.nama AS pelanggan FROM reservasi r JOIN barang b ON b.id=r.barang_id JOIN users u ON u.id=r.user_id WHERE r.tgl_mulai BETWEEN ? AND ? ORDER BY r.tgl_mulai');
$s->execute([$dari, $sampai]); 
$rows = $s->fetchAll();

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="transaksi_carment_' . date('Ymd') . '.xls"'); 
?>
<meta charset="utf-8">
<table border="1">
    <tr>
        <th>ID</th>
        <th>Tgl Mulai</th>
        <th>Tgl Selesai</th>
        <th>Pelanggan</th>
        <th>Unit</th>
        <th>Sewa</th>
        <th>Deposit</th>
        <th>Denda</th>
        <th>Status</th>
    </tr>
    
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= e($r['tgl_mulai']) ?></td>
            <td><?= e($r['tgl_selesai']) ?></td>
            <td><?= e($r['pelanggan']) ?></td>
            <td><?= e($r['barang']) ?></td>
            <td><?= (int)$r['sewa'] ?></td>
            <td><?= (int)$r['deposit'] ?></td>
            <td><?= (int)$r['denda'] ?></td>
            <td><?= e($r['status']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>