<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_login(['staff']);
csrf_cek();
$id = (int)($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';

// tolak = Perlu Perbaikan (bukan batal final); tambah aksi refund_selesai 
$map = [
    'konfirmasi' => ['Menunggu', 'Dikonfirmasi'],
    'tolak' => ['Menunggu', 'Perlu Perbaikan'],
    'serahkan' => ['Dikonfirmasi', 'Dipinjam'],
    'refund_selesai' => ['Menunggu Refund', 'Dikembalikan'],
];
if (!isset($map[$aksi])) {
    flash('Aksi tidak dikenal.');
    redirect('reservasi/list.php');
}
[$dari, $ke] = $map[$aksi];

// alasan penolakan wajib + simpan
$alasan = trim($_POST['alasan'] ?? '');
if ($aksi === 'refund_selesai') {
    $c = $pdo->prepare('SELECT refund_rekening FROM reservasi WHERE id=?');
    $c->execute([$id]);
    if (!$c->fetchColumn()) {
        flash('Rekening tujuan refund belum diisi pelanggan.');
        redirect('reservasi/list.php');
    }
}

// pengembalian dana WAJIB disertai bukti transfer pengembalian dari staff
$fileRefund = null;
if ($aksi === 'refund_selesai') {
    $f = $_FILES['bukti_refund'] ?? null;
    $ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
    if (!$f || $f['error'] !== UPLOAD_ERR_OK || !in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true) || $f['size'] > 2 * 1024 * 1024) {
        flash('Unggah bukti pengembalian dana (jpg/png/pdf, maks 2 MB) sebelum menandai selesai.');
        redirect('reservasi/list.php');
    }
    $fileRefund = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $fileRefund)) {
        flash('Gagal menyimpan bukti pengembalian dana.');
        redirect('reservasi/list.php');
    }
}

if ($aksi === 'tolak' && strlen($alasan) < 5) {
    flash('Alasan penolakan wajib diisi (minimal 5 karakter).');
    redirect('reservasi/list.php');
}
$s = $pdo->prepare('UPDATE reservasi SET status=?, alasan_tolak=? WHERE id=? AND status=?');
$s->execute([$ke, $aksi === 'tolak' ? $alasan : null, $id, $dari]);

// tanggal TETAP terkunci dan pelanggan diberi batas waktu perbaikan
if ($aksi === 'tolak' && $s->rowCount()) {
    $pdo->prepare("UPDATE reservasi SET batas_bayar = NOW() + INTERVAL '" . (int)BATAS_PERBAIKAN_JAM . " hours' WHERE id=?")->execute([$id]);
}

// simpan nama berkas bukti refund; bila status ternyata sudah berubah, berkas dihapus agar tidak yatim
if ($fileRefund) {
    if ($s->rowCount()) {
        $pdo->prepare('UPDATE reservasi SET bukti_refund=? WHERE id=?')->execute([$fileRefund, $id]);
    } else {
        @unlink(__DIR__ . '/../uploads/' . $fileRefund);
    }
}

if ($ke === 'Dipinjam' && $s->rowCount()) {
    $pdo->prepare("UPDATE barang SET status='Rented' WHERE id=(SELECT barang_id FROM reservasi WHERE id=?)")->execute([$id]);
}
flash($aksi === 'tolak' ? 'Pembayaran ditolak. Tanggal tetap terkunci selama ' . BATAS_PERBAIKAN_JAM . ' jam agar pelanggan bisa memperbaiki pembayaran atau meminta pengembalian dana.' : "Status reservasi diubah: $ke.");
redirect('reservasi/list.php');