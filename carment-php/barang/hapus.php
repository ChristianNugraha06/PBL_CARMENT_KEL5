<?php 
require_once __DIR__ . '/../includes/auth.php'; 
wajib_login(['staff']); 
csrf_cek();

try { 
    $pdo->prepare('DELETE FROM barang WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]); 
    flash('Unit dihapus.'); 
} catch (PDOException $e) { 
    flash('Unit tidak bisa dihapus karena sudah punya riwayat reservasi/maintenance.'); 
}

redirect('barang/list.php');