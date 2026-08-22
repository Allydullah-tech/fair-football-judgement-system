<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireManager();

$db    = getDB();
$mgrId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->prepare("SELECT id FROM teams WHERE manager_id = ?");
$stmt->execute([$mgrId]);
$team = $stmt->fetch();

if (!$team) sendSuccess([], 'No team registered.');

$teamId = $team['id'];

$stmt = $db->prepare("
    SELECT m.id, m.match_date, m.venue, m.status,
           ta.name AS team_a, tb.name AS team_b
    FROM matches m
    JOIN teams ta ON ta.id = m.team_a_id
    JOIN teams tb ON tb.id = m.team_b_id
    WHERE (m.team_a_id = ? OR m.team_b_id = ?)
    AND m.status = 'upcoming'
    ORDER BY m.match_date ASC
");
$stmt->execute([$teamId, $teamId]);
$fixtures = $stmt->fetchAll();
foreach ($fixtures as &$f) $f['match_date'] = formatDate($f['match_date']);
sendSuccess($fixtures);