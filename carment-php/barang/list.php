<?php 
$judul = 'Kelola barang'; 
require __DIR__ . '/../includes/header.php'; 
wajib_login(['staff']);

$rows =$pdo->query('SELECT * FROM barang ORDER BY nama')->fetchAll(); 
?>

<h1>Kelola katalog barang</h1>
<p>
    <a class="btn" href="tambah.php">Tambah unit</a>
</p>

<div class="wrap">
    <table class="tbl">
        <tr>
            <th>Unit</th>
            <th>Kategori</th>
            <th>Harga/hari</th>
            <th>Deposit</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($rows as$b): ?>
            <tr>
                <td><?= e($b['nama']) ?></td>
                <td><?= e($b['kategori']) ?></td>
                <td><?= rp($b['harga']) ?></td>
                <td><?= rp($b['deposit']) ?></td>
                <td><?= badge($b['status']) ?></td>
                <td>
                    <a class="btn sm" href="edit.php?id=<?= $b['id'] ?>">Edit</a>
                    <form method="post" action="hapus.php" style="display:inline" data-confirm="Hapus unit ini?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                        <button class="btn sm bad">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?