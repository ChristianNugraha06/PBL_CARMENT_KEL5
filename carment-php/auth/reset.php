<?php 
/* >>> REVISI [FILE BARU] #12 pemulihan akun (seluruh file ini baru) */ 
$judul = 'Atur ulang sandi'; 
require __DIR__ . '/../includes/header.php'; 

$token = $_GET['token'] ?? ''; 
$s = $pdo->prepare('SELECT id FROM reset_password WHERE token_hash=? AND dipakai=FALSE AND expires_at > NOW()'); 
$s->execute([hash('sha256', $token)]); 

if (!$s->fetch()) { 
    flash('Tautan pemulihan tidak valid atau sudah kedaluwarsa.'); 
    redirect('auth/lupa.php'); 
} 
?>

<div class="card narrow">
    <h2>Atur ulang kata sandi</h2>
    
    <form method="post" action="proses_reset.php">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        
        <label for="p">Kata sandi baru (min. 6 karakter)</label>
        <input id="p" name="password" type="password" minlength="6" required>
        
        <p><button class="btn">Simpan sandi baru</button></p>
    </form>
</div>

<?php 
require __DIR__ . '/../includes/footer.php'; 
?>