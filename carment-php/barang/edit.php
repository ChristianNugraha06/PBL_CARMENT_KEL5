<?php 
$judul = 'Edit unit'; 
require __DIR__ . '/../includes/header.php'; 
wajib_login(['staff']);

$s = $pdo->prepare('SELECT * FROM barang WHERE id=?'); 
$s->execute([(int)($_GET['id'] ?? 0)]); 
$b = $s->fetch();

// >>> REVISI #8 [UBAH] tolak edit saat unit disewa (baris di bawah ini)
if (!$b) { 
    redirect('barang/list.php'); 
} 

if (sedang_disewa($b['id'])) { 
    flash('Unit sedang disewa, tidak bisa diedit selama masa rental.'); 
    redirect('barang/list.php'); 
} 

$aksi = 'proses_edit.php'; 
?>

<h1>Edit unit</h1>

<?php 
require __DIR__ . '/_form.php'; 
require __DIR__ . '/../includes/footer.php'; 
?>