<?php
require_once __DIR__ . '/../../../config/config.php';

header('Content-Type: application/json');

// Only allow admins to view contact messages
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed.', null, 405);
}

try {
    $db = getDB();
    $stmt = $db->query("SELECT id, name, email, subject, message, is_read, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 100");
    $messages = $stmt->fetchAll();

    jsonResponse(true, 'Contact messages retrieved.', ['messages' => $messages]);
} catch (PDOException $e) {
    error_log("Database Error in contact messages list: " . $e->getMessage());
    jsonResponse(false, 'Failed to retrieve messages.', null, 500);
}
