<?php /* simpan bukti perbaikan: status kembali Menunggu verifikasi, tanggal tetap abu-abu */
require_once __DIR__ . '/../includes/auth.php';
$u = wajib_login(['customer']);
csrf_cek();
$id = (int)($_POST['id'] ?? 0);
$f = $_FILES['bukti'] ?? null;
$ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
$cat = trim($_POST['catatan'] ?? '');
if (!$f || $f['error'] !== UPLOAD_ERR_OK || !in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true) || $f['size'] > 2 * 1024 * 1024) {
    flash('Unggah bukti transfer (jpg/png/pdf, maks 2 MB).');
    redirect("reservasi/perbaiki.php?id=$id");
}
$pdo->beginTransaction();
$s = $pdo->prepare("SELECT id FROM reservasi WHERE id=? AND user_id=? AND status='Perlu Perbaikan' AND batas_bayar > NOW() FOR UPDATE");
$s->execute([$id, $u['id']]);
if (!$s->fetch()) {
    $pdo->rollBack();
    flash('Waktu perbaikan habis atau reservasi tidak ditemukan.');
    redirect('reservasi/riwayat.php');
}
$nama = bin2hex(random_bytes(8)) . '.' . $ext;
move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $nama);
$pdo->prepare("UPDATE reservasi SET bukti_lama=bukti, bukti=?, status='Menunggu', batas_bayar=NULL, catatan_customer=? WHERE id=?")->execute([$nama, $cat !== '' ? $cat : null, $id]);
$pdo->commit();
flash('Bukti perbaikan terkirim. Menunggu verifikasi staff; tanggal tetap terkunci sampai dikonfirmasi.');
redirect('reservasi/riwayat.php');