<?php 
$judul = 'Detail unit'; 
require __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM barang WHERE id=?'); 
$s->execute([$id]); 
$b = $s->fetch();

if (!$b) { 
    echo '<p>Unit tidak ditemukan.</p>'; 
    require __DIR__ . '/../includes/footer.php'; 
    exit; 
}

$r = $pdo->prepare("SELECT tgl_mulai,tgl_selesai FROM reservasi WHERE barang_id=? AND status IN ('Menunggu','Dikonfirmasi','Dipinjam')");
$r->execute([$id]); 
$rs = $r->fetchAll(); 
?>

<a href="../index.php">&larr; Kembali ke katalog</a>

<div class="two">
    <div>
        <div class="card">
            <h2><?= e($b['nama']) ?></h2>
            <?= badge($b['status']) ?>
            
            <h3>Spesifikasi</h3>
            <p><?= e($b['spesifikasi']) ?></p>
            
            <h3>Kondisi fisik terkini</h3>
            <p><?= e($b['kondisi']) ?></p>
            
            <p class="price">
                <?= rp($b['harga']) ?> / hari &middot; Deposit <?= rp($b['deposit']) ?>
            </p>
        </div>

        <div class="card" style="margin-top:1rem">
            <h3>Ketersediaan 28 hari ke depan</h3>
            <p class="mut">Hijau tersedia, merah sudah dipesan.</p>
            <div class="cal">
                <?php 
                for ($i = 0; $i < 28; $i++) { 
                    $d = date('Y-m-d', strtotime("+$i day")); 
                    $x = false;
                    foreach ($rs as $q) {
                        if ($q['tgl_mulai'] <= $d && $d <= $q['tgl_selesai']) {
                            $x = true;
                        }
                    }
                    echo '<div class="' . ($x ? 'x' : '') . '" title="' . $d . '">' . substr($d, 8) . '</div>'; 
                } 
                ?>
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Reservasi unit ini</h3>
        <?php if ($b['status'] === 'Maintenance'): ?>
            <p class="err">Unit sedang maintenance dan belum bisa disewa.</p>
        <?php elseif (!$u): ?>
            <p>Silakan <a href="../auth/login.php">masuk</a> untuk memesan.</p>
        <?php elseif ($u['role'] !== 'customer'): ?>
            <p class="mut">Hanya akun pelanggan yang dapat memesan.</p>
        <?php else: ?>
            <form method="post" action="../reservasi/proses_tambah.php" enctype="multipart/form-data" id="form-res" data-harga="<?= $b['harga'] ?>" data-deposit="<?= $b['deposit'] ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="barang_id" value="<?= $b['id'] ?>">
                
                <label for="d1">Tanggal mulai</label>
                <input id="d1" name="tgl_mulai" type="date" min="<?= date('Y-m-d') ?>" required>
                
                <label for="d2">Tanggal selesai</label>
                <input id="d2" name="tgl_selesai" type="date" min="<?= date('Y-m-d') ?>" required>
                
                <div id="ringkasan" class="info" style="margin-top:1rem">
                    Pilih tanggal untuk melihat rincian biaya.
                </div>
                
                <label for="bk">Bukti transfer (jpg/png/pdf, maks 2 MB)</label>
                <input id="bk" name="bukti" type="file" accept="image/*,.pdf" required>
                
                <p>
                    <button class="btn">Ajukan reservasi</button>
                </p>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>