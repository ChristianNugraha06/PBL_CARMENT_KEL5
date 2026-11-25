<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_login(['staff']);
csrf_cek();
try {
    $pdo->prepare('DELETE FROM kategori WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
    flash('Kategori dihapus.');
} catch (PDOException $e) {
    flash('Kategori tidak bisa dihapus karena masih dipakai unit. Pindahkan unitnya dulu.');
}
redirect('kategori/list.php');