<?php /* pelanggan meminta pengembalian dana */
$judul = 'Pengembalian dana';
require __DIR__ . '/../includes/header.php';
$u = wajib_login(['customer']);
$s = $pdo->prepare("SELECT r.*, b.nama FROM reservasi r JOIN barang b ON b.id=r.barang_id WHERE r.id=? AND r.user_id=? AND (r.status='Perlu Perbaikan' OR (r.status='Menunggu Refund' AND r.refund_rekening IS NULL))");
$s->execute([(int)($_GET['id'] ?? 0), $u['id']]);
$r = $s->fetch();
if (!$r) {
    flash('Reservasi tidak ditemukan atau tidak bisa diajukan pengembalian dana.');
    redirect('reservasi/riwayat.php');
}
?>

<h1>Pengembalian dana: <?= e($r['nama']) ?></h1>
<div class="card narrow" style="max-width:560px">
    <p>Dana yang dikembalikan: <b class="price"><?= rp($r['sewa'] + $r['deposit']) ?></b>. Reservasi dibatalkan dan tanggalnya dibuka kembali untuk pelanggan lain.</p>
    <form method="post" action="proses_refund.php" data-popup="Ajukan pengembalian dana? Reservasi dibatalkan dan tanggalnya dibuka untuk pelanggan lain." data-ya="Ya, ajukan refund">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <label for="bn">Bank / e-wallet tujuan</label>
        <input id="bn" name="bank" maxlength="40" required placeholder="Contoh: BCA">
        <label for="nr">Nomor rekening</label>
        <input id="nr" name="rekening" inputmode="numeric" pattern="[0-9]{5,30}" required placeholder="Angka saja">
        <label for="an">Atas nama</label>
        <input id="an" name="nama" maxlength="80" required>
        <p><button class="btn bad">Ajukan pengembalian dana</button> <a class="btn alt" href="riwayat.php">Kembali</a></p>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>