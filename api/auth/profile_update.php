<?php

/**
 * SafeSignal AI - Profile Update API
 * Updates the user's full name in the database and session.
 */

require_once __DIR__ . '/../../config/config.php';

// Check if logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required.', null, 401);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');

// Validation
if (empty($name)) {
    jsonResponse(false, 'Name is required.', null, 400);
}

if (strlen($name) < 2 || strlen($name) > 100) {
    jsonResponse(false, 'Name must be between 2 and 100 characters.', null, 400);
}

try {
    $db = getDB();
    $userId = $_SESSION['user_id'];

    // Update database
    $stmt = $db->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->execute([$name, $userId]);

    // Update session
    $_SESSION['user_name'] = $name;

    jsonResponse(true, 'Profile updated successfully.', ['name' => $name]);
} catch (Exception $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
