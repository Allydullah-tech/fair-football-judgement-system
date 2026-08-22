<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    $stmt = $db->query("
        SELECT u.id, u.full_name AS name, u.email, u.status,
               COUNT(m.id) AS assigned_matches,
               '—' AS experience
        FROM users u
        LEFT JOIN matches m ON m.referee_id = u.id
        WHERE u.role = 'referee'
        GROUP BY u.id
        ORDER BY u.full_name
    ");
    sendSuccess($stmt->fetchAll());
}

if ($method === 'POST') {
    $data   = getRequestBody();
    $action = sanitize($data['action'] ?? '');

    if ($action === 'remove') {
        $id   = (int)($data['id'] ?? 0);
        $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE id = ? AND role = 'referee'");
        $stmt->execute([$id]);
        logActivity($_SESSION['user_id'], "Referee deactivated (ID: {$id})");
        sendSuccess(null, 'Referee removed successfully.');
    }

    sendError('Invalid action.');
}

sendError('Method not allowed.', 405);