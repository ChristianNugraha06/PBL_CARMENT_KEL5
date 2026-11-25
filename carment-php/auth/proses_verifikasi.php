<?php /* periksa kode verifikasi; benar -> token atur ulang sandi sekali pakai */
require_once __DIR__ . '/../includes/auth.php';
wajib_tamu();
csrf_cek();
$e = trim($_POST['email'] ?? '');
$kode = trim($_POST['kode'] ?? '');
$balik = 'auth/verifikasi.php?email=' . urlencode($e);
$gagal = 'Kode salah atau sudah kedaluwarsa. Periksa kembali atau minta kode baru.'; // pesan sama untuk semua kegagalan (tidak membocorkan apakah email terdaftar)
if (!preg_match('/^[0-9]{6}$/', $kode)) {
    flash($gagal);
    redirect($balik);
}
$pdo->beginTransaction();
$s = $pdo->prepare("SELECT k.id, k.kode_hash, k.user_id FROM kode_pemulihan k JOIN users u ON u.id=k.user_id WHERE u.email=? AND k.dipakai=FALSE AND k.expires_at > NOW() ORDER BY k.id DESC LIMIT 1 FOR UPDATE OF k");
$s->execute([$e]);
$k = $s->fetch();
if (!$k) {
    $pdo->rollBack();
    flash($gagal);
    redirect($balik);
}
if (!password_verify($kode, $k['kode_hash'])) { // salah: hitung percobaan; setelah batas, kode hangus
    $pdo->prepare('UPDATE kode_pemulihan SET percobaan = percobaan + 1, dipakai = (percobaan + 1 >= ?) WHERE id=?')->execute([KODE_MAKS_SALAH, $k['id']]);
    $pdo->commit();
    flash($gagal);
    redirect($balik);
}
$pdo->prepare('UPDATE kode_pemulihan SET dipakai=TRUE WHERE id=?')->execute([$k['id']]); // sekali pakai
$token = bin2hex(random_bytes(32));
$pdo->prepare("INSERT INTO reset_password(user_id,token_hash,expires_at) VALUES(?,?, NOW() + INTERVAL '15 minutes')")->execute([$k['user_id'], hash('sha256', $token)]);
$pdo->commit();
flash('Kode benar. Silakan buat kata sandi baru.');
redirect('auth/reset.php?token=' . $token);