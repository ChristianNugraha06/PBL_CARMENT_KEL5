<?php 
$judul = 'Reservasi'; 
require __DIR__ . '/../includes/header.php'; 
wajib_login(['staff']);

$rows = $pdo->query("SELECT r.*, b.nama AS barang, u.nama AS pelanggan FROM reservasi r JOIN barang b ON b.id=r.barang_id JOIN users u ON u.id=r.user_id WHERE r.status IN ('Menunggu','Dikonfirmasi','Dipinjam') ORDER BY r.tgl_mulai")->fetchAll();

function aksi_btn($id, $aksi, $label, $cls = '') { 
    return '<form method="post" action="proses_status.php" style="display:inline">' . csrf_field() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="aksi" value="' . $aksi . '"><button class="btn sm ' . $cls . '">' . $label . '</button></form> '; 
} 
?>

<h1>Reservasi aktif &amp; serah-terima</h1>

<div class="wrap">
    <table class="tbl">
        <tr>
            <th>Pelanggan</th>
            <th>Unit</th>
            <th>Tanggal</th>
            <th>Bukti</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['pelanggan']) ?></td>
                <td><?= e($r['barang']) ?></td>
                <td><?= e($r['tgl_mulai']) ?> s/d <?= e($r['tgl_selesai']) ?></td>
                <td>
                    <a href="<?= BASE_URL ?>uploads/<?= e($r['bukti']) ?>" target="_blank" rel="noopener">Lihat</a>
                </td>
                <td><?= badge($r['status']) ?></td>
                <td>
                    <?php 
                    if ($r['status'] === 'Menunggu') {
                        echo aksi_btn($r['id'], 'konfirmasi', 'Konfirmasi') . aksi_btn($r['id'], 'tolak', 'Tolak', 'bad');
                    }
                    if ($r['status'] === 'Dikonfirmasi') {
                        echo aksi_btn($r['id'], 'serahkan', 'Serahkan barang');
                    }
                    if ($r['status'] === 'Dipinjam') {
                        echo '<a class="btn sm" href="../inspeksi/form.php?id=' . $r['id'] . '">Inspeksi pengembalian</a>';
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr>
                <td colspan="6">Tidak ada reservasi aktif.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>