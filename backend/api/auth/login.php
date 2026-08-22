<?php
define('FFJS_ACCESS', true);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/validator.php';
require_once __DIR__ . '/../../helpers/utilities.php';
require_once __DIR__ . '/../../middleware/session.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$data = getRequestBody();

$error = validateRequired(['email', 'password'], $data);
if ($error) sendError($error);

$email    = sanitize($data['email']);
$password = $data['password'];

if (!validateEmail($email)) sendError('Invalid email address.');

$db   = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !verifyPassword($password, $user['password'])) {
    sendError('Invalid email or password.');
}

$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['full_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role']  = $user['role'];

logActivity($user['id'], "User logged in: {$user['full_name']}");

sendSuccess([
    'id'        => $user['id'],
    'full_name' => $user['full_name'],
    'email'     => $user['email'],
    'role'      => $user['role'],
], 'Login successful.');