<?php 
/* >>> REVISI [FILE BARU] #17 (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
$u = wajib_login(['customer']); 
csrf_cek();

$pdo->prepare('DELETE FROM keranjang WHERE id=? AND user_id=?')->execute([(int)($_POST['id'] ?? 0), $u['id']]);

flash('Dihapus dari keranjang.'); 
redirect('keranjang/list.php');