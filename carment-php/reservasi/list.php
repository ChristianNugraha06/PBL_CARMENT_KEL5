<?php
$judul = 'Reservasi';
require __DIR__ . '/../includes/header.php';
wajib_login(['staff']);

// tampilkan juga reservasi Perlu Perbaikan & Menunggu Refund + sisa waktu 
$rows = $pdo->query("SELECT r.*, b.nama AS barang, u.nama AS pelanggan, EXTRACT(EPOCH FROM (r.batas_bayar - NOW()))::int AS sisa FROM reservasi r JOIN barang b ON b.id=r.barang_id JOIN users u ON u.id=r.user_id WHERE r.status IN ('Menunggu','Perlu Perbaikan','Menunggu Refund','Dikonfirmasi','Dipinjam') ORDER BY r.tgl_mulai")->fetchAll();

// popup konfirmasi serahkan barang 
function aksi_btn($id, $aksi, $label, $cls = '')
{
    $pesan = $aksi === 'serahkan'
        ? 'Serahkan unit ini kepada pelanggan? Status reservasi menjadi Dipinjam dan unit menjadi Rented.'
        : ($aksi === 'refund_selesai'
            ? 'Tandai dana sudah dikembalikan ke pelanggan? Status menjadi Dikembalikan dan tidak dapat diubah lagi.'
            : 'Lanjutkan: ' . $label . '?');
    return '<form method="post" action="proses_status.php" style="display:inline" data-popup="' . e($pesan) . '">' . csrf_field() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="aksi" value="' . $aksi . '"><button class="btn sm ' . $cls . '">' . $label . '</button></form> ';
}
?>

<h1>Reservasi aktif &amp; serah-terima</h1>
<div class="wrap">
    <table class="tbl">
        <tr><th>Pelanggan</th><th>Unit</th><th>Tanggal</th><th>Bukti</th><th>Status</th><th>Aksi</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['pelanggan']) ?></td>
                <td><?= e($r['barang']) ?></td>
                <td><?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?></td>
                <?php /* bukti lewat halaman terproteksi */ ?>
                <td><a href="<?= BASE_URL ?>reservasi/bukti.php?id=<?= $r['id'] ?>" target="_blank" rel="noopener">Lihat</a></td>
                <td><?= badge($r['status']) ?></td>
                <td>
                    <?php /* tombol Periksa pembayaran (menggantikan Konfirmasi/Tolak langsung) */ ?>
                    <?php if ($r['status'] === 'Menunggu'): ?>
                        <button type="button" class="btn sm" data-verif data-id="<?= $r['id'] ?>" data-pelanggan="<?= e($r['pelanggan']) ?>" data-unit="<?= e($r['barang']) ?>" data-tgl="<?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?>" data-total="<?= e(rp($r['sewa'] + $r['deposit'])) ?>" data-bukti="<?= BASE_URL ?>reservasi/bukti.php?id=<?= $r['id'] ?>" data-pdf="<?= substr($r['bukti'], -4) === '.pdf' ? '1' : '0' ?>">Periksa pembayaran</button>
                    <?php endif; ?>
                    <?php if ($r['status'] === 'Dikonfirmasi'): ?>
                        <?= aksi_btn($r['id'], 'serahkan', 'Serahkan barang') ?>
                    <?php endif; ?>
                    <?php if ($r['status'] === 'Dipinjam'): ?>
                        <a class="btn sm" href="../inspeksi/form.php?id=<?= $r['id'] ?>">Inspeksi pengembalian</a>
                    <?php endif; ?>

                    <?php // aksi staff untuk Perlu Perbaikan (menunggu pelanggan), Menunggu Refund, dan bukti sebelumnya ?>
                    <?php if ($r['status'] === 'Perlu Perbaikan'): ?>
                        <span class="mut">Menunggu perbaikan pelanggan</span> <b class="hitung err" data-sisa="<?= max(0, (int)$r['sisa']) ?>"></b><div class="mut">Alasan: <?= e($r['alasan_tolak']) ?></div>
                    <?php endif; ?>

                    <?php // tombol selesai refund kini berupa form unggah bukti pengembalian dana  ?>
                    <?php if ($r['status'] === 'Menunggu Refund'): ?>
                        <div>Refund <?= e(rp($r['sewa'] + $r['deposit'])) ?> &rarr; <?= $r['refund_rekening'] ? e($r['refund_bank']) . ' ' . e($r['refund_rekening']) . ' a.n. ' . e($r['refund_nama']) : '<span class="err">rekening belum diisi pelanggan</span>' ?></div>
                        <?php if ($r['refund_rekening']): ?>
                            <form method="post" action="proses_status.php" enctype="multipart/form-data" class="form-refund" data-popup="Tandai dana sudah dikembalikan dan kirim bukti pengembalian? Status menjadi Dikembalikan dan tidak dapat diubah lagi." data-ya="Ya, selesai">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="aksi" value="refund_selesai">
                                <label class="mut" for="br<?= $r['id'] ?>">Bukti pengembalian dana (jpg/png/pdf, maks 2 MB)</label>
                                <input id="br<?= $r['id'] ?>" type="file" name="bukti_refund" accept="image/*,.pdf" required>
                                <button class="btn sm">Dana sudah dikembalikan</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($r['bukti_lama']): ?>
                        <div><a href="<?= BASE_URL ?>reservasi/bukti.php?id=<?= $r['id'] ?>&amp;lama=1" target="_blank" rel="noopener">Bukti sebelumnya</a></div>
                    <?php endif; ?>
                    <?php // ?>

                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr><td colspan="6">Tidak ada reservasi aktif.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php /* popup verifikasi: bukti + Terima/Tolak + konfirmasi tambahan */ ?>
<dialog id="dlg-verif" aria-labelledby="v-judul">
    <div class="dlg">
        <h3 id="v-judul">Verifikasi pembayaran</h3>
        <p id="v-info" class="mut"></p>
        <div id="v-bukti" class="v-bukti"></div>
        <div id="v-aksi" class="dlg-aksi">
            <button type="button" class="btn" id="v-terima">Terima</button>
            <button type="button" class="btn bad" id="v-tolak">Tolak</button>
            <button type="button" class="btn alt" data-tutup>Tutup</button>
        </div>
        <form id="v-form-terima" method="post" action="proses_status.php" hidden>
            <?= csrf_field() ?>
            <input type="hidden" name="id" class="v-id">
            <input type="hidden" name="aksi" value="konfirmasi">
            <p><b>Konfirmasi:</b> terima pembayaran ini? Reservasi menjadi Dikonfirmasi dan pelanggan dapat mengambil unit.</p>
            <div class="dlg-aksi">
                <button class="btn">Ya, terima</button>
                <button type="button" class="btn alt" data-kembali>Batal</button>
            </div>
        </form>
        <form id="v-form-tolak" method="post" action="proses_status.php" hidden>

            <?= csrf_field() ?>
            <input type="hidden" name="id" class="v-id">
            <input type="hidden" name="aksi" value="tolak">
            <label for="v-alasan">Alasan penolakan (wajib, akan dilihat pelanggan)</label>
            <textarea id="v-alasan" name="alasan" rows="3" minlength="5" required></textarea>

            <?php /* teks konfirmasi tolak: tanggal tetap terkunci, pelanggan diberi kesempatan  */ ?>
            <p><b>Konfirmasi:</b> yakin menolak pembayaran ini? Tanggal <b>tetap terkunci</b> (abu-abu) dan pelanggan diberi waktu <?= BATAS_PERBAIKAN_JAM ?> jam untuk memperbaiki pembayaran atau meminta pengembalian dana.</p>
            <div class="dlg-aksi">
                <button class="btn bad">Ya, tolak</button>
                <button type="button" class="btn alt" data-kembali>Batal</button>
            </div>
        </form>
    </div>
</dialog>
<?php ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>