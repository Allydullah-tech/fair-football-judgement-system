<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireManager();

$db    = getDB();
$mgrId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->prepare("SELECT id, name FROM teams WHERE manager_id = ?");
$stmt->execute([$mgrId]);
$team = $stmt->fetch();

if (!$team) sendSuccess([], 'No team registered.');

$teamId = $team['id'];

// Get prizes for the team directly OR for players of this team
$stmt = $db->prepare("
    SELECT p.title AS prize, pd.winner_name AS winner,
           pd.winner_type, pd.status, pd.awarded_at
    FROM prize_distribution pd
    JOIN prizes p ON p.id = pd.prize_id
    WHERE pd.team_id = ?
    AND pd.status = 'approved'

    UNION

    SELECT p.title AS prize, pd.winner_name AS winner,
           pd.winner_type, pd.status, pd.awarded_at
    FROM prize_distribution pd
    JOIN prizes p ON p.id = pd.prize_id
    JOIN players pl ON LOWER(pl.name) = LOWER(pd.winner_name)
    WHERE pl.team_id = ?
    AND pd.winner_type = 'player'
    AND pd.status = 'approved'

    ORDER BY awarded_at DESC
");
$stmt->execute([$teamId, $teamId]);
sendSuccess($stmt->fetchAll());