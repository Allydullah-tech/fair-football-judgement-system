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

$required = ['full_name', 'email', 'password', 'role', 'security_question', 'security_answer'];
$error    = validateRequired($required, $data);
if ($error) sendError($error);

$fullName         = sanitize($data['full_name']);
$email            = sanitize($data['email']);
$password         = $data['password'];
$role             = sanitize($data['role']);
$securityQuestion = sanitize($data['security_question']);
$securityAnswer   = strtolower(trim($data['security_answer']));

if (!validateEmail($email))           sendError('Invalid email address.');
if (!validateRole($role))             sendError('Invalid role selected.');
if (!validateMinLength($password, 6)) sendError('Password must be at least 6 characters.');

$db   = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) sendError('An account with this email already exists.');

$hashedPassword = hashPassword($password);
$hashedAnswer   = hashPassword($securityAnswer);

$stmt = $db->prepare("
    INSERT INTO users (full_name, email, password, role, security_question, security_answer)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$fullName, $email, $hashedPassword, $role, $securityQuestion, $hashedAnswer]);

$userId = $db->lastInsertId();
logActivity($userId, "New user registered: {$fullName} as {$role}");

sendSuccess(null, 'Account created successfully.');