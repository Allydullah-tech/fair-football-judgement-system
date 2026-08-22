<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->query("
    SELECT pd.winner_name AS winner, p.title AS prize,
           p.criteria, pd.status, pd.winner_type
    FROM prize_distribution pd
    JOIN prizes p ON p.id = pd.prize_id
    WHERE pd.status = 'approved'
    ORDER BY pd.awarded_at DESC
");
sendSuccess($stmt->fetchAll());