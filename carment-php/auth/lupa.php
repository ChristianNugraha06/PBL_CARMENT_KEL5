<?php 
/* >>> REVISI [FILE BARU] #12 pemulihan akun (seluruh file ini baru) */ 
$judul = 'Pulihkan akun'; 
require __DIR__ . '/../includes/header.php'; 
?>

<div class="card narrow">
    <h2>Pulihkan akun</h2>
    <p class="mut">Masukkan email akun Anda untuk mengatur ulang kata sandi.</p>
    
    <form method="post" action="proses_lupa.php">
        <?= csrf_field() ?>
        <label for="e">Email</label>
        <input id="e" name="email" type="email" required>
        
        <p><button class="btn">Kirim tautan pemulihan</button></p>
    </form>
</div>

<?php 
require __DIR__ . '/../includes/footer.php'; 
?>