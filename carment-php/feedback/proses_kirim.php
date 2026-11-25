<?php /* kirim feedback (pengunjung & pelanggan) */
require_once __DIR__ . '/../includes/auth.php';
tamu_atau_customer();
csrf_cek();
$balik = 'index.php#feedback';
$u = current_user();

if (!empty($_POST['situs'])) redirect($balik); // kolom jebakan (honeypot) untuk bot: diam-diam diabaikan
$rating = (int)($_POST['rating'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$pesan = trim($_POST['pesan'] ?? '');
if ($rating < 1 || $rating > 5 || panjang($nama) < 2 || panjang($nama) > 60 || panjang($pesan) < 5 || panjang($pesan) > 500) {
    $_SESSION['fb_msg'] = 'Lengkapi nama (min. 2 huruf), pilih rating 1-5, dan tulis pesan 5-500 karakter.';
    redirect($balik);
}
if (time() - (int)($_SESSION['fb_terakhir'] ?? 0) < 60) {
    $_SESSION['fb_msg'] = 'Mohon tunggu sebentar sebelum mengirim feedback lagi.';
    redirect($balik);
}
$pdo->prepare('INSERT INTO feedback(user_id,nama,rating,pesan) VALUES(?,?,?,?)')->execute([$u['id'] ?? null, $nama, $rating, $pesan]); // user_id hanya dari sesi, bukan dari input
$_SESSION['fb_terakhir'] = time();
$_SESSION['fb_msg'] = 'Terima kasih! Feedback Anda sudah kami terima.';
redirect($balik);