<?php 
/* >>> REVISI [FILE BARU] #5 (seluruh file ini baru) */ 
$judul = 'Kategori'; 
require __DIR__ . '/../includes/header.php'; 
wajib_login(['staff']);

$rows = $pdo->query('SELECT k.*, (SELECT COUNT(*) FROM barang b WHERE b.kategori = k.nama) AS jml FROM kategori k ORDER BY k.nama')->fetchAll(); 
?>

<h1>Kelola kategori</h1>

<form method="post" action="proses_tambah.php" class="bar">
    <?= csrf_field() ?>
    <input name="nama" placeholder="Nama kategori baru" maxlength="30" required aria-label="Nama kategori">
    <button class="btn">Tambah</button>
</form>

<div class="wrap">
    <table class="tbl">
        <tr>
            <th>Nama kategori</th>
            <th>Jumlah unit</th>
            <th>Hapus</th>
        </tr>
        
        <?php foreach ($rows as $k): ?>
            <tr>
                <td>
                    <form method="post" action="proses_edit.php" class="bar" style="margin:0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $k['id'] ?>">
                        <input name="nama" value="<?= e($k['nama']) ?>" maxlength="30" required aria-label="Nama">
                        <button class="btn sm">Simpan</button>
                    </form>
                </td>
                <td><?= (int)$k['jml'] ?></td>
                <td>
                    <form method="post" action="hapus.php" data-confirm="Hapus kategori ini?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $k['id'] ?>">
                        <button class="btn sm bad">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php 
require __DIR__ . '/../includes/footer.php'; 
?>