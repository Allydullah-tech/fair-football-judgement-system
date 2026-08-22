<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    $rows = $stmt->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    sendSuccess([
        'system_name' => $settings['system_name'] ?? '',
        'admin_email' => $settings['admin_email'] ?? '',
        'season'      => $settings['season']      ?? '',
    ]);
}

if ($method === 'POST') {
    $data   = getRequestBody();
    $action = sanitize($data['action'] ?? '');

    if ($action === 'update') {
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach (['system_name', 'admin_email', 'season'] as $key) {
            if (isset($data[$key])) {
                $stmt->execute([sanitize($data[$key]), $key]);
            }
        }
        logActivity($_SESSION['user_id'], "System settings updated.");
        sendSuccess(null, 'Settings saved successfully.');
    }

    sendError('Invalid action.');
}

sendError('Method not allowed.', 405);