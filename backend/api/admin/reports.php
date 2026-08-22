<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = sanitize($_GET['action'] ?? 'activities');

    if ($action === 'activities') {
        $stmt = $db->query("
            SELECT a.activity, a.status,
                   DATE_FORMAT(a.created_at, '%d %b %Y') AS date
            FROM activity_log a
            ORDER BY a.created_at DESC
            LIMIT 20
        ");
        sendSuccess($stmt->fetchAll());
    }

    sendError('Invalid action.');
}

sendError('Method not allowed.', 405);