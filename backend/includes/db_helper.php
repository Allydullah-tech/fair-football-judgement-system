<?php
// ============================================
// FFJS — includes/db_helper.php
// Reusable database query functions.
// ============================================

if (!defined('FFJS_ACCESS')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed.']);
    exit;
}

// Fetch a single row
function dbFetchOne(string $sql, array $params = []): ?array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}

// Fetch multiple rows
function dbFetchAll(string $sql, array $params = []): array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Execute insert/update/delete, return affected rows
function dbExecute(string $sql, array $params = []): int {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

// Execute insert, return last inserted ID
function dbInsert(string $sql, array $params = []): int {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return (int) getDB()->lastInsertId();
}

// Count query shortcut
function dbCount(string $sql, array $params = []): int {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

// Check if a record exists
function dbExists(string $table, string $column, $value): bool {
    $stmt = getDB()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
    $stmt->execute([$value]);
    return (int) $stmt->fetchColumn() > 0;
}

// Check if a record exists by multiple conditions
function dbExistsWhere(string $table, array $conditions): bool {
    $clauses = implode(' AND ', array_map(fn($k) => "{$k} = ?", array_keys($conditions)));
    $stmt    = getDB()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$clauses}");
    $stmt->execute(array_values($conditions));
    return (int) $stmt->fetchColumn() > 0;
}