<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed.', null, 405);
}

$db = getDB();

// Get alerts from the last 24 hours, unread first
$since = $_GET['since'] ?? null;
$params = [];
$whereExtra = '';

if ($since && strtotime($since)) {
    $whereExtra = 'AND a.created_at > ?';
    $params[] = date('Y-m-d H:i:s', strtotime($since));
} else {
    $whereExtra = 'AND a.created_at >= NOW() - INTERVAL 24 HOUR';
}

$stmt = $db->prepare("
    SELECT a.id, a.message, a.severity, a.latitude, a.longitude, a.created_at,
           r.id AS report_id, r.title AS report_title, r.ai_category
    FROM alerts a
    LEFT JOIN reports r ON a.report_id = r.id
    WHERE 1=1 {$whereExtra}
    ORDER BY a.created_at DESC
    LIMIT 20
");
$stmt->execute($params);
$alerts = $stmt->fetchAll();

jsonResponse(true, 'Alerts retrieved.', ['alerts' => $alerts]);
