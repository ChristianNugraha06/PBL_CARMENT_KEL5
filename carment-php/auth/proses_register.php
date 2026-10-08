<?php 
require_once __DIR__ . '/../includes/auth.php'; 
csrf_cek();

$n = trim($_POST['nama'] ?? ''); 
$e = trim($_POST['email'] ?? ''); 
$h = trim($_POST['hp'] ?? ''); 
$p = $_POST['password'] ?? '';

if ($n === '' || !filter_var($e, FILTER_VALIDATE_EMAIL) || strlen($p) < 6) { 
    flash('Lengkapi data dengan benar (sandi min. 6 karakter).'); 
    redirect('auth/register.php'); 
}

$c = $pdo->prepare('SELECT id FROM users WHERE email=?'); 
$c->execute([$e]);

if ($c->fetch()) { 
    flash('Email sudah terdaftar.'); 
    redirect('auth/register.php'); 
}

$pdo->prepare("INSERT INTO users(nama,email,hp,password,role) VALUES(?,?,?,?, 'customer')")
    ->execute([$n, $e, $h, password_hash($p, PASSWORD_DEFAULT)]);

flash('Akun dibuat. Silakan masuk.'); 
redirect('auth/login.php');