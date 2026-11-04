<?php 
/* >>> REVISI [FILE BARU] #13 edit profil (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
$u = wajib_login(); 
csrf_cek();

$n = trim($_POST['nama'] ?? ''); 
$e = trim($_POST['email'] ?? ''); 
$h = trim($_POST['hp'] ?? ''); 
$lama = $_POST['sandi_lama'] ?? ''; 
$baru = $_POST['sandi_baru'] ?? '';

if ($n === '' || !filter_var($e, FILTER_VALIDATE_EMAIL)) { 
    flash('Nama dan email wajib diisi dengan benar.'); 
    redirect('auth/profil.php'); 
}

$c = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>?'); 
$c->execute([$e, $u['id']]);

if ($c->fetch()) { 
    flash('Email sudah dipakai akun lain.'); 
    redirect('auth/profil.php'); 
}

if ($baru !== '') {
    $p = $pdo->prepare('SELECT password FROM users WHERE id=?'); 
    $p->execute([$u['id']]);
    
    if (strlen($baru) < 6 || !password_verify($lama, $p->fetchColumn())) { 
        flash('Sandi lama salah atau sandi baru kurang dari 6 karakter.'); 
        redirect('auth/profil.php'); 
    }
    
    $pdo->prepare('UPDATE users SET password=? WHERE id=?')
        ->execute([password_hash($baru, PASSWORD_DEFAULT), $u['id']]);
}

$pdo->prepare('UPDATE users SET nama=?, email=?, hp=? WHERE id=?')
    ->execute([$n, $e, $h, $u['id']]);
    
$_SESSION['user']['nama'] = $n; 
flash('Profil diperbarui.'); 
redirect('auth/profil.php');