<?php 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

// >>> REVISI #4 #8 [UBAH] tolak hapus saat disewa + hapus file foto (blok sampai tanda AKHIR)
$id = (int)($_POST['id'] ?? 0);

if (sedang_disewa($id)) { 
    flash('Unit sedang disewa dan tidak bisa dihapus selama masa rental.'); 
    redirect('barang/list.php'); 
}

$f = $pdo->prepare('SELECT foto_nama FROM barang WHERE id=?'); 
$f->execute([$id]); 
$foto = $f->fetchColumn();

try { 
    $pdo->prepare('DELETE FROM barang WHERE id=?')->execute([$id]); 
    
    if ($foto) { 
        hapus_foto($foto); 
    }
    
    flash('Unit dihapus.'); 
} catch (PDOException $e) { 
    flash('Unit tidak bisa dihapus karena sudah punya riwayat reservasi/maintenance.'); 
}
// <<< AKHIR REVISI

redirect('barang/list.php');