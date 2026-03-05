<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', null, 405);
}

requireLogin();

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) jsonResponse(false, 'Invalid report ID.');

$db = getDB();

// Prevent duplicate confirmations per session
if (!isset($_SESSION['confirmed_reports'])) $_SESSION['confirmed_reports'] = [];
if (in_array($id, $_SESSION['confirmed_reports'])) {
    jsonResponse(false, 'You have already confirmed this report.');
}

// Check report exists
$stmt = $db->prepare('SELECT id, confirm_count FROM reports WHERE id = ?');
$stmt->execute([$id]);
$report = $stmt->fetch();
if (!$report) jsonResponse(false, 'Report not found.', null, 404);

$stmt = $db->prepare('UPDATE reports SET confirm_count = confirm_count + 1 WHERE id = ?');
$stmt->execute([$id]);

$_SESSION['confirmed_reports'][] = $id;

jsonResponse(true, 'Report confirmed. Thank you for helping verify this incident.', [
    'confirm_count' => $report['confirm_count'] + 1
]);
