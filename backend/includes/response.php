<?php
if (!defined('FFJS_ACCESS')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed.']);
    exit;
}

function sendResponse(bool $success, string $message = '', $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');

    $response = [
        'success' => $success,
        'message' => $message,
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

function sendSuccess($data = null, string $message = 'Success'): void {
    sendResponse(true, $message, $data, 200);
}

function sendError(string $message = 'An error occurred', int $statusCode = 400): void {
    sendResponse(false, $message, null, $statusCode);
}

function sendUnauthorized(string $message = 'Unauthorized access'): void {
    sendResponse(false, $message, null, 401);
}

function sendForbidden(string $message = 'Forbidden'): void {
    sendResponse(false, $message, null, 403);
}

function sendNotFound(string $message = 'Not found'): void {
    sendResponse(false, $message, null, 404);
}