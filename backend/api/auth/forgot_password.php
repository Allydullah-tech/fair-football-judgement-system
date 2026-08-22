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

$data   = getRequestBody();
$action = sanitize($data['action'] ?? '');
$db     = getDB();

// ── STEP 1 ───────────────────────────────────
if ($action === 'forgot_step1') {
    $error = validateRequired(['role', 'email'], $data);
    if ($error) sendError($error);

    $role  = sanitize($data['role']);
    $email = sanitize($data['email']);

    if (!validateEmail($email)) sendError('Invalid email address.');
    if (!validateRole($role))   sendError('Invalid role.');

    $stmt = $db->prepare("
        SELECT id, security_question FROM users
        WHERE email = ? AND role = ? AND status = 'active'
    ");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if (!$user) sendError('No account found with that role and email.');

    sendSuccess(['security_question' => $user['security_question']], 'Account found.');
}

// ── STEP 2 ───────────────────────────────────
if ($action === 'forgot_step2') {
    $error = validateRequired(['email', 'role', 'security_answer'], $data);
    if ($error) sendError($error);

    $email  = sanitize($data['email']);
    $role   = sanitize($data['role']);
    $answer = strtolower(trim($data['security_answer']));

    $stmt = $db->prepare("
        SELECT id, security_answer FROM users
        WHERE email = ? AND role = ? AND status = 'active'
    ");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if (!$user) sendError('Account not found.');

    if (!verifyPassword($answer, $user['security_answer'])) {
        sendError('Incorrect answer. Please try again.');
    }

    $token  = generateToken();
    $expiry = date('Y-m-d H:i:s', time() + RESET_TOKEN_EXPIRY);

    $stmt = $db->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
    $stmt->execute([$token, $expiry, $user['id']]);

    sendSuccess(['reset_token' => $token], 'Answer verified.');
}

// ── STEP 3 ───────────────────────────────────
if ($action === 'forgot_step3') {
    $error = validateRequired(['email', 'role', 'reset_token', 'new_password'], $data);
    if ($error) sendError($error);

    $email       = sanitize($data['email']);
    $role        = sanitize($data['role']);
    $token       = sanitize($data['reset_token']);
    $newPassword = $data['new_password'];

    if (!validateMinLength($newPassword, 6)) sendError('Password must be at least 6 characters.');

    $stmt = $db->prepare("
        SELECT id, reset_token, token_expiry FROM users
        WHERE email = ? AND role = ? AND status = 'active'
    ");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if (!$user)                                sendError('Account not found.');
    if ($user['reset_token'] !== $token)       sendError('Invalid reset token.');
    if (isTokenExpired($user['token_expiry'])) sendError('Reset token has expired. Please start again.');

    $hashed = hashPassword($newPassword);
    $stmt   = $db->prepare("
        UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?
    ");
    $stmt->execute([$hashed, $user['id']]);

    logActivity($user['id'], "Password reset successfully.");
    sendSuccess(null, 'Password reset successfully.');
}

sendError('Invalid action.');