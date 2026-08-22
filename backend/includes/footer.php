<?php
// ============================================
// FFJS — includes/footer.php
// Optionally include at the bottom of API files
// to handle fallthrough / unmatched actions.
// ============================================

if (!defined('FFJS_ACCESS')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed.']);
    exit;
}

// If execution reaches here, no valid action was matched
sendError('Invalid action or request.');