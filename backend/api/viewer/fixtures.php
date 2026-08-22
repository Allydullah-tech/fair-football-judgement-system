<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->query("
    SELECT m.id, m.match_date, m.venue, m.status,
           ta.name AS team_a, tb.name AS team_b
    FROM matches m
    JOIN teams ta ON ta.id = m.team_a_id
    JOIN teams tb ON tb.id = m.team_b_id
    WHERE m.status = 'upcoming'
    ORDER BY m.match_date ASC
");
$fixtures = $stmt->fetchAll();
foreach ($fixtures as &$f) $f['match_date'] = formatDate($f['match_date']);
sendSuccess($fixtures);