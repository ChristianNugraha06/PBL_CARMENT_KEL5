<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';

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