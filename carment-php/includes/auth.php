<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';

// auto-cancel reservasi lewat 10 menit 
// Batalkan otomatis reservasi yang melewati batas waktu pelunasan (10 menit)
$pdo->exec("UPDATE reservasi SET status='Dibatalkan' WHERE status='Menunggu Pembayaran' AND batas_bayar < NOW()");

function current_user() {
  return $_SESSION['user'] ?? null;
}

function wajib_login(array $roles = []) {
  $u = current_user();
  
  if (!$u) {
    redirect('auth/login.php');
  }
  
  if ($roles && !in_array($u['role'], $roles, true)) {
    flash('Akses ditolak untuk peran Anda.');
    redirect('index.php');
  }
  
  return $u;
}