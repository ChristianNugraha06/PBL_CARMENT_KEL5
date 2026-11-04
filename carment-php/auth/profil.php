<?php 
/* >>> REVISI [FILE BARU] #13 edit profil (seluruh file ini baru) */ 
$judul = 'Profil'; 
require __DIR__ . '/../includes/header.php'; 
$u = wajib_login();

$s = $pdo->prepare('SELECT nama,email,hp FROM users WHERE id=?'); 
$s->execute([$u['id']]); 
$d = $s->fetch(); 
?>

<div class="card narrow">
    <h2>Edit profil</h2>
    
    <form method="post" action="proses_profil.php">
        <?= csrf_field() ?>
        
        <label for="n">Nama lengkap</label>
        <input id="n" name="nama" value="<?= e($d['nama']) ?>" required>
        
        <label for="e">Email</label>
        <input id="e" name="email" type="email" value="<?= e($d['email']) ?>" required>
        
        <label for="h">No. WhatsApp</label>
        <input id="h" name="hp" type="tel" value="<?= e($d['hp']) ?>">
        
        <h3 style="margin-top:1rem">Ganti kata sandi (opsional)</h3>
        
        <label for="sl">Sandi lama</label>
        <input id="sl" name="sandi_lama" type="password" autocomplete="current-password">
        
        <label for="sb">Sandi baru (min. 6 karakter)</label>
        <input id="sb" name="sandi_baru" type="password" minlength="6" autocomplete="new-password">
        
        <p><button class="btn">Simpan profil</button></p>
    </form>
</div>

<?php 
require __DIR__ . '/../includes/footer.php'; 
?>