<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireLogin();

$db   = getDB();
$role = $_SESSION['user_role'];
$uid  = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

if ($role === 'administrator') {
    $teams    = $db->query("SELECT COUNT(*) FROM teams WHERE status = 'active'")->fetchColumn();
    $matches  = $db->query("SELECT COUNT(*) FROM matches")->fetchColumn();
    $referees = $db->query("SELECT COUNT(*) FROM users WHERE role = 'referee' AND status = 'active'")->fetchColumn();
    $prizes   = $db->query("SELECT COUNT(*) FROM prize_distribution WHERE status = 'approved'")->fetchColumn();
    sendSuccess(['teams' => $teams, 'matches' => $matches, 'referees' => $referees, 'prizes' => $prizes]);
}

if ($role === 'referee') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM matches WHERE referee_id = ?");
    $stmt->execute([$uid]);
    $assigned = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM results r JOIN matches m ON m.id = r.match_id WHERE m.referee_id = ? AND r.status = 'verified'");
    $stmt->execute([$uid]);
    $verified = $stmt->fetchColumn();

    sendSuccess(['assigned_matches' => $assigned, 'verified_results' => $verified]);
}

if ($role === 'manager') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM teams WHERE manager_id = ?");
    $stmt->execute([$uid]);
    $team = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM matches m JOIN teams t ON (m.team_a_id = t.id OR m.team_b_id = t.id) WHERE t.manager_id = ? AND m.status = 'upcoming'");
    $stmt->execute([$uid]);
    $fixtures = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM prize_distribution pd JOIN teams t ON t.id = pd.team_id WHERE t.manager_id = ? AND pd.status = 'approved'");
    $stmt->execute([$uid]);
    $awards = $stmt->fetchColumn();

    sendSuccess(['team' => $team, 'fixtures' => $fixtures, 'awards' => $awards]);
}

sendError('Unknown role.');