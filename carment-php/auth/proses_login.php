<?php
require_once __DIR__ . '/../includes/auth.php'; /* sudah login: dialihkan ke halaman utama */
wajib_tamu();
csrf_cek();
$s = $pdo->prepare('SELECT * FROM users WHERE email=?');
$s->execute([trim($_POST['email'] ?? '')]);
$u = $s->fetch();
if (!$u || !password_verify($_POST['password'] ?? '', $u['password'])) {
    flash('Email atau kata sandi salah.');
    redirect('auth/login.php');
}
session_regenerate_id(true);
$_SESSION['user'] = ['id' => $u['id'], 'nama' => $u['nama'], 'role' => $u['role']];
redirect(halaman_utama($u['role'])); // R5 #1: routing per peran dari satu tempat