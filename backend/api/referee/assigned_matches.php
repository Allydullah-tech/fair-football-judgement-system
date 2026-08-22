<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireReferee();

$db        = getDB();
$refereeId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->prepare("
    SELECT m.id, m.match_date, m.venue, m.status,
           ta.name AS team_a, tb.name AS team_b
    FROM matches m
    JOIN teams ta ON ta.id = m.team_a_id
    JOIN teams tb ON tb.id = m.team_b_id
    WHERE m.referee_id = ?
    ORDER BY m.match_date ASC
");
$stmt->execute([$refereeId]);
$matches = $stmt->fetchAll();
foreach ($matches as &$m) $m['match_date'] = formatDate($m['match_date']);
sendSuccess($matches);