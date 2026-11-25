<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
// auto-cancel reservasi lewat 10 menit 
// Batalkan otomatis reservasi yang melewati batas waktu pelunasan (10 menit)
$pdo->exec("UPDATE reservasi SET status='Dibatalkan' WHERE status='Menunggu Pembayaran' AND batas_bayar < NOW()");

// waktu perbaikan pembayaran habis -> tanggal dilepas, status Menunggu Refund (pelanggan masih bisa mengisi rekening refund)
$pdo->exec("UPDATE reservasi SET status='Menunggu Refund', batas_bayar=NULL WHERE status='Perlu Perbaikan' AND batas_bayar < NOW()");

// sesi divalidasi ke database, routing per peran, penjaga halaman 
const HALAMAN_UTAMA = ['customer' => 'index.php', 'staff' => 'reservasi/list.php', 'owner' => 'laporan/index.php']; // halaman utama tiap peran
function halaman_utama($role)
{
    return HALAMAN_UTAMA[$role] ?? 'auth/login.php';
}
function nama_peran($role)
{
    return ['customer' => 'pelanggan', 'staff' => 'staff', 'owner' => 'owner'][$role] ?? $role;
}
function current_user()
{ // identitas HANYA dari sesi server lalu dicocokkan ke database; id/peran dari input pengguna tidak pernah dipakai
    static $sudah = false, $hasil = null;
    if ($sudah) {
        return $hasil;
    }
    $sudah = true;
    global $pdo;
    $id = (int)($_SESSION['user']['id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    $s = $pdo->prepare('SELECT id, nama, role FROM users WHERE id=?');
    $s->execute([$id]);
    $r = $s->fetch();
    if (!$r) {
        unset($_SESSION['user']);
        return null;
    } // akun sudah dihapus: sesi tidak berlaku lagi
    return $hasil = $_SESSION['user'] = ['id' => (int)$r['id'], 'nama' => $r['nama'], 'role' => $r['role']]; // peran selalu mengikuti database
}
function wajib_login(array $roles = [])
{ // salah peran: dialihkan ke halaman utama akunnya sendiri
    $u = current_user();
    if (!$u) {
        redirect('auth/login.php');
    }
    if ($roles && !in_array($u['role'], $roles, true)) {
        flash('Halaman itu bukan untuk akun ' . nama_peran($u['role']) . '. Anda dialihkan ke halaman utama akun Anda.');
        redirect(halaman_utama($u['role']));
    }
    return $u;
}
function wajib_tamu()
{ // halaman masuk/daftar/pemulihan hanya untuk yang belum login
    if ($u = current_user()) {
        flash('Anda sudah masuk sebagai ' . $u['nama'] . '. Keluar dulu jika ingin berganti akun.');
        redirect(halaman_utama($u['role']));
    }
}
function tamu_atau_customer()
{ // katalog hanya untuk pengunjung dan pelanggan
    $u = current_user();
    if ($u && $u['role'] !== 'customer') {
        flash('Katalog sewa hanya untuk pelanggan. Anda dialihkan ke halaman utama akun Anda.');
        redirect(halaman_utama($u['role']));
    }
}