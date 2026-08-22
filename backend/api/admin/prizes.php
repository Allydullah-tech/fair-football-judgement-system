<?php
define('FFJS_ACCESS', true);
require_once __DIR__ . '/../../includes/header.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    $action = sanitize($_GET['action'] ?? 'categories');

    if ($action === 'categories') {
        $stmt = $db->query("SELECT * FROM prizes ORDER BY created_at DESC");
        sendSuccess($stmt->fetchAll());
    }

    if ($action === 'distribution') {
        $stmt = $db->query("
            SELECT pd.id, pd.winner_name, pd.winner_type, pd.status, pd.awarded_at,
                   p.title AS prize, p.criteria
            FROM prize_distribution pd
            JOIN prizes p ON p.id = pd.prize_id
            ORDER BY pd.awarded_at DESC
        ");
        sendSuccess($stmt->fetchAll());
    }

    if ($action === 'teams') {
        $stmt = $db->query("SELECT id, name FROM teams WHERE status = 'active' ORDER BY name");
        sendSuccess($stmt->fetchAll());
    }

    if ($action === 'referees') {
        $stmt = $db->query("SELECT id, full_name AS name FROM users WHERE role = 'referee' AND status = 'active' ORDER BY full_name");
        sendSuccess($stmt->fetchAll());
    }

    if ($action === 'mvp') {
        $stmt = $db->query("
            SELECT p.id, p.name AS player_name, t.name AS team_name,
                   SUM(ps.total_points) AS total_points
            FROM players p
            JOIN teams t ON t.id = p.team_id
            JOIN player_statistics ps ON ps.player_id = p.id
            GROUP BY p.id, p.name, t.name
            ORDER BY total_points DESC
            LIMIT 1
        ");
        $result = $stmt->fetch();
        sendSuccess($result ?: null);
    }

    sendError('Invalid action.');
}

if ($method === 'POST') {
    $data   = getRequestBody();
    $action = sanitize($data['action'] ?? '');

    if ($action === 'add_category') {
        $error = validateRequired(['title', 'criteria'], $data);
        if ($error) sendError($error);

        $title     = sanitize($data['title']);
        $criteria  = sanitize($data['criteria']);
        $amount    = (float)($data['amount'] ?? 0);
        $prizeType = sanitize($data['prize_type'] ?? 'trophy');

        $stmt = $db->prepare("INSERT INTO prizes (title, criteria, amount, prize_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $criteria, $amount, $prizeType]);

        logActivity($_SESSION['user_id'], "Prize category added: {$title}");
        sendSuccess(null, 'Prize category added successfully.');
    }

    if ($action === 'allocate') {
        $error = validateRequired(['prize_id', 'winner_name', 'winner_type'], $data);
        if ($error) sendError($error);

        $prizeId    = (int)$data['prize_id'];
        $winnerName = sanitize($data['winner_name']);
        $winnerType = sanitize($data['winner_type']);
        $teamId     = !empty($data['team_id']) ? (int)$data['team_id'] : null;
        $awardedBy  = $_SESSION['user_id'];

        // Map winner_type to valid DB ENUM values
        // DB allows: team, player
        // We show: team, player, referee — store referee as player
        $dbWinnerType = ($winnerType === 'referee') ? 'player' : $winnerType;
        if (!in_array($dbWinnerType, ['team', 'player'])) {
            sendError('Invalid winner type.');
        }

        $stmt = $db->prepare("
            INSERT INTO prize_distribution (prize_id, winner_name, winner_type, team_id, awarded_by, status)
            VALUES (?, ?, ?, ?, ?, 'approved')
        ");
        $stmt->execute([$prizeId, $winnerName, $dbWinnerType, $teamId, $awardedBy]);

        logActivity($awardedBy, "Prize allocated: {$winnerName} ({$winnerType}) — prize ID {$prizeId}");
        sendSuccess(null, 'Prize allocated successfully.');
    }

    sendError('Invalid action.');
}

sendError('Method not allowed.', 405);