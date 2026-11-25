<?php /* pemulihan akun */
require_once __DIR__ . '/../includes/auth.php'; /* jika sudah login: dialihkan ke halaman utama */
wajib_tamu();
csrf_cek();
// pemulihan: kirim KODE VERIFIKASI 6 digit lewat email (bukan lagi tautan langsung)
require_once __DIR__ . '/../includes/email.php';
$e = trim($_POST['email'] ?? '');
$pesan = 'Jika email terdaftar, kode verifikasi 6 digit telah dikirim (berlaku ' . KODE_BERLAKU_MENIT . ' menit). Kirim ulang bisa dilakukan setelah ' . KODE_JEDA_DETIK . ' detik.'; // pesan sama untuk email terdaftar/tidak
if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
    $s = $pdo->prepare('SELECT id, nama FROM users WHERE email=?');
    $s->execute([$e]);
    $u = $s->fetch();
    if ($u) {
        $t = $pdo->prepare("SELECT COUNT(*) FROM kode_pemulihan WHERE user_id=? AND created_at > NOW() - INTERVAL '" . (int)KODE_JEDA_DETIK . " seconds'");
        $t->execute([$u['id']]);
        if ($t->fetchColumn() == 0) { // di luar masa jeda: buat kode baru, kode lama hangus
            $kode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE kode_pemulihan SET dipakai=TRUE WHERE user_id=? AND dipakai=FALSE')->execute([$u['id']]);
            $pdo->prepare("INSERT INTO kode_pemulihan(user_id,kode_hash,expires_at) VALUES(?,?, NOW() + INTERVAL '" . (int)KODE_BERLAKU_MENIT . " minutes')")->execute([$u['id'], password_hash($kode, PASSWORD_DEFAULT)]);
            $pdo->commit();
            if (MODE_DEMO_EMAIL) {
                $pesan = "Mode demo (tanpa email): kode verifikasi Anda $kode (berlaku " . KODE_BERLAKU_MENIT . ' menit).';
            } else {
                kirim_email($e, 'Kode verifikasi pemulihan akun CARMENT', "Halo {$u['nama']},\n\nKode verifikasi pemulihan akun CARMENT Anda: $kode\nKode berlaku " . KODE_BERLAKU_MENIT . " menit. Jangan bagikan kode ini kepada siapa pun.\n\nJika Anda tidak meminta pemulihan akun, abaikan email ini.");
            }
        }
    }
}
flash($pesan);
redirect('auth/verifikasi.php?email=' . urlencode($e));