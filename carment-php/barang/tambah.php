<?php 
$judul = 'Tambah unit'; 
require __DIR__ . '/../includes/header.php'; 
wajib_login(['staff']);

$b = []; 
$aksi = 'proses_tambah.php'; 
?>

<h1>Tambah unit</h1>

<?php 
require __DIR__ . '/_form.php'; 
require __DIR__ . '/../includes/footer.php'; 
?>