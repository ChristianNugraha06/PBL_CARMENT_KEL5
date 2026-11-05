<?php 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

$nama = trim($_POST['nama'] ?? ''); 
$harga = (int)($_POST['harga'] ?? 0);

if ($nama === '' || $harga <= 0) { 
    flash('Nama dan harga sewa wajib diisi.'); 
    redirect('barang/tambah.php'); 
}

// >>> REVISI #4 #5 #9 [UBAH] validasi kategori + simpan foto (blok sampai tanda AKHIR)
$kat = $_POST['kategori'] ?? ''; 
if (!in_array($kat, daftar_kategori(), true)) { 
    flash('Kategori tidak valid.'); 
    redirect('barang/tambah.php'); 
}

[$foto, $err] = simpan_foto($_FILES['foto'] ?? null); 
if ($err) { 
    flash($err); 
    redirect('barang/tambah.php'); 
}

$pdo->prepare("INSERT INTO barang(nama, kategori, harga, deposit, spesifikasi, kondisi, status, foto_nama, foto_url) VALUES(?, ?, ?, ?, ?, ?, 'Available', ?, ?)")
    ->execute([
        $nama, 
        $kat, 
        $harga, 
        (int)($_POST['deposit'] ?? 0), 
        trim($_POST['spesifikasi'] ?? ''), 
        trim($_POST['kondisi'] ?? 'Baik.'), 
        $foto, 
        $foto ? 'gambar/asli/' . $foto : null
    ]);
// <<< AKHIR REVISI

flash('Unit ditambahkan.'); 
redirect('barang/list.php');