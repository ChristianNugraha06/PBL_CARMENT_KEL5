<?php 
$judul = 'Masuk'; 
require __DIR__ . '/../includes/header.php'; 
?>

<div class="card narrow">
    <h2>Masuk ke CARMENT</h2>
    <form method="post" action="proses_login.php">
        <?= csrf_field() ?>
        
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required>
        
        <label for="pw">Kata sandi</label>
        <input id="pw" name="password" type="password" required>
        
        <p>
            <button class="btn">Masuk</button>
        </p>
    </form>
    
    <p class="mut">
        Belum punya akun? <a href="register.php">Daftar</a>
    </p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>