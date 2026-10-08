<?php 
$judul = 'Laporan'; 
require __DIR__ . '/../includes/header.php'; 
wajib_login(['owner', 'staff']);

$t = $pdo->query("SELECT COALESCE(SUM(sewa),0) AS sewa, COALESCE(SUM(denda),0) AS denda, COUNT(*) AS n FROM reservasi WHERE status IN ('Dikonfirmasi','Dipinjam','Selesai')")->fetch();
$inv = $pdo->query('SELECT status, COUNT(*) AS n FROM barang GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$bln = $pdo->query("SELECT TO_CHAR(tgl_mulai,'YYYY-MM') AS bln, SUM(sewa) AS total FROM reservasi WHERE status IN ('Dikonfirmasi','Dipinjam','Selesai') GROUP BY bln ORDER BY bln")->fetchAll();
$mx = max(1, ...array_map(fn($x) => (float)$x['total'], $bln ?: [['total' => 1]]));
$trx = $pdo->query('SELECT r.*, b.nama AS barang, u.nama AS pelanggan FROM reservasi r JOIN barang b ON b.id=r.barang_id JOIN users u ON u.id=r.user_id ORDER BY r.id DESC')->fetchAll(); 
?>

<h1>Laporan manajerial</h1>

<div class="stat">
    <div class="card">
        Pendapatan sewa<br>
        <b><?= rp($t['sewa']) ?></b>
    </div>
    <div class="card">
        Denda keterlambatan<br>
        <b><?= rp($t['denda']) ?></b>
    </div>
    <div class="card">
        Total transaksi<br>
        <b><?= (int)$t['n'] ?></b>
    </div>
    <div class="card">
        Inventaris<br>
        <b><?= (int)($inv['Available'] ?? 0) ?></b> tersedia, <?= (int)($inv['Rented'] ?? 0) ?> disewa, <?= (int)($inv['Maintenance'] ?? 0) ?> maintenance
    </div>
</div>

<div class="card" style="margin-bottom:2rem">
    <h3>Pendapatan per bulan</h3>
    <div class="bars">
        <?php foreach ($bln as $x): ?>
            <div style="height:<?= $x['total'] / $mx * 100 ?>%" title="<?= rp($x['total']) ?>">
                <span><?= e($x['bln']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <p style="margin-top:1.5rem" class="mut">Tinggi batang proporsional terhadap bulan tertinggi.</p>
</div>

<h2>Transaksi rental</h2>

<div class="wrap">
    <table class="tbl">
        <tr>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Unit</th>
            <th>Sewa</th>
            <th>Denda</th>
            <th>Status</th>
        </tr>
        <?php foreach ($trx as $r): ?>
            <tr>
                <td><?= e($r['tgl_mulai']) ?></td>
                <td><?= e($r['pelanggan']) ?></td>
                <td><?= e($r['barang']) ?></td>
                <td><?= rp($r['sewa']) ?></td>
                <td><?= rp($r['denda']) ?></td>
                <td><?= badge($r['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>