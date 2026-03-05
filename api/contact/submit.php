<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', null, 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

if (empty($name) || strlen($name) < 2) jsonResponse(false, 'Please enter your name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(false, 'Please enter a valid email address.');
if (empty($message) || strlen($message) < 10) jsonResponse(false, 'Message must be at least 10 characters.');
if (strlen($message) > 2000) jsonResponse(false, 'Message is too long (max 2000 characters).');

$db = getDB();
$stmt = $db->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $subject ?: null, $message]);

jsonResponse(true, 'Message sent successfully! We will get back to you shortly.');
