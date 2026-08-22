<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireManager();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();
$mgrId  = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT id, name, total_players FROM teams WHERE manager_id = ?");
$stmt->execute([$mgrId]);
$team = $stmt->fetch();

if ($method === 'GET') {
    $action = sanitize($_GET['action'] ?? 'list');

    if ($action === 'list') {
        if (!$team) sendSuccess([], 'No team registered.');
        $stmt = $db->prepare("
            SELECT id, name, jersey_number, position, status
            FROM players WHERE team_id = ?
            ORDER BY jersey_number ASC
        ");
        $stmt->execute([$team['id']]);
        sendSuccess($stmt->fetchAll());
    }

    // Get team info including player count
    if ($action === 'team_info') {
        if (!$team) sendSuccess(null, 'No team registered.');
        $stmt = $db->prepare("SELECT COUNT(*) FROM players WHERE team_id = ? AND status = 'active'");
        $stmt->execute([$team['id']]);
        $currentCount = $stmt->fetchColumn();
        sendSuccess([
            'id'             => $team['id'],
            'name'           => $team['name'],
            'total_players'  => $team['total_players'],
            'current_count'  => $currentCount,
            'slots_left'     => max(0, $team['total_players'] - $currentCount),
        ]);
    }

    sendError('Invalid action.');
}

if ($method === 'POST') {
    if (!$team) sendError('You must register a team first.');

    $data   = getRequestBody();
    $action = sanitize($data['action'] ?? '');

    if ($action === 'add') {
        $error = validateRequired(['name', 'jersey_number', 'position'], $data);
        if ($error) sendError($error);

        $name     = sanitize($data['name']);
        $jersey   = (int)$data['jersey_number'];
        $position = sanitize($data['position']);
        $teamId   = $team['id'];

        // Check player count does not exceed team size
        $stmt = $db->prepare("SELECT COUNT(*) FROM players WHERE team_id = ? AND status = 'active'");
        $stmt->execute([$teamId]);
        $currentCount = (int)$stmt->fetchColumn();

        if ($currentCount >= $team['total_players']) {
            sendError("Cannot add more players. Your team is full ({$team['total_players']} players maximum).");
        }

        // Check jersey number not already taken
        $stmt = $db->prepare("SELECT id FROM players WHERE team_id = ? AND jersey_number = ?");
        $stmt->execute([$teamId, $jersey]);
        if ($stmt->fetch()) sendError("Jersey number {$jersey} is already taken in your team.");

        $stmt = $db->prepare("INSERT INTO players (team_id, name, jersey_number, position) VALUES (?, ?, ?, ?)");
        $stmt->execute([$teamId, $name, $jersey, $position]);

        logActivity($mgrId, "Player registered: {$name} (#{$jersey})");
        sendSuccess(null, 'Player registered successfully.');
    }

    if ($action === 'edit') {
        $error = validateRequired(['id', 'name', 'jersey_number', 'position'], $data);
        if ($error) sendError($error);

        $id       = (int)$data['id'];
        $name     = sanitize($data['name']);
        $jersey   = (int)$data['jersey_number'];
        $position = sanitize($data['position']);

        // Check jersey not taken by another player
        $stmt = $db->prepare("SELECT id FROM players WHERE team_id = ? AND jersey_number = ? AND id != ?");
        $stmt->execute([$team['id'], $jersey, $id]);
        if ($stmt->fetch()) sendError("Jersey number {$jersey} is already taken.");

        $stmt = $db->prepare("UPDATE players SET name = ?, jersey_number = ?, position = ? WHERE id = ? AND team_id = ?");
        $stmt->execute([$name, $jersey, $position, $id, $team['id']]);

        logActivity($mgrId, "Player updated: {$name} (#{$jersey})");
        sendSuccess(null, 'Player updated successfully.');
    }

    if ($action === 'delete') {
        $id   = (int)($data['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM players WHERE id = ? AND team_id = ?");
        $stmt->execute([$id, $team['id']]);
        logActivity($mgrId, "Player removed (ID: {$id})");
        sendSuccess(null, 'Player removed successfully.');
    }

    sendError('Invalid action.');
}

sendError('Method not allowed.', 405);