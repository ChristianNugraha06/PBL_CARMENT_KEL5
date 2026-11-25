<?php /* pengiriman email: SMTP lewat socket (STARTTLS/SSL + AUTH LOGIN) atau mail() bila SMTP_HOST kosong */
function kirim_email($ke, $subjek, $isi)
{
    $ke = trim((string)$ke);
    if (!filter_var($ke, FILTER_VALIDATE_EMAIL)) {
        return false; // sekaligus mencegah header injection
    }
    $dari = EMAIL_DARI;
    $subj = '=?UTF-8?B?' . base64_encode($subjek) . '?=';
    if (SMTP_HOST === '') {
        return @mail($ke, $subj, $isi, "From: CARMENT <$dari>\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8");
    }
    $hdr = "From: CARMENT <$dari>\r\nTo: <$ke>\r\nSubject: $subj\r\nDate: " . date('r') . "\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n";
    $fp = @stream_socket_client((SMTP_SECURE === 'ssl' ? 'ssl://' : 'tcp://') . SMTP_HOST . ':' . SMTP_PORT, $en, $es, 10);
    if (!$fp) {
        error_log("SMTP: tidak bisa terhubung ke " . SMTP_HOST . " ($es)");
        return false;
    }
    stream_set_timeout($fp, 10);
    $baca = function () use ($fp) {
        $r = '';
        while (($l = fgets($fp, 515)) !== false) {
            $r .= $l;
            if (strlen($l) < 4 || $l[3] === ' ') {
                break;
            }
        }
        return $r;
    };
    $cmd = function ($c, $harap) use ($fp, $baca) {
        if ($c !== null) {
            fwrite($fp, $c . "\r\n");
        }
        $r = $baca();
        if (strncmp($r, (string)$harap, 3) !== 0) {
            throw new Exception("SMTP menjawab '" . trim($r) . "' (diharapkan $harap)");
        }
        return $r;
    };
    try {
        $host = preg_replace('/[^A-Za-z0-9.\-]/', '', $_SERVER['SERVER_NAME'] ?? 'localhost') ?: 'localhost';
        $cmd(null, 220);
        $cmd("EHLO $host", 250);
        if (SMTP_SECURE === 'tls') {
            $cmd('STARTTLS', 220);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('gagal STARTTLS');
            }
            $cmd("EHLO $host", 250);
        }
        if (SMTP_USER !== '') {
            $cmd('AUTH LOGIN', 334);
            $cmd(base64_encode(SMTP_USER), 334);
            $cmd(base64_encode(SMTP_PASS), 235);
        }
        $cmd("MAIL FROM:<$dari>", 250);
        $cmd("RCPT TO:<$ke>", 250);
        $cmd('DATA', 354);
        fwrite($fp, $hdr . "\r\n" . chunk_split(base64_encode($isi)) . "\r\n.\r\n");
        $r = $baca();
        if (strncmp($r, '250', 3) !== 0) {
            throw new Exception('DATA ditolak: ' . trim($r));
        }
        @fwrite($fp, "QUIT\r\n");
        fclose($fp);
        return true;
    } catch (Exception $e) {
        error_log('SMTP gagal: ' . $e->getMessage());
        @fclose($fp);
        return false;
    }
}