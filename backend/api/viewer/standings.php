<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Method not allowed.', 405);

$stmt = $db->query("SELECT id, name, played, wins, draws, losses, points FROM standings");
sendSuccess($stmt->fetchAll());