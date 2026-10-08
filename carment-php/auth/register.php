<?php 
$judul = 'Daftar'; 
require __DIR__ . '/../includes/header.php'; 
?>

<div class="card narrow">
    <h2>Buat akun pelanggan</h2>
    <form method="post" action="proses_register.php">
        <?= csrf_field() ?>
        
        <label for="n">Nama lengkap</label>
        <input id="n" name="nama" required>
        
        <label for="e">Email</label>
        <input id="e" name="email" type="email" required>
        
        <label for="h">No. WhatsApp</label>
        <input id="h" name="hp" type="tel" required>
        
        <label for="p">Kata sandi (min. 6 karakter)</label>
        <input id="p" name="password" type="password" minlength="6" required>
        
        <p>
            <button class="btn">Daftar</button>
        </p>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>