<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed.', null, 405);
}

$db = getDB();

// Build query with filters
$where  = [];
$params = [];

// Category filter
if (!empty($_GET['category'])) {
    $where[]  = 'ai_category = ?';
    $params[] = $_GET['category'];
}

// Severity filter
$validSeverities = ['Low', 'Medium', 'High', 'Critical'];
if (!empty($_GET['severity']) && in_array($_GET['severity'], $validSeverities)) {
    $where[]  = 'ai_severity = ?';
    $params[] = $_GET['severity'];
}

// Status filter
$validStatuses = ['pending', 'verified', 'resolved'];
if (!empty($_GET['status']) && in_array($_GET['status'], $validStatuses)) {
    $where[]  = 'r.status = ?';
    $params[] = $_GET['status'];
}

// Time filter
$timeFilter = $_GET['time'] ?? '';
if ($timeFilter === '24h') {
    $where[] = 'r.created_at >= NOW() - INTERVAL 1 DAY';
} elseif ($timeFilter === '7d') {
    $where[] = 'r.created_at >= NOW() - INTERVAL 7 DAY';
} elseif ($timeFilter === '30d') {
    $where[] = 'r.created_at >= NOW() - INTERVAL 30 DAY';
}

// Search filter
if (!empty($_GET['search'])) {
    $where[]  = '(r.title LIKE ? OR r.location_name LIKE ? OR r.ai_summary LIKE ?)';
    $search   = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

// SDG filter
if (!empty($_GET['sdg'])) {
    $where[]  = 'r.sdg_tags LIKE ?';
    $params[] = '%' . $_GET['sdg'] . '%';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination
$page    = max(1, intval($_GET['page'] ?? 1));
$limit   = min(100, max(1, intval($_GET['limit'] ?? 50)));
$offset  = ($page - 1) * $limit;

$sql = "
    SELECT 
        r.id, r.title, r.description, r.image_path, r.latitude, r.longitude, r.location_name,
        r.ai_category, r.ai_severity, r.ai_summary, r.ai_recommended_actions, r.ai_tags,
        r.sdg_tags, r.status, r.confirm_count, r.created_at,
        u.name AS reporter_name
    FROM reports r
    JOIN users u ON r.user_id = u.id
    {$whereClause}
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
";

$params[] = $limit;
$params[] = $offset;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Get total count
$countSql = "SELECT COUNT(*) FROM reports r JOIN users u ON r.user_id = u.id {$whereClause}";
$countParams = array_slice($params, 0, -2); // Remove limit/offset
$countStmt = $db->prepare($countSql);
$countStmt->execute($countParams);
$total = $countStmt->fetchColumn();

// Parse JSON fields
foreach ($reports as &$report) {
    $report['ai_recommended_actions'] = json_decode($report['ai_recommended_actions'] ?? '[]', true) ?? [];
    $report['ai_tags']                = json_decode($report['ai_tags'] ?? '[]', true) ?? [];
    $report['sdg_list']               = array_filter(explode(',', $report['sdg_tags'] ?? ''));
    if ($report['image_path']) {
        $report['image_url'] = APP_URL . '/' . $report['image_path'];
    }
}
unset($report);

jsonResponse(true, 'Reports retrieved.', [
    'reports'  => $reports,
    'total'    => (int)$total,
    'page'     => $page,
    'limit'    => $limit,
    'pages'    => ceil($total / $limit),
]);
