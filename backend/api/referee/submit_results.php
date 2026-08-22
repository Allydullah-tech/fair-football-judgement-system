<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireReferee();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$data    = getRequestBody();
$db      = getDB();
$userId  = $_SESSION['user_id'];

$error = validateRequired(['match_id', 'score_a', 'score_b'], $data);
if ($error) sendError($error);

$matchId = (int)$data['match_id'];
$scoreA  = (int)$data['score_a'];
$scoreB  = (int)$data['score_b'];
$notes   = sanitize($data['notes'] ?? '');

$stmt = $db->prepare("SELECT id FROM matches WHERE id = ? AND referee_id = ?");
$stmt->execute([$matchId, $userId]);
if (!$stmt->fetch()) sendError('You are not assigned to this match.');

$stmt = $db->prepare("SELECT id FROM results WHERE match_id = ?");
$stmt->execute([$matchId]);
if ($stmt->fetch()) sendError('A result has already been submitted for this match.');

$stmt = $db->prepare("INSERT INTO results (match_id, score_a, score_b, notes, submitted_by) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$matchId, $scoreA, $scoreB, $notes, $userId]);

$stmt = $db->prepare("UPDATE matches SET status = 'completed' WHERE id = ?");
$stmt->execute([$matchId]);

logActivity($userId, "Result submitted for match ID: {$matchId} — {$scoreA} vs {$scoreB}");
sendSuccess(null, 'Result submitted successfully.');