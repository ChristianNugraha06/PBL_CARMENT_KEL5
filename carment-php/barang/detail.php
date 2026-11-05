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

$r = $pdo->prepare("SELECT tgl_mulai, tgl_selesai, status FROM reservasi WHERE barang_id=? AND status IN ('Menunggu Pembayaran', 'Menunggu', 'Dikonfirmasi', 'Dipinjam')");
$r->execute([$id]);
$rs = $r->fetchAll();
?>

<a href="../index.php">&larr; Kembali ke katalog</a>

<div class="two">
    <div>
        <div class="card">
            <?php if (!empty($b['foto_nama'])): ?>
                <img class="foto" src="<?= foto_asli($b['foto_nama']) ?>" style="background-image:url('<?= foto_thumb($b['foto_nama']) ?>')" alt="<?= e($b['nama']) ?>">
            <?php endif; ?>
            
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

        <?php if ($b['status'] !== 'Maintenance'): ?>
            <div class="card" style="margin-top:1rem">
                <h3>Ketersediaan 28 hari ke depan</h3>
                <p class="mut">Hijau = tersedia, merah = sudah dibooking, abu-abu = terkunci sementara (menunggu pembayaran).</p>
                <div class="cal">
                    <?php 
                    for ($i = 0; $i < 28; $i++) {
                        $d = date('Y-m-d', strtotime("+$i day"));
                        $c = '';
                        
                        foreach ($rs as $q) {
                            if ($q['tgl_mulai'] <= $d && $d <= $q['tgl_selesai']) {
                                $c = ($q['status'] === 'Menunggu Pembayaran') ? ($c === 'x' ? 'x' : 'k') : 'x';
                            }
                        }
                        
                        echo '<div class="' . $c . '" title="' . $d . '">' . substr($d, 8) . '</div>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Reservasi unit ini</h3>
        
        <?php if ($b['status'] === 'Maintenance'): ?>
            <p class="err">Unit sedang maintenance dan belum bisa disewa.</p>
        <?php elseif (empty($u)): ?>
            <p>Silakan <a href="../auth/login.php">masuk</a> untuk memesan.</p>
        <?php elseif ($u['role'] !== 'customer'): ?>
            <p class="mut">Hanya akun pelanggan yang dapat memesan.</p>
        <?php else: ?>
            <form method="post" action="../keranjang/proses_tambah.php" id="form-res" data-harga="<?= $b['harga'] ?>" data-deposit="<?= $b['deposit'] ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="barang_id" value="<?= $b['id'] ?>">
                
                <label for="d1">Tanggal mulai</label>
                <input id="d1" name="tgl_mulai" type="date" min="<?= date('Y-m-d') ?>" required>
                
                <label for="d2">Tanggal selesai</label>
                <input id="d2" name="tgl_selesai" type="date" min="<?= date('Y-m-d') ?>" required>
                
                <div id="ringkasan" class="info" style="margin-top:1rem">Pilih tanggal untuk melihat rincian biaya.</div>
                
                <?php if (function_exists('info_rekening')) { echo info_rekening(); } ?>
                
                <p><button class="btn">Masukkan ke keranjang</button></p>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php 
require __DIR__ . '/../includes/footer.php'; 
?> 