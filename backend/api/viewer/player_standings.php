<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->query("
    SELECT
        p.id,
        p.name              AS player_name,
        p.jersey_number,
        p.position,
        t.name              AS team_name,
        SUM(ps.goals)                   AS total_goals,
        SUM(ps.assists)                 AS total_assists,
        SUM(ps.successful_passes)       AS total_passes,
        SUM(ps.yellow_cards)            AS total_yellow_cards,
        SUM(ps.red_cards)               AS total_red_cards,
        SUM(ps.total_points)            AS total_points,
        COUNT(ps.match_id)              AS matches_played
    FROM players p
    JOIN teams t ON t.id = p.team_id
    JOIN player_statistics ps ON ps.player_id = p.id
    GROUP BY p.id, p.name, p.jersey_number, p.position, t.name
    ORDER BY total_points DESC, total_goals DESC
");
sendSuccess($stmt->fetchAll());