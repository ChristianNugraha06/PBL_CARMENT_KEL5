<?php 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

$nama = trim($_POST['nama'] ?? ''); 
$harga = (int)($_POST['harga'] ?? 0); 
$id = (int)($_POST['id'] ?? 0);

// >>> REVISI #8 [BARU] tolak edit saat unit disewa (baris di bawah ini)
if (sedang_disewa($id)) { 
    flash('Unit sedang disewa, tidak bisa diedit selama masa rental.'); 
    redirect('barang/list.php'); 
}

if ($nama === '' || $harga <= 0) { 
    flash('Nama dan harga sewa wajib diisi.'); 
    redirect("barang/edit.php?id=$id"); 
}

// >>> REVISI #4 #5 #7 [UBAH] kategori, status, ganti foto (blok sampai tanda AKHIR)
$kat = $_POST['kategori'] ?? ''; 
if (!in_array($kat, daftar_kategori(), true)) { 
    flash('Kategori tidak valid.'); 
    redirect("barang/edit.php?id=$id"); 
}

[$foto, $err] = simpan_foto($_FILES['foto'] ?? null); 
if ($err) { 
    flash($err); 
    redirect("barang/edit.php?id=$id"); 
}

$st = in_array($_POST['status'] ?? '', ['Available', 'Maintenance'], true) ? $_POST['status'] : null; // 'Rented' hanya diatur otomatis

$lama = $pdo->prepare('SELECT foto_nama FROM barang WHERE id=?'); 
$lama->execute([$id]); 
$fotoLama = $lama->fetchColumn();

$pdo->prepare('UPDATE barang SET nama=?, kategori=?, harga=?, deposit=?, spesifikasi=?, kondisi=?, status=COALESCE(?,status), foto_nama=COALESCE(?,foto_nama), foto_url=COALESCE(?,foto_url) WHERE id=?')
    ->execute([
        $nama, 
        $kat, 
        $harga, 
        (int)($_POST['deposit'] ?? 0), 
        trim($_POST['spesifikasi'] ?? ''), 
        trim($_POST['kondisi'] ?? ''), 
        $st, 
        $foto, 
        $foto ? 'gambar/asli/' . $foto : null, 
        $id
    ]);

if ($foto && $fotoLama) { 
    hapus_foto($fotoLama); 
}
// <<< AKHIR REVISI

flash('Unit diperbarui.'); 
redirect('barang/list.php');