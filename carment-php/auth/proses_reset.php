<?php 
/* >>> REVISI [FILE BARU] #12 pemulihan akun (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
csrf_cek();

$token = $_POST['token'] ?? ''; 
$p = $_POST['password'] ?? '';

if (strlen($p) < 6) { 
    flash('Kata sandi minimal 6 karakter.'); 
    redirect('auth/reset.php?token=' . urlencode($token)); 
}

$s = $pdo->prepare('SELECT id, user_id FROM reset_password WHERE token_hash=? AND dipakai=FALSE AND expires_at > NOW()'); 
$s->execute([hash('sha256', $token)]); 
$r = $s->fetch();

if (!$r) { 
    flash('Tautan pemulihan tidak valid atau sudah kedaluwarsa.'); 
    redirect('auth/lupa.php'); 
}

$pdo->prepare('UPDATE users SET password=? WHERE id=?')
    ->execute([password_hash($p, PASSWORD_DEFAULT), $r['user_id']]);
    
$pdo->prepare('UPDATE reset_password SET dipakai=TRUE WHERE id=?')
    ->execute([$r['id']]);
    
flash('Kata sandi diubah. Silakan masuk.'); 
redirect('auth/login.php');