<?php
$judul = 'Riwayat sewa';
require __DIR__ . '/../includes/header.php';
$u = wajib_login(['customer']);

// hitung sisa waktu bayar
$s = $pdo->prepare('SELECT r.*, b.nama, EXTRACT(EPOCH FROM (r.batas_bayar - NOW()))::int AS sisa FROM reservasi r JOIN barang b ON b.id=r.barang_id WHERE r.user_id=? ORDER BY r.id DESC');
$s->execute([$u['id']]);
$rows = $s->fetchAll();
?>
<h1>Riwayat penyewaan</h1>
<?php if (!$rows): ?>
    <p>Belum ada reservasi. <a href="../index.php">Lihat katalog</a>.</p>
<?php else: ?>

    <?php /* kolom aksi */ ?>
    <div class="wrap">
        <table class="tbl">
            <tr><th>Unit</th><th>Tanggal</th><th>Sewa</th><th>Deposit</th><th>Denda</th><th>Status</th><th>Aksi</th></tr>

            <?php /* tombol bayar + hitung mundur */ ?>
            <?php /* tombol lihat bukti + alasan penolakan */ ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nama']) ?></td>
                    <td><?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?></td>
                    <td><?= rp($r['sewa']) ?></td>
                    <td><?= rp($r['deposit']) ?></td>
                    <td><?= rp($r['denda']) ?></td>
                    <td><?= badge($r['status']) ?></td>
                    <td>
                        <?php if ($r['status'] === 'Menunggu Pembayaran'): ?>
                            <a class="btn sm" href="bayar.php?id=<?= $r['id'] ?>">Bayar</a> <span class="hitung err" data-sisa="<?= max(0, (int)$r['sisa']) ?>"></span>
                        <?php endif; ?>
                        <?php if ($r['bukti']): ?>
                            <a class="btn sm alt" href="bukti.php?id=<?= $r['id'] ?>" target="_blank" rel="noopener">Lihat bukti</a>
                        <?php endif; ?>
                        <?php if ($r['status'] === 'Ditolak' && $r['alasan_tolak']): ?>
                            <div class="err">Ditolak: <?= e($r['alasan_tolak']) ?></div>
                        <?php endif; ?>

                        <?php // opsi pelanggan: lanjutkan transaksi (perbaiki pembayaran) atau minta pengembalian dana ?>
                        <?php if ($r['status'] === 'Perlu Perbaikan'): ?>
                            <div class="err">Pembayaran ditolak: <?= e($r['alasan_tolak']) ?></div>
                            <p class="mut">Tanggal masih terkunci untuk Anda. Sisa waktu <b class="hitung err" data-sisa="<?= max(0, (int)$r['sisa']) ?>"></b>. Pilih salah satu:</p>
                            <a class="btn sm" href="perbaiki.php?id=<?= $r['id'] ?>">Lanjutkan transaksi (perbaiki pembayaran)</a> <a class="btn sm alt" href="refund.php?id=<?= $r['id'] ?>">Minta pengembalian dana</a>
                        <?php endif; ?>
                        <?php if ($r['status'] === 'Menunggu Refund'): ?>
                            <div class="mut"><?= $r['refund_rekening'] ? 'Pengembalian dana ' . e(rp($r['sewa'] + $r['deposit'])) . ' ke ' . e($r['refund_bank']) . ' ' . e($r['refund_rekening']) . ' sedang diproses staff.' : 'Waktu perbaikan habis. Isi rekening untuk pengembalian dana.' ?></div>
                            <?php if (!$r['refund_rekening']): ?>
                                <a class="btn sm" href="refund.php?id=<?= $r['id'] ?>">Isi rekening refund</a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php // pelanggan dapat melihat bukti pengembalian dana dari staff ?>
                        <?php if ($r['status'] === 'Dikembalikan' && $r['bukti_refund']): ?>
                            <div class="mut">Dana Anda sudah dikembalikan.</div>
                            <a class="btn sm alt" href="bukti.php?id=<?= $r['id'] ?>&amp;refund=1" target="_blank" rel="noopener">Lihat bukti pengembalian dana</a>
                        <?php endif; ?>
                        <?php ?>
                        <?php ?>

                        <?php /* tombol tanya staff lewat WhatsApp berisi nomor reservasi + unit */ ?>
                        <div class="tanya"><a class="btn-min" href="<?= e(wa_link('Halo admin CARMENT, saya ' . $u['nama'] . '. Saya ingin bertanya tentang reservasi #' . $r['id'] . ' (' . $r['nama'] . ', ' . $r['tgl_mulai'] . ' s/d ' . $r['tgl_selesai'] . ').')) ?>" target="_blank" rel="noopener">Tanya staff (WhatsApp)</a></div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>