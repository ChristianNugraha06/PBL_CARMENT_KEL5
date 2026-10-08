<?php
const DENDA_PERSEN = 0.5; // denda/hari = 50% harga sewa (konfirmasi ke Owner)

function e($s) { 
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); 
}

function rp($n) { 
    return 'Rp' . number_format((float)$n, 0, ',', '.'); 
}

function redirect($p) { 
    header('Location: ' . BASE_URL . $p); 
    exit; 
}

function flash($m = null) { 
    if ($m !== null) { 
        $_SESSION['flash'] = $m; 
        return; 
    } 
    $f = $_SESSION['flash'] ?? ''; 
    unset($_SESSION['flash']); 
    return $f; 
}

function badge($s) { 
    return '<span class="badge ' . e($s) . '">' . e($s) . '</span>'; 
}

function hari($a, $b) { 
    return (int)round((strtotime($b) - strtotime($a)) / 86400); 
}

function tgl_valid($d) { 
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false; 
}