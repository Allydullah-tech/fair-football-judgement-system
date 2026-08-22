<?php
define('FFJS_ACCESS', true);

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/utilities.php';
require_once __DIR__ . '/../../middleware/session.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], "User logged out: " . ($_SESSION['user_name'] ?? ''));
}

$_SESSION = [];
session_destroy();

sendSuccess(null, 'Logged out successfully.');