<?php
if (!defined('FFJS_ACCESS')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed.']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_name('ffjs_session');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}