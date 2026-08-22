<?php
ob_start();
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireReferee();

$method    = $_SERVER['REQUEST_METHOD'];
$db        = getDB();
$refereeId = $_SESSION['user_id'];

if ($method === 'GET') {
    $action  = sanitize($_GET['action'] ?? '');
    $matchId = (int)($_GET['match_id'] ?? 0);

    // Get ALL matches assigned to this referee
    if ($action === 'matches') {
        $stmt = $db->prepare("
            SELECT m.id, m.match_date, m.status,
                   ta.name AS team_a, tb.name AS team_b
            FROM matches m
            JOIN teams ta ON ta.id = m.team_a_id
            JOIN teams tb ON tb.id = m.team_b_id
            WHERE m.referee_id = ?
            ORDER BY m.match_date DESC
        ");
        $stmt->execute([$refereeId]);
        $matches = $stmt->fetchAll();
        foreach ($matches as &$m) {
            $m['match_date'] = formatDate($m['match_date']);
        }
        ob_end_clean();
        sendSuccess($matches);
    }

    // Get players for both teams in a specific match
    if ($action === 'players' && $matchId) {
        $stmt = $db->prepare("SELECT id, team_a_id, team_b_id FROM matches WHERE id = ? AND referee_id = ?");
        $stmt->execute([$matchId, $refereeId]);
        $match = $stmt->fetch();
        if (!$match) {
            ob_end_clean();
            sendError('Match not found or not assigned to you.');
        }

        $stmt = $db->prepare("
            SELECT p.id, p.name, p.jersey_number, p.position, t.name AS team_name
            FROM players p
            JOIN teams t ON t.id = p.team_id
            WHERE p.team_id IN (?, ?) AND p.status = 'active'
            ORDER BY t.name, p.jersey_number ASC
        ");
        $stmt->execute([$match['team_a_id'], $match['team_b_id']]);
        ob_end_clean();
        sendSuccess($stmt->fetchAll());
    }

    // Get already saved statistics for a match
    if ($action === 'stats' && $matchId) {
        $stmt = $db->prepare("
            SELECT ps.*, p.name AS player_name, p.jersey_number, t.name AS team_name
            FROM player_statistics ps
            JOIN players p ON p.id = ps.player_id
            JOIN teams t   ON t.id = p.team_id
            WHERE ps.match_id = ?
            ORDER BY ps.total_points DESC
        ");
        $stmt->execute([$matchId]);
        ob_end_clean();
        sendSuccess($stmt->fetchAll());
    }

    ob_end_clean();
    sendError('Invalid action.');
}

if ($method === 'POST') {
    $data   = getRequestBody();
    $action = isset($data['action']) ? sanitize($data['action']) : '';

    if ($action === 'save') {
        // Validate match_id
        if (empty($data['match_id'])) {
            ob_end_clean();
            sendError('Field match_id is required.');
        }

        // Validate statistics array exists
        if (!isset($data['statistics']) || !is_array($data['statistics']) || empty($data['statistics'])) {
            ob_end_clean();
            sendError('No statistics provided.');
        }

        $matchId    = (int)$data['match_id'];
        $statistics = $data['statistics'];

        // Verify match assigned to this referee
        $stmt = $db->prepare("SELECT id FROM matches WHERE id = ? AND referee_id = ?");
        $stmt->execute([$matchId, $refereeId]);
        if (!$stmt->fetch()) {
            ob_end_clean();
            sendError('Match not found or not assigned to you.');
        }

        // Find player with most successful passes in this match
        $maxPasses   = 0;
        $maxPassesId = 0;

        foreach ($statistics as $stat) {
            $passes = isset($stat['successful_passes']) ? (int)$stat['successful_passes'] : 0;
            if ($passes > $maxPasses) {
                $maxPasses   = $passes;
                $maxPassesId = isset($stat['player_id']) ? (int)$stat['player_id'] : 0;
            }
        }

        // Delete existing stats for this match to allow re-submission
        $stmt = $db->prepare("DELETE FROM player_statistics WHERE match_id = ?");
        $stmt->execute([$matchId]);

        // Insert each player's stats
        foreach ($statistics as $stat) {
            $playerId = isset($stat['player_id'])         ? (int)$stat['player_id']         : 0;
            $goals    = isset($stat['goals'])             ? (int)$stat['goals']             : 0;
            $assists  = isset($stat['assists'])           ? (int)$stat['assists']           : 0;
            $passes   = isset($stat['successful_passes']) ? (int)$stat['successful_passes'] : 0;
            $yellow   = isset($stat['yellow_cards'])      ? (int)$stat['yellow_cards']      : 0;
            $red      = isset($stat['red_cards'])         ? (int)$stat['red_cards']         : 0;

            if ($playerId === 0) continue;

            $passPoints  = ($playerId === $maxPassesId && $maxPasses > 0) ? 1 : 0;
            $totalPoints = ($goals * 3) + ($assists * 1) + $passPoints + ($yellow * -1) + ($red * -3);

            $stmt = $db->prepare("
                INSERT INTO player_statistics
                    (match_id, player_id, goals, assists, successful_passes, yellow_cards, red_cards, total_points, recorded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$matchId, $playerId, $goals, $assists, $passes, $yellow, $red, $totalPoints, $refereeId]);
        }

        logActivity($refereeId, "Player statistics saved for match ID: {$matchId}");
        ob_end_clean();
        sendSuccess(null, 'Player statistics saved successfully.');
    }

    ob_end_clean();
    sendError('Invalid action.');
}

ob_end_clean();
sendError('Method not allowed.', 405);