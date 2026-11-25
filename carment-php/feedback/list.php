<?php /* daftar feedback untuk staff & owner */
$judul = 'Feedback';
require __DIR__ . '/../includes/header.php';
wajib_login(['staff', 'owner']);
$rows = $pdo->query('SELECT id, nama, rating, pesan, created_at FROM feedback ORDER BY id DESC')->fetchAll();
$r = $pdo->query('SELECT COUNT(*) AS n, COALESCE(ROUND(AVG(rating), 1), 0) AS rata FROM feedback')->fetch();
?>
<h1>Feedback pelanggan</h1>
<p class="mut">Rata-rata <b><?= e($r['rata']) ?> / 5</b> dari <?= (int)$r['n'] ?> ulasan.</p>
<div class="wrap">
    <table class="tbl">
        <tr><th>Tanggal</th><th>Nama</th><th>Rating</th><th>Pesan</th><th>Aksi</th></tr>
        <?php foreach ($rows as $f): ?>
            <tr>
                <td><?= e(substr($f['created_at'], 0, 16)) ?></td>
                <td><?= e($f['nama']) ?></td>
                <td><span class="bintang-tampil" aria-label="<?= (int)$f['rating'] ?> dari 5"><?= str_repeat('&#9733;', (int)$f['rating']) . str_repeat('&#9734;', 5 - (int)$f['rating']) ?></span></td>
                <td><?= nl2br(e($f['pesan'])) ?></td>
                <td>
                    <form method="post" action="hapus.php" data-popup="Hapus feedback ini secara permanen?" data-ya="Ya, hapus">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                        <button class="btn sm bad">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach;
        if (!$rows) echo '<tr><td colspan="5">Belum ada feedback.</td></tr>'; ?>
    </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>