<?php
function requireRole(string ...$roles): void {
    requireLogin();
    $userRole = $_SESSION['user_role'] ?? '';
    if (!in_array($userRole, $roles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission.']);
        exit;
    }
}

function requireAdmin(): void {
    requireRole('administrator');
}

function requireReferee(): void {
    requireRole('referee', 'administrator');
}

function requireManager(): void {
    requireRole('manager', 'administrator');
}