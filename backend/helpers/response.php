<?php
function sendResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');

    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

function sendSuccess($data = null, $message = 'Success') {
    sendResponse(true, $message, $data, 200);
}

function sendError($message = 'An error occurred', $statusCode = 400) {
    sendResponse(false, $message, null, $statusCode);
}

function sendUnauthorized($message = 'Unauthorized access') {
    sendResponse(false, $message, null, 401);
}

function sendForbidden($message = 'Forbidden') {
    sendResponse(false, $message, null, 403);
}

function sendNotFound($message = 'Not found') {
    sendResponse(false, $message, null, 404);
}