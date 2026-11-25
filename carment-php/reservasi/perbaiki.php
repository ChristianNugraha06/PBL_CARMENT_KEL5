<?php /* pelanggan memperbaiki pembayaran yang ditolak (kirim bukti baru) */
$judul = 'Perbaiki pembayaran';
require __DIR__ . '/../includes/header.php';
$u = wajib_login(['customer']);
$s = $pdo->prepare("SELECT r.*, b.nama, EXTRACT(EPOCH FROM (r.batas_bayar - NOW()))::int AS sisa FROM reservasi r JOIN barang b ON b.id=r.barang_id WHERE r.id=? AND r.user_id=? AND r.status='Perlu Perbaikan' AND r.batas_bayar > NOW()");
$s->execute([(int)($_GET['id'] ?? 0), $u['id']]);
$r = $s->fetch();
if (!$r) {
    flash('Reservasi tidak ditemukan atau waktu perbaikan sudah habis.');
    redirect('reservasi/riwayat.php');
}
?>
<h1>Perbaiki pembayaran: <?= e($r['nama']) ?></h1>
<div class="card narrow" style="max-width:560px">
    <p><?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?> &middot; total <b><?= rp($r['sewa'] + $r['deposit']) ?></b></p>
    <p class="err">Alasan penolakan: <?= e($r['alasan_tolak']) ?></p>
    <p>Sisa waktu perbaikan: <b class="hitung err" data-sisa="<?= max(0, (int)$r['sisa']) ?>"></b>. Tanggal Anda tetap terkunci selama waktu ini.</p>
    <div class="info">Transfer (atau transfer kekurangan) ke <b><?= e(REK_BANK) ?> <?= e(REK_NO) ?></b> a.n. <b><?= e(REK_AN) ?></b>, lalu unggah bukti yang benar.</div>
    <form method="post" action="proses_perbaiki.php" enctype="multipart/form-data" data-popup="Kirim bukti pembayaran yang sudah diperbaiki? Staff akan memeriksanya kembali." data-ya="Ya, kirim">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <label for="bk">Bukti transfer baru (jpg/png/pdf, maks 2 MB)</label>
        <input id="bk" name="bukti" type="file" accept="image/*,.pdf" required>
        <label for="ct">Catatan untuk staff (opsional)</label>
        <textarea id="ct" name="catatan" rows="2" placeholder="Contoh: transfer tambahan Rp50.000 untuk kekurangan nominal"></textarea>
        <p><button class="btn">Kirim bukti perbaikan</button> <a class="btn alt" href="riwayat.php">Kembali</a></p>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>