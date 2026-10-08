<?php 
require_once __DIR__ . '/../includes/auth.php'; 
csrf_cek();

$s = $pdo->prepare('SELECT * FROM users WHERE email=?'); 
$s->execute([trim($_POST['email'] ?? '')]); 
$u = $s->fetch();

if (!$u || !password_verify($_POST['password'] ?? '', $u['password'])) { 
    flash('Email atau kata sandi salah.'); 
    redirect('auth/login.php'); 
}

session_regenerate_id(true);

$_SESSION['user'] = [
    'id'   => $u['id'], 
    'nama' => $u['nama'], 
    'role' => $u['role']
];

redirect(['staff' => 'reservasi/list.php', 'owner' => 'laporan/index.php'][$u['role']] ?? 'index.php');