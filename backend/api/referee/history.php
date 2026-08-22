<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireReferee();

$db        = getDB();
$refereeId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->prepare("
    SELECT r.id, r.status, r.submitted_at,
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
$stmt->execute([$refereeId]);
$history = $stmt->fetchAll();
foreach ($history as &$h) $h['match_date'] = formatDate($h['match_date']);
sendSuccess($history);