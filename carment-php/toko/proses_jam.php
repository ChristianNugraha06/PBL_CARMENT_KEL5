<?php
/* simpan jadwal buka toko */
require_once __DIR__ . '/../includes/auth.php';
wajib_login(['staff']);
csrf_cek();

$libur = (array)($_POST['libur'] ?? []);
$buka = (array)($_POST['buka'] ?? []);
$tutup = (array)($_POST['tutup'] ?? []);
$pola = '/^([01]\d|2[0-3]):[0-5]\d$/';
$rows = [];

foreach (NAMA_HARI as $n => $nm) {
  if (isset($libur[$n])) {
    $rows[] = [$n, null, null];
    continue;
  }
  
  $b = (string)($buka[$n] ?? '');
  $t = (string)($tutup[$n] ?? '');
  
  if (!preg_match($pola, $b) || !preg_match($pola, $t) || $b >= $t) {
    flash("Jadwal $nm tidak valid: jam buka harus lebih awal dari jam tutup (format HH:MM). Tidak ada yang disimpan.");
    redirect('toko/jam.php');
  }
  
  $rows[] = [$n, $b, $t];
}

$pdo->beginTransaction();

$st = $pdo->prepare('INSERT INTO jam_toko (hari, buka, tutup) VALUES (?, ?, ?) ON CONFLICT (hari) DO UPDATE SET buka = EXCLUDED.buka, tutup = EXCLUDED.tutup');

foreach ($rows as $r) {
  $st->execute($r);
}

$pdo->commit();

flash('Jadwal toko diperbarui.');
redirect('toko/jam.php');