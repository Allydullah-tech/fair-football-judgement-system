<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    $action = sanitize($_GET['action'] ?? 'all');

    if ($action === 'all') {
        $stmt = $db->query("
            SELECT t.id, t.name, t.total_players AS players, t.status,
                   COALESCE(u.full_name, '—') AS manager
            FROM teams t
            LEFT JOIN users u ON u.id = t.manager_id
            ORDER BY t.created_at DESC
        ");
        sendSuccess($stmt->fetchAll());
    }

    if ($action === 'single') {
        $id   = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("
            SELECT t.id, t.name, t.total_players AS players, t.status,
                   COALESCE(u.full_name, '—') AS manager
            FROM teams t
            LEFT JOIN users u ON u.id = t.manager_id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $team = $stmt->fetch();
        if (!$team) sendNotFound('Team not found.');
        sendSuccess($team);
    }

    // Get players for a specific team
    if ($action === 'players') {
        $teamId = (int)($_GET['team_id'] ?? 0);
        if (!$teamId) sendError('Team ID required.');
        $stmt = $db->prepare("
            SELECT id, name, jersey_number, position, status
            FROM players
            WHERE team_id = ?
            ORDER BY jersey_number ASC
        ");
        $stmt->execute([$teamId]);
        sendSuccess($stmt->fetchAll());
    }

    sendError('Invalid action.');
}

if ($method === 'POST') {
    $data   = getRequestBody();
    $action = sanitize($data['action'] ?? '');

    if ($action === 'add') {
        $error = validateRequired(['name'], $data);
        if ($error) sendError($error);

        $name    = sanitize($data['name']);
        $players = (int)($data['players'] ?? 0);

        $stmt = $db->prepare("SELECT id FROM teams WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) sendError('A team with this name already exists.');

        $stmt = $db->prepare("INSERT INTO teams (name, total_players) VALUES (?, ?)");
        $stmt->execute([$name, $players]);
        $teamId = $db->lastInsertId();

        logActivity($_SESSION['user_id'], "Team added: {$name}");
        sendSuccess(['id' => $teamId], 'Team added successfully.');
    }

    if ($action === 'edit') {
        $error = validateRequired(['id', 'name'], $data);
        if ($error) sendError($error);

        $id      = (int)$data['id'];
        $name    = sanitize($data['name']);
        $players = (int)($data['players'] ?? 0);
        $status  = sanitize($data['status'] ?? 'active');

        $stmt = $db->prepare("UPDATE teams SET name = ?, total_players = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $players, $status, $id]);

        logActivity($_SESSION['user_id'], "Team updated: {$name}");
        sendSuccess(null, 'Team updated successfully.');
    }

    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) sendError('Invalid team ID.');

        $stmt = $db->prepare("SELECT name FROM teams WHERE id = ?");
        $stmt->execute([$id]);
        $team = $stmt->fetch();
        if (!$team) sendNotFound('Team not found.');

        $stmt = $db->prepare("DELETE FROM teams WHERE id = ?");
        $stmt->execute([$id]);

        logActivity($_SESSION['user_id'], "Team deleted: {$team['name']}");
        sendSuccess(null, 'Team deleted successfully.');
    }

    sendError('Invalid action.');
}

sendError('Method not allowed.', 405);