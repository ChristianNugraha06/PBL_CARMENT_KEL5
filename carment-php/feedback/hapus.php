<?php /* hapus feedback (staff & owner) */
require_once __DIR__ . '/../includes/auth.php';
wajib_login(['staff', 'owner']);
csrf_cek();
$pdo->prepare('DELETE FROM feedback WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
flash('Feedback dihapus.');
redirect('feedback/list.php');