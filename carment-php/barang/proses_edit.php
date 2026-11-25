<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_login(['staff']);
csrf_cek();
$nama = trim($_POST['nama'] ?? '');
$harga = (int)($_POST['harga'] ?? 0);
$id = (int)($_POST['id'] ?? 0);

// tolak edit saat unit disewa 
if (sedang_disewa($id)) {
    flash('Unit sedang disewa, tidak bisa diedit selama masa rental.');
    redirect('barang/list.php');
}
if ($nama === '' || $harga <= 0) {
    flash('Nama dan harga sewa wajib diisi.');
    redirect("barang/edit.php?id=$id");
}

// kategori, status, ganti foto 
$kat = $_POST['kategori'] ?? '';
if (!in_array($kat, daftar_kategori(), true)) {
    flash('Kategori tidak valid.');
    redirect("barang/edit.php?id=$id");
}
$st = in_array($_POST['status'] ?? '', ['Available','Maintenance'], true) ? $_POST['status'] : null; // 'Rented' hanya diatur otomatis
$lama = $pdo->prepare('SELECT foto_nama, status FROM barang WHERE id=?');
$lama->execute([$id]);
$lamaRow = $lama->fetch();
$fotoLama = $lamaRow['foto_nama'] ?? null;

// unit yang dialihkan ke Maintenance wajib punya masalah/catatan untuk tiketnya 
$statusLama = $lamaRow['status'] ?? '';
$masalah = trim($_POST['masalah'] ?? '');
if ($st === 'Maintenance' && $statusLama !== 'Maintenance' && strlen($masalah) < 3) {
    flash('Isi masalah/catatan maintenance (minimal 3 karakter) agar tiket perbaikan dibuat.');
    redirect("barang/edit.php?id=$id");
}

[$foto, $err] = simpan_foto($_FILES['foto'] ?? null);
if ($err) {
    flash($err);
    redirect("barang/edit.php?id=$id");
}

// update unit + tiket maintenance dalam satu transaksi 
$pdo->beginTransaction();
$pdo->prepare('UPDATE barang SET nama=?,kategori=?,harga=?,deposit=?,spesifikasi=?,kondisi=?,status=COALESCE(?,status),foto_nama=COALESCE(?,foto_nama),foto_url=COALESCE(?,foto_url) WHERE id=?')
    ->execute([$nama, $kat, $harga, (int)($_POST['deposit'] ?? 0), trim($_POST['spesifikasi'] ?? ''), trim($_POST['kondisi'] ?? ''), $st, $foto, $foto ? 'gambar/asli/' . $foto : null, $id]);
if ($st === 'Maintenance') { // pastikan ada tiket Dibuka agar unit tampil di halaman Maintenance (tidak dobel jika sudah ada)
    $ada = $pdo->prepare("SELECT 1 FROM maintenance WHERE barang_id=? AND status='Dibuka'");
    $ada->execute([$id]);
    if (!$ada->fetchColumn()) {
        $pdo->prepare("INSERT INTO maintenance(barang_id,masalah,status,tgl) VALUES(?,?,'Dibuka',CURRENT_DATE)")->execute([$id, $masalah !== '' ? $masalah : 'Dialihkan ke Maintenance oleh staff']);
    }
} elseif ($st === 'Available' && $statusLama === 'Maintenance') { // dikembalikan manual: tutup tiket yang masih terbuka
    $pdo->prepare("UPDATE maintenance SET status='Selesai' WHERE barang_id=? AND status='Dibuka'")->execute([$id]);
}

$pdo->commit();
if ($foto && $fotoLama) {
    hapus_foto($fotoLama);
}

flash('Unit diperbarui.');
redirect('barang/list.php');