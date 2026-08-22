<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->query("
    SELECT CONCAT(r.score_a, ' - ', r.score_b) AS score,
           r.status, ta.name AS team_a, tb.name AS team_b,
           m.match_date
    FROM results r
    JOIN matches m  ON m.id  = r.match_id
    JOIN teams ta   ON ta.id = m.team_a_id
    JOIN teams tb   ON tb.id = m.team_b_id
    WHERE r.status = 'verified'
    ORDER BY m.match_date DESC
");
$results = $stmt->fetchAll();
foreach ($results as &$r) $r['match_date'] = formatDate($r['match_date']);
sendSuccess($results);