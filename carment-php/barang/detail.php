<?php
$judul = 'Detail unit'; /* staff/owner dialihkan ke halaman utama akunnya */
require_once __DIR__ . '/../includes/auth.php';
tamu_atau_customer();
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
// mengikuti status reservasi (warna kalender) 
$r = $pdo->prepare("SELECT tgl_mulai,tgl_selesai,status FROM reservasi WHERE barang_id=? AND status IN ('Menunggu Pembayaran','Menunggu','Perlu Perbaikan','Dikonfirmasi','Dipinjam')");
$r->execute([$id]);
$rs = $r->fetchAll();
?>
<a href="../index.php">&larr; Kembali ke katalog</a>
<div class="two">
    <div>
        <?php /* foto asli, thumbnail sebagai latar  */ ?>
        <div class="card">
            <?php if ($b['foto_nama']): ?>
                <img class="foto" src="<?= foto_asli($b['foto_nama']) ?>" style="background-image:url('<?= foto_thumb($b['foto_nama']) ?>')" alt="<?= e($b['nama']) ?>">
            <?php endif; ?>
            <h2><?= e($b['nama']) ?></h2>
            <?= badge($b['status']) ?>
            <h3>Spesifikasi</h3>
            <p><?= e($b['spesifikasi']) ?></p>
            <h3>Kondisi fisik terkini</h3>
            <p><?= e($b['kondisi']) ?></p>
            <p class="price"><?= rp($b['harga']) ?> / hari &middot; Deposit <?= rp($b['deposit']) ?></p>
        </div>
        <?php /* kalender bulanan lengkap + pilih bulan/tahun + unit Rented/terlambat merah */ ?>
        <?php if ($b['status'] !== 'Maintenance'):
            $hariIni = date('Y-m-d');
            $thn0 = (int)date('Y');
            $bln = (int)($_GET['bulan'] ?? date('n'));
            $thn = (int)($_GET['tahun'] ?? $thn0);
            if ($bln < 1 || $bln > 12) $bln = (int)date('n');
            if ($thn < $thn0 || $thn > $thn0 + 3) $thn = $thn0;
            $jmlHari = (int)date('t', mktime(0, 0, 0, $bln, 1, $thn));
            $offset = (int)date('N', mktime(0, 0, 0, $bln, 1, $thn)) - 1; // kolom Senin = 0
            $prev = $bln === 1 ? [12, $thn - 1] : [$bln - 1, $thn];
            $next = $bln === 12 ? [1, $thn + 1] : [$bln + 1, $thn];
            $bisaPrev = $thn > $thn0 || $bln > 1;
            $bisaNext = $thn < $thn0 + 3 || $bln < 12;
            $terlambat = false;
            foreach ($rs as $q) if ($q['status'] === 'Dipinjam' && $q['tgl_selesai'] < $hariIni) $terlambat = true;
        ?>
        <div class="card" style="margin-top:1rem">
            <h3>Ketersediaan unit</h3>
            <?php if ($b['status'] === 'Rented'): ?>
                <p class="info">Unit sedang disewa<?= $terlambat ? ' dan belum dikembalikan (melewati jatuh tempo)' : '' ?>. Tanggal hari ini ditandai merah sampai unit dikembalikan.</p>
            <?php endif; ?>
            <form method="get" class="kal-nav">
                <input type="hidden" name="id" value="<?= $id ?>">
                <?php if ($bisaPrev): ?>
                    <a class="btn-min" href="?id=<?= $id ?>&amp;bulan=<?= $prev[0] ?>&amp;tahun=<?= $prev[1] ?>" aria-label="Bulan sebelumnya">&lsaquo;</a>
                <?php else: ?>
                    <span class="btn-min off">&lsaquo;</span>
                <?php endif; ?>
                <select name="bulan" aria-label="Bulan" onchange="this.form.submit()"><?php foreach (NAMA_BULAN as $n => $nm): ?><option value="<?= $n ?>" <?= $n === $bln ? 'selected' : '' ?>><?= $nm ?></option><?php endforeach; ?></select>
                <select name="tahun" aria-label="Tahun" onchange="this.form.submit()"><?php for ($y = $thn0; $y <= $thn0 + 3; $y++): ?><option <?= $y === $thn ? 'selected' : '' ?>><?= $y ?></option><?php endfor; ?></select>
                <?php if ($bisaNext): ?>
                    <a class="btn-min" href="?id=<?= $id ?>&amp;bulan=<?= $next[0] ?>&amp;tahun=<?= $next[1] ?>" aria-label="Bulan berikutnya">&rsaquo;</a>
                <?php else: ?>
                    <span class="btn-min off">&rsaquo;</span>
                <?php endif; ?>
                <noscript><button class="btn-min">Tampilkan</button></noscript>
            </form>
            <div class="cal cal-bulan"><?php for ($i = 1; $i <= 7; $i++) echo '<b>' . substr(NAMA_HARI[$i], 0, 3) . '</b>';
            for ($i = 0; $i < $offset; $i++) echo '<span></span>';
            for ($t = 1; $t <= $jmlHari; $t++) {
                $d = sprintf('%04d-%02d-%02d', $thn, $bln, $t);
                $c = '';
                $ket = 'Tersedia';
                if ($d < $hariIni) {
                    $c = 'l';
                    $ket = 'Sudah lewat';
                } else {
                    foreach ($rs as $q) {
                        // unit yang belum dikembalikan setelah jatuh tempo tetap dianggap terpakai sampai hari ini
                        $akhir = ($q['status'] === 'Dipinjam' && $q['tgl_selesai'] < $hariIni) ? $hariIni : $q['tgl_selesai'];
                        if ($q['tgl_mulai'] <= $d && $d <= $akhir) {
                            // tanggal abu-abu selama menunggu pembayaran/verifikasi/perbaikan; merah hanya setelah dikonfirmasi staff
                            if (in_array($q['status'], ['Menunggu Pembayaran', 'Menunggu', 'Perlu Perbaikan'], true)) {
                                if ($c !== 'x') {
                                    $c = 'k';
                                    $ket = 'Terkunci sementara (belum dikonfirmasi staff)';
                                }
                            } else {
                                $c = 'x';
                                $ket = 'Sudah dibooking / sedang disewa';
                            }
                        }
                    }
                    if ($b['status'] === 'Rented' && $d === $hariIni) {
                        $c = 'x';
                        $ket = 'Unit sedang disewa';
                    }
                }
                echo '<div class="' . trim($c . ($d === $hariIni ? ' h' : '')) . '" title="' . $t . ' ' . NAMA_BULAN[$bln] . ' ' . $thn . ': ' . $ket . '">' . $t . '</div>';
            } ?></div>
            <ul class="legend">
                <li><i class="sw"></i>Tersedia</li>
                <li><i class="sw x"></i>Dibooking / disewa</li>
                <li><i class="sw k"></i>Terkunci (menunggu pembayaran / konfirmasi staff)</li>
                <li><i class="sw l"></i>Sudah lewat</li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <?php /* <<< AKHIR REVISI */ ?>
    <div class="card">
        <h3>Reservasi unit ini</h3>
        <?php if ($b['status'] === 'Maintenance'): ?>
            <p class="err">Unit sedang maintenance dan belum bisa disewa.</p>
        <?php elseif (!$u): ?>
            <p>Silakan <a href="../auth/login.php">masuk</a> untuk memesan.</p>
        <?php elseif ($u['role'] !== 'customer'): ?>
            <p class="mut">Hanya akun pelanggan yang dapat memesan.</p>
        <?php else: ?>
            <?php /* form menuju keranjang (tanpa upload bukti)  */ ?>
            <form method="post" action="../keranjang/proses_tambah.php" id="form-res" data-harga="<?= $b['harga'] ?>" data-deposit="<?= $b['deposit'] ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="barang_id" value="<?= $b['id'] ?>">
                <label for="d1">Tanggal mulai</label>
                <input id="d1" name="tgl_mulai" type="date" min="<?= date('Y-m-d') ?>" required>
                <label for="d2">Tanggal selesai</label>
                <input id="d2" name="tgl_selesai" type="date" min="<?= date('Y-m-d') ?>" required>
                <div id="ringkasan" class="info" style="margin-top:1rem">Pilih tanggal untuk melihat rincian biaya.</div>
                <?php /* keterangan transfer rekening  */ ?>
                <?= info_rekening() ?>
                <?php /* teks tombol  */ ?>
                <p><button class="btn">Masukkan ke keranjang</button></p>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>