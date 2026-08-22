<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireRole('administrator', 'referee');

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    $action = sanitize($_GET['action'] ?? 'all');

    if ($action === 'all') {
        $stmt = $db->query("
            SELECT r.id, r.score_a, r.score_b, r.status, r.notes,
                   CONCAT(r.score_a, ' - ', r.score_b) AS score,
                   ta.name AS team_a, tb.name AS team_b,
                   m.match_date, m.venue,
                   COALESCE(u.full_name, '—') AS referee
            FROM results r
            JOIN matches m  ON m.id  = r.match_id
            JOIN teams ta   ON ta.id = m.team_a_id
            JOIN teams tb   ON tb.id = m.team_b_id
            LEFT JOIN users u ON u.id = m.referee_id
            ORDER BY r.submitted_at DESC
        ");
        $results = $stmt->fetchAll();
        foreach ($results as &$r) $r['match_date'] = formatDate($r['match_date']);
        sendSuccess($results);
    }

    if ($action === 'history') {
        $userId = $_SESSION['user_id'];
        $stmt   = $db->prepare("
            SELECT r.id, r.status,
                   CONCAT(r.score_a, ' - ', r.score_b) AS score,
                   ta.name AS team_a, tb.name AS team_b,
                   m.match_date, m.venue
            FROM results r
            JOIN matches m  ON m.id  = r.match_id
            JOIN teams ta   ON ta.id = m.team_a_id
            JOIN teams tb   ON tb.id = m.team_b_id
            WHERE m.referee_id = ?
            ORDER BY r.submitted_at DESC
        ");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();
        foreach ($results as &$r) $r['match_date'] = formatDate($r['match_date']);
        sendSuccess($results);
    }

    sendError('Invalid action.');
}

if ($method === 'POST') {
    $data   = getRequestBody();
    $action = sanitize($data['action'] ?? '');

    if ($action === 'verify') {
        requireAdmin();
        $id   = (int)($data['id'] ?? 0);
        $stmt = $db->prepare("UPDATE results SET status = 'verified' WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($_SESSION['user_id'], "Result verified (ID: {$id})");
        sendSuccess(null, 'Result verified successfully.');
    }

    sendError('Invalid action.');
}

sendError('Method not allowed.', 405);