<?php /* partial: butuh $b (array) dan $aksi (URL proses) */ ?>

<?php /* >>> REVISI #9 [UBAH] form mendukung upload foto (baris di bawah ini) */ ?>
<form method="post" enctype="multipart/form-data" action="<?= e($aksi) ?>" class="card narrow" style="max-width:560px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($b['id'] ?? 0) ?>">
    
    <label for="nm">Nama unit</label>
    <input id="nm" name="nama" value="<?= e($b['nama'] ?? '') ?>" required>
    
    <?php /* >>> REVISI #5 [UBAH] kategori dari database (baris di bawah ini) */ ?>
    <label for="kt">Kategori</label>
    <select id="kt" name="kategori">
        <?php foreach (daftar_kategori() as $c): ?>
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
    
    <?php /* >>> REVISI #7 #9 [BARU] pilihan status + input foto (blok sampai tanda AKHIR) */ ?>
    <?php if (!empty($b['id'])): ?>
        <label for="st">Status unit</label>
        <select id="st" name="status">
            <?php foreach (['Available', 'Maintenance'] as $s): ?>
                <option <?= ($b['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    
    <label for="ft">Foto unit (jpg/png/webp, maks 3 MB)</label>
    <input id="ft" name="foto" type="file" accept="image/*">
    
    <?php if (!empty($b['foto_nama'])): ?>
        <p class="mut">
            Foto saat ini (kosongkan jika tidak diganti):<br>
            <img src="<?= foto_thumb($b['foto_nama']) ?>" alt="" style="max-width:160px;border-radius:8px">
        </p>
    <?php endif; ?>
    <?php /* <<< AKHIR REVISI */ ?>
    
    <p>
        <button class="btn">Simpan</button> 
        <a class="btn alt" href="list.php">Batal</a>
    </p>
</form>