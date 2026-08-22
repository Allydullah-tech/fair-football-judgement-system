<?php
if (!defined('FFJS_ACCESS')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/utilities.php';
require_once __DIR__ . '/../middleware/session.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_once __DIR__ . '/../middleware/role_check.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}