<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireManager();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();
$mgrId  = $_SESSION['user_id'];

if ($method !== 'POST') sendError('Method not allowed.', 405);

$data   = getRequestBody();
$action = sanitize($data['action'] ?? 'register');

// Register new team
if ($action === 'register') {
    $error = validateRequired(['name', 'players'], $data);
    if ($error) sendError($error);

    $name    = sanitize($data['name']);
    $players = (int)$data['players'];

    $stmt = $db->prepare("SELECT id FROM teams WHERE manager_id = ?");
    $stmt->execute([$mgrId]);
    if ($stmt->fetch()) sendError('You have already registered a team.');

    $stmt = $db->prepare("SELECT id FROM teams WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) sendError('A team with this name already exists.');

    $stmt = $db->prepare("INSERT INTO teams (name, manager_id, total_players) VALUES (?, ?, ?)");
    $stmt->execute([$name, $mgrId, $players]);

    logActivity($mgrId, "Team registered: {$name}");
    sendSuccess(null, 'Team registered successfully.');
}

// Edit team
if ($action === 'edit') {
    $error = validateRequired(['id', 'name', 'players'], $data);
    if ($error) sendError($error);

    $id      = (int)$data['id'];
    $name    = sanitize($data['name']);
    $players = (int)$data['players'];

    // Verify this manager owns this team
    $stmt = $db->prepare("SELECT id FROM teams WHERE id = ? AND manager_id = ?");
    $stmt->execute([$id, $mgrId]);
    if (!$stmt->fetch()) sendError('Team not found or you do not have permission.');

    // Check current player count does not exceed new max
    $stmt = $db->prepare("SELECT COUNT(*) FROM players WHERE team_id = ? AND status = 'active'");
    $stmt->execute([$id]);
    $currentCount = (int)$stmt->fetchColumn();
    if ($players < $currentCount) {
        sendError("Cannot set max players to {$players}. You already have {$currentCount} registered players.");
    }

    $stmt = $db->prepare("UPDATE teams SET name = ?, total_players = ? WHERE id = ? AND manager_id = ?");
    $stmt->execute([$name, $players, $id, $mgrId]);

    logActivity($mgrId, "Team updated: {$name}");
    sendSuccess(null, 'Team updated successfully.');
}

sendError('Invalid action.');