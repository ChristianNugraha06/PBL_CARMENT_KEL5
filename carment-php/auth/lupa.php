<?php /* pemulihan akun */
$judul = 'Pulihkan akun'; /* jika sudah login: dialihkan ke halaman utama */
require_once __DIR__ . '/../includes/auth.php';
wajib_tamu();
require __DIR__ . '/../includes/header.php';
?>
<?php /* kode verifikasi dikirim lewat email */ ?>
<div class="card narrow">
    <h2>Pulihkan akun</h2>
    <p class="mut">Masukkan email akun Anda. Kami mengirim kode verifikasi 6 digit ke email tersebut sebelum Anda bisa membuat kata sandi baru.</p>
    <form method="post" action="proses_lupa.php">
        <?= csrf_field() ?>
        <label for="e">Email</label>
        <input id="e" name="email" type="email" required>
        <p><button class="btn">Kirim kode verifikasi</button></p>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>