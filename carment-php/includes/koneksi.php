<?php
// >>> REVISI [BARU] buffer output + zona waktu (perbaikan bug redirect) (blok sampai tanda AKHIR)
ob_start(); // buffer: redirect() aman dipanggil setelah header.php
date_default_timezone_set('Asia/Jakarta');
// <<< AKHIR REVISI

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', '/'); // jalankan dengan: php -S localhost:8000 (jika di htdocs, isi '/carment/')

$DB = [
    'host' => 'localhost',
    'port' => '5432',
    'name' => 'carment',
    'user' => 'postgres',
    'pass' => 'Kapokmukapan'
]; // sesuaikan

try {
    $pdo = new PDO(
        "pgsql:host={$DB['host']};port={$DB['port']};dbname={$DB['name']}", 
        $DB['user'], 
        $DB['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // >>> REVISI [BARU] zona waktu PostgreSQL (baris di bawah ini)
    $pdo->exec("SET TIME ZONE 'Asia/Jakarta'");
} catch (PDOException $e) { 
    exit('Koneksi database gagal. Periksa includes/koneksi.php dan ekstensi pdo_pgsql.'); 
}