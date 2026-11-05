<?php 
/* >>> REVISI [FILE BARU] #3 #17 #18 pelunasan (seluruh file ini baru) */ 
$judul = 'Pelunasan'; 
require __DIR__ . '/../includes/header.php'; 
$u = wajib_login(['customer']);

$s = $pdo->prepare("SELECT r.*, b.nama, EXTRACT(EPOCH FROM (r.batas_bayar - NOW()))::int AS sisa FROM reservasi r JOIN barang b ON b.id=r.barang_id WHERE r.id=? AND r.user_id=? AND r.status='Menunggu Pembayaran' AND r.batas_bayar > NOW()");
$s->execute([(int)($_GET['id'] ?? 0), $u['id']]); 
$r = $s->fetch(); 

if (!$r) { 
    flash('Reservasi tidak ditemukan atau waktu pembayaran habis (dibatalkan otomatis).'); 
    redirect('reservasi/riwayat.php'); 
} 
?>

<h1>Pelunasan: <?= e($r['nama']) ?></h1>

<div class="card narrow" style="max-width:560px">
    <p>
        <?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?><br>
        Sewa <?= rp($r['sewa']) ?> + deposit <?= rp($r['deposit']) ?>
    </p>
    
    <p>Total transfer: <b class="price"><?= rp($r['sewa'] + $r['deposit']) ?></b></p>
    
    <p>Sisa waktu: <b class="hitung err" data-sisa="<?= max(0, (int)$r['sisa']) ?>"></b></p>
    
    <?= info_rekening() ?>
    
    <form method="post" action="proses_bayar.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        
        <label for="bk">Bukti transfer (jpg/png/pdf, maks 2 MB)</label>
        <input id="bk" name="bukti" type="file" accept="image/*,.pdf" required>
        
        <p>
            <button class="btn">Kirim bukti pelunasan</button>
        </p>
    </form>
</div>

<?php 
require __DIR__ . '/../includes/footer.php'; 
?>