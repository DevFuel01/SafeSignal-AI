<?php
require_once __DIR__ . '/../../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', null, 405);
}

requireAdmin();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$reportId   = intval($input['report_id'] ?? 0);
$status     = trim($input['status'] ?? '');
$adminNote  = trim($input['admin_note'] ?? '');

if ($reportId <= 0) jsonResponse(false, 'Report ID is required.');

$validStatuses = ['pending', 'verified', 'resolved'];
if (!in_array($status, $validStatuses)) jsonResponse(false, 'Invalid status. Must be: pending, verified, or resolved.');

$db = getDB();

// Verify report exists
$stmt = $db->prepare('SELECT id, title FROM reports WHERE id = ?');
$stmt->execute([$reportId]);
$report = $stmt->fetch();
if (!$report) jsonResponse(false, 'Report not found.', null, 404);

// Update status
$stmt = $db->prepare('UPDATE reports SET status = ?, admin_note = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$status, $adminNote ?: null, $reportId]);

jsonResponse(true, "Report status updated to '{$status}'.", [
    'report_id' => $reportId,
    'status'    => $status,
]);
