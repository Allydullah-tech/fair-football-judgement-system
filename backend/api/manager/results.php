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
    SELECT CONCAT(r.score_a, ' - ', r.score_b) AS score,
           r.status, ta.name AS team_a, tb.name AS team_b
    FROM results r
    JOIN matches m  ON m.id  = r.match_id
    JOIN teams ta   ON ta.id = m.team_a_id
    JOIN teams tb   ON tb.id = m.team_b_id
    WHERE (m.team_a_id = ? OR m.team_b_id = ?)
    ORDER BY r.submitted_at DESC
");
$stmt->execute([$teamId, $teamId]);
sendSuccess($stmt->fetchAll());