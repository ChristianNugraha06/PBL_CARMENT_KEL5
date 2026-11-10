<?php 
$judul = 'Laporan'; 
require __DIR__ . '/../includes/header.php'; 
wajib_login(['owner', 'staff']);

// >>> REVISI #10 [BARU] filter rentang tanggal (blok sampai tanda AKHIR)
$dari = tgl_valid($_GET['dari'] ?? '') ? $_GET['dari'] : ''; 
$sampai = tgl_valid($_GET['sampai'] ?? '') ? $_GET['sampai'] : '';
$rng = [$dari ?: '1900-01-01', $sampai ?: '2999-12-31'];

$q1 = $pdo->prepare("SELECT COALESCE(SUM(sewa),0) AS sewa, COALESCE(SUM(denda),0) AS denda, COUNT(*) AS n FROM reservasi WHERE status IN ('Dikonfirmasi', 'Dipinjam', 'Selesai') AND tgl_mulai BETWEEN ? AND ?"); 
$q1->execute($rng); 
$t = $q1->fetch();
// <<< AKHIR REVISI

$inv = $pdo->query('SELECT status, COUNT(*) AS n FROM barang GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

// >>> REVISI #10 [UBAH] grafik mengikuti rentang (baris di bawah ini)
$q2 = $pdo->prepare("SELECT TO_CHAR(tgl_mulai,'YYYY-MM') AS bln, SUM(sewa) AS total FROM reservasi WHERE status IN ('Dikonfirmasi', 'Dipinjam', 'Selesai') AND tgl_mulai BETWEEN ? AND ? GROUP BY bln ORDER BY bln"); 
$q2->execute($rng); 
$bln = $q2->fetchAll();

$mx = max(1, ...array_map(fn($x) => (float)$x['total'], $bln ?: [['total' => 1]]));

// >>> REVISI #10 [UBAH] tabel transaksi mengikuti rentang (baris di bawah ini)
$q3 = $pdo->prepare('SELECT r.*, b.nama AS barang, u.nama AS pelanggan FROM reservasi r JOIN barang b ON b.id=r.barang_id JOIN users u ON u.id=r.user_id WHERE r.tgl_mulai BETWEEN ? AND ? ORDER BY r.id DESC'); 
$q3->execute($rng); 
$trx = $q3->fetchAll(); 
?>

<h1>Laporan manajerial</h1>

<?php /* >>> REVISI #10 #11 [BARU] form rentang tanggal + tombol ekspor (blok sampai tanda AKHIR) */ ?>
<form method="get" class="bar">
    <label class="cek">Dari <input type="date" name="dari" value="<?= e($dari) ?>"></label>
    <label class="cek">Sampai <input type="date" name="sampai" value="<?= e($sampai) ?>"></label>
    <button class="btn">Terapkan</button>
    <a class="btn alt" href="index.php">Reset</a>
    <a class="btn alt" href="ekspor.php?dari=<?= e($dari) ?>&amp;sampai=<?= e($sampai) ?>">Ekspor Excel</a>
</form>
<?php /* <<< AKHIR REVISI */ ?>

<div class="stat">
    <div class="card">Pendapatan sewa<br><b><?= rp($t['sewa']) ?></b></div>
    <div class="card">Denda keterlambatan<br><b><?= rp($t['denda']) ?></b></div>
    <div class="card">Total transaksi<br><b><?= (int)$t['n'] ?></b></div>
    <div class="card">Inventaris<br><b><?= (int)($inv['Available'] ?? 0) ?></b> tersedia, <?= (int)($inv['Rented'] ?? 0) ?> disewa, <?= (int)($inv['Maintenance'] ?? 0) ?> maintenance</div>
</div>

<div class="card" style="margin-bottom:2rem">
    <h3>Pendapatan per bulan</h3>
    <div class="bars">
        <?php foreach ($bln as $x): ?>
            <div style="height:<?= ($x['total'] / $mx) * 100 ?>%" title="<?= rp($x['total']) ?>">
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

<?php 
require __DIR__ . '/../includes/footer.php'; 
?>