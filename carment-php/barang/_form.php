<?php /* partial: butuh $b (array) dan $aksi (URL proses) */ ?>
<form method="post" action="<?= e($aksi) ?>" class="card narrow" style="max-width:560px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($b['id'] ?? 0) ?>">
    
    <label for="nm">Nama unit</label>
    <input id="nm" name="nama" value="<?= e($b['nama'] ?? '') ?>" required>
    
    <label for="kt">Kategori</label>
    <select id="kt" name="kategori">
        <?php foreach (['Kamera', 'Lensa', 'Aksesori'] as $c): ?>
            <option <?= ($b['kategori'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
    </select>
    
    <label for="hg">Harga sewa / hari</label>
    <input id="hg" name="harga" type="number" min="0" value="<?= e($b['harga'] ?? '') ?>" required>
    
    <label for="dp">Deposit</label>
    <input id="dp" name="deposit" type="number" min="0" value="<?= e($b['deposit'] ?? '') ?>" required>
    
    <label for="sp">Spesifikasi</label>
    <textarea id="sp" name="spesifikasi" rows="2"><?= e($b['spesifikasi'] ?? '') ?></textarea>
    
    <label for="kd">Kondisi fisik</label>
    <textarea id="kd" name="kondisi" rows="2"><?= e($b['kondisi'] ?? 'Baik.') ?></textarea>
    
    <p>
        <button class="btn">Simpan</button> 
        <a class="btn alt" href="list.php">Batal</a>
    </p>
</form>