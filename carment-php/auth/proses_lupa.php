<?php 
/* >>> REVISI [FILE BARU] #12 pemulihan akun (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
csrf_cek();

$e = trim($_POST['email'] ?? ''); 
$s = $pdo->prepare('SELECT id FROM users WHERE email=?'); 
$s->execute([$e]); 
$u = $s->fetch();

if ($u) {
    $token = bin2hex(random_bytes(32)); // yang disimpan hanya hash-nya
    
    $pdo->prepare("INSERT INTO reset_password(user_id,token_hash,expires_at) VALUES(?,?, NOW() + INTERVAL '30 minutes')")
        ->execute([$u['id'], hash('sha256', $token)]);
        
    if (MODE_DEMO_EMAIL) { 
        flash('Mode demo (tanpa SMTP): Anda langsung diarahkan ke halaman atur ulang sandi.'); 
        redirect('auth/reset.php?token=' . $token); 
    }
    
    $link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . 'auth/reset.php?token=' . $token;
    
    @mail($e, 'Pemulihan akun CARMENT', "Buka tautan berikut untuk mengatur ulang kata sandi (berlaku 30 menit):\n$link");
}

flash('Jika email terdaftar, tautan pemulihan telah dikirim.'); 
redirect('auth/login.php');