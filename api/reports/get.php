<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) jsonResponse(false, 'Invalid report ID.');

$db = getDB();
$stmt = $db->prepare('
    SELECT r.*, u.name AS reporter_name
    FROM reports r 
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
');
$stmt->execute([$id]);
$report = $stmt->fetch();

if (!$report) jsonResponse(false, 'Report not found.', null, 404);

$report['ai_recommended_actions'] = json_decode($report['ai_recommended_actions'] ?? '[]', true) ?? [];
$report['ai_tags']                = json_decode($report['ai_tags'] ?? '[]', true) ?? [];
$report['sdg_list']               = array_filter(explode(',', $report['sdg_tags'] ?? ''));
if ($report['image_path']) {
    $report['image_url'] = APP_URL . '/' . $report['image_path'];
}

jsonResponse(true, 'Report retrieved.', ['report' => $report]);
