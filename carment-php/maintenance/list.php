<?php
$judul = 'Maintenance';
require __DIR__ . '/../includes/header.php';
wajib_login(['staff']);

// pulihkan data lama: unit berstatus Maintenance yang belum punya tiket dibuatkan tiket otomatis
$pdo->exec("INSERT INTO maintenance(barang_id,masalah,status,tgl) SELECT b.id, 'Dialihkan ke Maintenance oleh staff (tiket dibuat otomatis)', 'Dibuka', CURRENT_DATE FROM barang b WHERE b.status='Maintenance' AND NOT EXISTS (SELECT 1 FROM maintenance m WHERE m.barang_id=b.id AND m.status='Dibuka')");

$rows = $pdo->query('SELECT m.*, b.nama FROM maintenance m JOIN barang b ON b.id=m.barang_id ORDER BY m.id DESC')->fetchAll();
?>
<h1>Tiket maintenance</h1>
<div class="wrap">
    <table class="tbl">
        <tr><th>Unit</th><th>Masalah</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
        <?php foreach ($rows as $m): ?>
            <tr>
                <td><?= e($m['nama']) ?></td>
                <td><?= e($m['masalah']) ?></td>
                <td><?= e($m['tgl']) ?></td>
                <td><?= badge($m['status']) ?></td>
                <td>

                    <?php /* popup konfirmasi perbaikan selesai */ ?>
                    <?php if ($m['status'] === 'Dibuka'): ?>
                        <form method="post" action="proses_selesai.php" data-popup="Konfirmasi: <?= e($m['nama']) ?> sudah selesai diperbaiki? Unit akan kembali tersedia untuk disewa.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button class="btn sm">Tandai selesai</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr><td colspan="5">Belum ada tiket.</td></tr>
        <?php endif; ?>
    </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>