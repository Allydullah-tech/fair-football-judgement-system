<?php
function generateToken(int $length = 64): string {
    return bin2hex(random_bytes($length / 2));
}

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function formatDate(string $date): string {
    return date('d M Y', strtotime($date));
}

function isTokenExpired(string $expiry): bool {
    return strtotime($expiry) < time();
}

function logActivity(int $userId, string $activity, string $status = 'completed'): void {
    try {
        $db   = getDB();
        $stmt = $db->prepare("INSERT INTO activity_log (user_id, activity, status) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $activity, $status]);
    } catch (Exception $e) {
        // Silent fail — log errors shouldn't break the app
    }
}