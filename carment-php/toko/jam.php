<?php /* edit jadwal buka toko (staff) */
$judul = 'Jadwal toko';
require __DIR__ . '/../includes/header.php';
wajib_login(['staff']);
$jam = jam_toko();
?>
<h1>Jadwal buka toko</h1>
<p class="mut">Jadwal ini tampil di banner halaman utama dan menentukan status "Buka/Tutup sekarang". Centang Libur untuk hari tutup.</p>

<?php /* popup konfirmasi simpan jadwal */ ?>
<form method="post" action="proses_jam.php" class="card" style="max-width:560px" data-popup="Simpan jadwal toko? Jadwal baru langsung tampil di halaman utama.">
    <?= csrf_field() ?>
    <div class="wrap">
        <table class="tbl">
            <tr><th>Hari</th><th>Libur</th><th>Buka</th><th>Tutup</th></tr>
            <?php foreach (NAMA_HARI as $n => $nm): ?>
                <?php $j = $jam[$n]; ?>
                <tr>
                    <td><?= $nm ?></td>
                    <td><input type="checkbox" name="libur[<?= $n ?>]" value="1" <?= $j ? '' : 'checked' ?> aria-label="Libur hari <?= $nm ?>"></td>
                    <td><input type="time" name="buka[<?= $n ?>]" value="<?= $j ? e($j[0]) : '09:00' ?>" aria-label="Jam buka <?= $nm ?>"></td>
                    <td><input type="time" name="tutup[<?= $n ?>]" value="<?= $j ? e($j[1]) : '17:00' ?>" aria-label="Jam tutup <?= $nm ?>"></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <p><button class="btn">Simpan jadwal</button></p>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>