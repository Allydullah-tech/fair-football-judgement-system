<?php
function validateRequired(array $fields, array $data): ?string {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return "Field '{$field}' is required.";
        }
    }
    return null;
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateRole(string $role): bool {
    return in_array($role, ['administrator', 'referee', 'manager', 'viewer']);
}

function validateMinLength(string $value, int $min): bool {
    return strlen(trim($value)) >= $min;
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)));
}

function getRequestBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}