<?php
if (!defined('FFJS_ACCESS')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed.']);
    exit;
}

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'You must be logged in to access this resource.']);
        exit;
    }
}

function getSessionUser(): array {
    return [
        'id'    => $_SESSION['user_id']    ?? null,
        'name'  => $_SESSION['user_name']  ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role'  => $_SESSION['user_role']  ?? null,
    ];
}

function requireRole(string ...$roles): void {
    requireLogin();
    $userRole = $_SESSION['user_role'] ?? '';
    if (!in_array($userRole, $roles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to access this resource.']);
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