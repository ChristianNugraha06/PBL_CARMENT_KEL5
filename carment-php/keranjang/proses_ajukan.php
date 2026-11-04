<?php 
/* >>> REVISI [FILE BARU] #16 #17 #18 kunci booking + batas 10 menit (seluruh file ini baru) */ 
require_once __DIR__ . '/../includes/auth.php'; 
$u = wajib_login(['customer']); 
csrf_cek();

$s = $pdo->prepare('SELECT * FROM keranjang WHERE user_id=? ORDER BY barang_id, id'); 
$s->execute([$u['id']]); 
$items = $s->fetchAll(); 

if (!$items) { 
    flash('Keranjang kosong.'); 
    redirect('keranjang/list.php'); 
}

$ok = 0; 
$gagal = [];

try {
    $pdo->beginTransaction();
    
    foreach ($items as $k) { // urut per unit agar tidak deadlock
        $b = $pdo->prepare('SELECT * FROM barang WHERE id=? FOR UPDATE'); 
        $b->execute([$k['barang_id']]); 
        $brg = $b->fetch(); // KUNCI unit selama pengecekan
        
        $c = $pdo->prepare("SELECT COUNT(*) FROM reservasi WHERE barang_id=? AND status IN ('Menunggu Pembayaran', 'Menunggu', 'Dikonfirmasi', 'Dipinjam') AND tgl_mulai<=? AND tgl_selesai>=?");
        $c->execute([$k['barang_id'], $k['tgl_selesai'], $k['tgl_mulai']]);
        
        if (!$brg || $brg['status'] === 'Maintenance' || $k['tgl_mulai'] < date('Y-m-d') || $c->fetchColumn() > 0) { 
            $gagal[] = ($brg['nama'] ?? 'Unit') . " ({$k['tgl_mulai']})"; 
        } else { // tanggal langsung DIKUNCI (Menunggu Pembayaran) selama 10 menit
            $sewa = (hari($k['tgl_mulai'], $k['tgl_selesai']) + 1) * $brg['harga'];
            
            $pdo->prepare("INSERT INTO reservasi(user_id, barang_id, tgl_mulai, tgl_selesai, sewa, deposit, denda, status, batas_bayar) VALUES(?, ?, ?, ?, ?, ?, 0, 'Menunggu Pembayaran', NOW() + INTERVAL '" . (int)BATAS_BAYAR_MENIT . " minutes')")
                ->execute([$u['id'], $k['barang_id'], $k['tgl_mulai'], $k['tgl_selesai'], $sewa, $brg['deposit']]);
            
            $ok++;
        }
        
        $pdo->prepare('DELETE FROM keranjang WHERE id=?')->execute([$k['id']]);
    }
    
    $pdo->commit();
} catch (PDOException $e) { 
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); 
    }
    
    flash('Reservasi gagal diproses (tanggal mungkin baru saja diambil orang lain). Coba lagi.'); 
    redirect('keranjang/list.php'); 
}

flash(($ok ? "$ok reservasi dibuat dan tanggal dikunci. Lakukan pelunasan dalam " . BATAS_BAYAR_MENIT . ' menit. ' : '') . ($gagal ? 'Gagal (tanggal sudah diambil): ' . implode(', ', $gagal) . '.' : '')); 

redirect('reservasi/riwayat.php');