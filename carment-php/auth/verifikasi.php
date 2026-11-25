<?php /* langkah 2 pemulihan akun: masukkan kode verifikasi dari email */
$judul = 'Verifikasi kode';
require_once __DIR__ . '/../includes/auth.php';
wajib_tamu();
require __DIR__ . '/../includes/header.php';
$email = trim($_GET['email'] ?? '');
?>
<div class="card narrow">
    <h2>Masukkan kode verifikasi</h2>
    <p class="mut">Kami mengirim kode 6 digit ke email Anda. Kode berlaku <?= KODE_BERLAKU_MENIT ?> menit dan hanya bisa dicoba <?= KODE_MAKS_SALAH ?> kali.</p>
    <form method="post" action="proses_verifikasi.php">
        <?= csrf_field() ?>
        <label for="e">Email</label>
        <input id="e" name="email" type="email" value="<?= e($email) ?>" required>
        <label for="k">Kode verifikasi</label>
        <input id="k" name="kode" class="kode-input" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" required>
        <p><button class="btn">Verifikasi</button></p>
    </form>
    <form method="post" action="proses_lupa.php">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= e($email) ?>">
        <p class="mut">Belum menerima kode? <button class="btn-min">Kirim ulang kode</button></p>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>