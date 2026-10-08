<?php
function csrf_token() { 
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32)); 
    }
    return $_SESSION['csrf']; 
}

function csrf_field() { 
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; 
}

function csrf_cek() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { 
        http_response_code(400); 
        exit('Permintaan tidak valid (CSRF).'); 
    }
}