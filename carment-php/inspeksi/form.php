<?php 
$judul = 'Inspeksi'; 
require __DIR__ . '/../includes/header.php'; 
wajib_login(['staff']);

$s = $pdo->prepare("SELECT r.*, b.nama, b.harga, u.nama AS pelanggan FROM reservasi r JOIN barang b ON b.id=r.barang_id JOIN users u ON u.id=r.user_id WHERE r.id=? AND r.status='Dipinjam'");
$s->execute([(int)($_GET['id'] ?? 0)]); 
$r = $s->fetch(); 

if (!$r) {
    redirect('reservasi/list.php');
}

$telat = max(0, hari($r['tgl_selesai'], date('Y-m-d'))); 
?>

<h1>Inspeksi pengembalian: <?= e($r['nama']) ?></h1>

<form method="post" action="proses.php" class="card narrow" style="max-width:560px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $r['id'] ?>">
    
    <p>
        Pelanggan: <?= e($r['pelanggan']) ?>. 
        <?= $telat ? '<span class="err">Terlambat ' . $telat . ' hari, denda ' . rp($telat * $r['harga'] * DENDA_PERSEN) . '.</span>' : 'Dikembalikan tepat waktu.' ?>
    </p>

    <?php foreach (['Body & layar', 'Lensa / optik', 'Baterai & charger', 'Aksesori lengkap (strap, tutup, tas)'] as $i => $c): ?>
        <label class="cek">
            <input type="checkbox" name="cek[]" value="<?= $i ?>"> <?= e($c) ?> baik/lengkap
        </label>
    <?php endforeach; ?>

    <label for="kd">Kondisi akhir unit</label>
    <select id="kd" name="kondisi">
        <option>Baik</option>
        <option>Rusak</option>
    </select>

    <label for="ct">Catatan kondisi</label>
    <textarea id="ct" name="catatan" rows="2" placeholder="Contoh: baret di hood lensa"></textarea>

    <p>
        <button class="btn">Simpan inspeksi</button> 
        <a class="btn alt" href="../reservasi/list.php">Batal</a>
    </p>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>