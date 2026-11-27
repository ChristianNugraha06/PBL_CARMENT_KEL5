<?php
// buffer output + zona waktu (perbaikan bug redirect)
ob_start();
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('BASE_URL', '/'); // jalankan lokal dengan: php -S localhost:8000

// 1) lokal: kredensial dari config.local.php (tidak ikut git)
if (is_file(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// 2) server (Render): kredensial dari environment variable
$bawaan = [
    'DB_HOST'    => 'localhost',
    'DB_PORT'    => '5432',
    'DB_NAME'    => 'carment',
    'DB_USER'    => 'postgres',
    'DB_PASS'    => null,
    'DB_SSLMODE' => 'prefer',
];
foreach ($bawaan as $k => $def) {
    if (defined($k)) {
        continue;
    }
    $v = getenv($k);
    if ($v !== false && $v !== '') {
        define($k, $v);
    } elseif ($def !== null) {
        define($k, $def);
    }
}
if (!defined('DB_PASS')) {
    exit('Kredensial database belum diatur (config.local.php atau environment variable DB_*).');
}

try {
    $pdo = new PDO(
        'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';sslmode=' . DB_SSLMODE,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    $pdo->exec("SET TIME ZONE 'Asia/Jakarta'");
} catch (PDOException $e) {
    error_log('DB gagal: ' . $e->getMessage()); // detail masuk log Render, bukan ke layar
    exit('Koneksi database gagal. Periksa pengaturan database dan ekstensi pdo_pgsql.');
}