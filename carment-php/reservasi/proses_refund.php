<?php /* simpan permintaan pengembalian dana: tanggal dilepas, menunggu diproses staff */
require_once __DIR__ . '/../includes/auth.php';
$u = wajib_login(['customer']);
csrf_cek();
$id = (int)($_POST['id'] ?? 0);
$bank = trim($_POST['bank'] ?? '');
$rek = trim($_POST['rekening'] ?? '');
$an = trim($_POST['nama'] ?? '');
if (strlen($bank) < 2 || !preg_match('/^[0-9]{5,30}$/', $rek) || strlen($an) < 3) {
    flash('Lengkapi bank, nomor rekening (angka 5-30 digit), dan atas nama.');
    redirect("reservasi/refund.php?id=$id");
}
$pdo->beginTransaction();
$s = $pdo->prepare("SELECT id FROM reservasi WHERE id=? AND user_id=? AND (status='Perlu Perbaikan' OR (status='Menunggu Refund' AND refund_rekening IS NULL)) FOR UPDATE");
$s->execute([$id, $u['id']]);
if (!$s->fetch()) {
    $pdo->rollBack();
    flash('Reservasi tidak ditemukan atau tidak bisa diajukan pengembalian dana.');
    redirect('reservasi/riwayat.php');
}
$pdo->prepare("UPDATE reservasi SET status='Menunggu Refund', batas_bayar=NULL, refund_bank=?, refund_rekening=?, refund_nama=? WHERE id=?")->execute([$bank, $rek, $an, $id]);
$pdo->commit();
flash('Permintaan pengembalian dana dikirim. Tanggal dibuka kembali; staff akan memproses refund ke rekening Anda.');
redirect('reservasi/riwayat.php');