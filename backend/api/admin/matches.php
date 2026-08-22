<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

$baseQuery = "
    SELECT
        m.id, m.match_date, m.venue, m.status,
        ta.name AS team_a,
        tb.name AS team_b,
        u.full_name AS referee,
        CASE
            WHEN r.id IS NOT NULL THEN CONCAT(r.score_a, ' - ', r.score_b)
            ELSE '— vs —'
        END AS score
    FROM matches m
    JOIN teams ta   ON ta.id = m.team_a_id
    JOIN teams tb   ON tb.id = m.team_b_id
    LEFT JOIN users u   ON u.id  = m.referee_id
    LEFT JOIN results r ON r.match_id = m.id
";

if ($method === 'GET') {
    $action = sanitize($_GET['action'] ?? 'all');

    if ($action === 'all') {
        $stmt    = $db->query($baseQuery . " ORDER BY m.match_date DESC");
        $matches = $stmt->fetchAll();
        foreach ($matches as &$m) $m['match_date'] = formatDate($m['match_date']);
        sendSuccess($matches);
    }

    if ($action === 'upcoming') {
        $stmt    = $db->query($baseQuery . " WHERE m.status = 'upcoming' ORDER BY m.match_date ASC");
        $matches = $stmt->fetchAll();
        foreach ($matches as &$m) $m['match_date'] = formatDate($m['match_date']);
        sendSuccess($matches);
    }

    if ($action === 'recent') {
        $limit = (int)($_GET['limit'] ?? 5);
        $stmt  = $db->prepare($baseQuery . " ORDER BY m.match_date DESC LIMIT ?");
        $stmt->execute([$limit]);
        $matches = $stmt->fetchAll();
        foreach ($matches as &$m) $m['match_date'] = formatDate($m['match_date']);
        sendSuccess($matches);
    }

    if ($action === 'assigned') {
        $userId = $_SESSION['user_id'];
        $stmt   = $db->prepare($baseQuery . " WHERE m.referee_id = ? ORDER BY m.match_date ASC");
        $stmt->execute([$userId]);
        $matches = $stmt->fetchAll();
        foreach ($matches as &$m) $m['match_date'] = formatDate($m['match_date']);
        sendSuccess($matches);
    }

    sendError('Invalid action.');
}

if ($method === 'POST') {
    requireAdmin();
    $data   = getRequestBody();
    $action = sanitize($data['action'] ?? '');

    if ($action === 'schedule') {
        $error = validateRequired(['team_a_id', 'team_b_id', 'match_date', 'venue'], $data);
        if ($error) sendError($error);

        $teamA     = (int)$data['team_a_id'];
        $teamB     = (int)$data['team_b_id'];
        $refId     = (int)($data['referee_id'] ?? 0) ?: null;
        $matchDate = sanitize($data['match_date']);
        $venue     = sanitize($data['venue']);

        if ($teamA === $teamB) sendError('A team cannot play against itself.');

        $stmt = $db->prepare("INSERT INTO matches (team_a_id, team_b_id, referee_id, match_date, venue) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$teamA, $teamB, $refId, $matchDate, $venue]);

        logActivity($_SESSION['user_id'], "Match scheduled on {$matchDate} at {$venue}");
        sendSuccess(null, 'Match scheduled successfully.');
    }

    if ($action === 'delete') {
        $id   = (int)($data['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM matches WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($_SESSION['user_id'], "Match deleted (ID: {$id})");
        sendSuccess(null, 'Match deleted successfully.');
    }

    sendError('Invalid action.');
}

sendError('Method not allowed.', 405);