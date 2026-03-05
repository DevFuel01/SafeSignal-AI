<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', null, 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$name     = trim($input['name'] ?? '');
$email    = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($password)) {
    jsonResponse(false, 'Name, email, and password are required.');
}
if (strlen($name) < 2 || strlen($name) > 100) {
    jsonResponse(false, 'Name must be between 2 and 100 characters.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email address.');
}
if (strlen($password) < 8) {
    jsonResponse(false, 'Password must be at least 8 characters.');
}
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
    jsonResponse(false, 'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
}

$db = getDB();

// Check if email already exists
$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    jsonResponse(false, 'An account with this email already exists.');
}

// Create account
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $hash, 'user']);
$userId = $db->lastInsertId();

// Set session
$_SESSION['user_id']   = $userId;
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;
$_SESSION['role']      = 'user';

jsonResponse(true, 'Account created successfully. Welcome to SafeSignal!', [
    'user' => ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'user']
]);
