<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../services/gemini.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', null, 405);
}

requireLogin();

// Rate limiting
if (!checkRateLimit('report', RATE_LIMIT_REPORTS, RATE_LIMIT_WINDOW)) {
    jsonResponse(false, 'Too many reports submitted. Please wait before submitting again.', null, 429);
}

// Parse inputs
$title        = trim($_POST['title'] ?? '');
$description  = trim($_POST['description'] ?? '');
$latitude     = floatval($_POST['latitude'] ?? 0);
$longitude    = floatval($_POST['longitude'] ?? 0);
$locationName = trim($_POST['location_name'] ?? '');
$userCategory = trim($_POST['category'] ?? '') ?: null;
$userSeverity = trim($_POST['severity'] ?? '') ?: null;

// Validation
if (empty($title)) jsonResponse(false, 'Title is required.');
if (strlen($title) < 5 || strlen($title) > 255) jsonResponse(false, 'Title must be between 5 and 255 characters.');
if (empty($description)) jsonResponse(false, 'Description is required.');
if (strlen($description) < 20) jsonResponse(false, 'Description must be at least 20 characters.');
if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    jsonResponse(false, 'Invalid location coordinates.');
}
if ($latitude == 0 && $longitude == 0) {
    jsonResponse(false, 'Please provide a valid location for your report.');
}

$validSeverities  = ['Low', 'Medium', 'High', 'Critical'];
$validCategories  = ['Crime', 'Flood', 'Fire', 'Harassment', 'Accident', 'Infrastructure Damage', 'Pollution', 'Medical Emergency', 'General Safety'];
if ($userSeverity && !in_array($userSeverity, $validSeverities)) jsonResponse(false, 'Invalid severity level.');
if ($userCategory && !in_array($userCategory, $validCategories)) jsonResponse(false, 'Invalid category.');

// Handle image upload
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $file     = $_FILES['image'];
    $fileSize = $file['size'];
    $fileExt  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(false, 'File upload error. Please try again.');
    }
    if ($fileSize > MAX_UPLOAD_SIZE) {
        jsonResponse(false, 'Image file too large. Maximum size is 5MB.');
    }
    if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
        jsonResponse(false, 'Invalid image format. Allowed: JPG, PNG, GIF, WebP.');
    }

    // Verify it's actually an image
    $imageInfo = getimagesize($file['tmp_name']);
    if (!$imageInfo) {
        jsonResponse(false, 'Uploaded file is not a valid image.');
    }

    $uploadDir  = UPLOAD_DIR;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $newFileName = uniqid('report_', true) . '.' . $fileExt;
    $destPath    = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        jsonResponse(false, 'Failed to save uploaded image.');
    }

    $imagePath = 'uploads/' . $newFileName;
}

// Run Gemini AI analysis
$gemini = new GeminiService();
$ai = $gemini->analyzeReport($title, $description, $userCategory, $userSeverity);

$aiCategory  = $ai['category'];
$aiSeverity  = $ai['severity'];
$aiSummary   = $ai['summary'];
$aiActions   = json_encode($ai['recommended_actions']);
$aiTags      = json_encode($ai['tags']);
$sdgTags     = implode(',', $ai['sdg_mapping']);

// Insert report into DB
$db = getDB();
$stmt = $db->prepare('
    INSERT INTO reports 
        (user_id, title, description, image_path, latitude, longitude, location_name,
         user_category, user_severity, ai_category, ai_severity, ai_summary,
         ai_recommended_actions, ai_tags, sdg_tags, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
');
$stmt->execute([
    $_SESSION['user_id'],
    $title,
    $description,
    $imagePath,
    $latitude,
    $longitude,
    $locationName,
    $userCategory,
    $userSeverity,
    $aiCategory,
    $aiSeverity,
    $aiSummary,
    $aiActions,
    $aiTags,
    $sdgTags,
    'pending'
]);
$reportId = $db->lastInsertId();

// Create alert for High/Critical reports
if (in_array($aiSeverity, ['High', 'Critical'])) {
    $alertMsg = strtoupper($aiSeverity) . " ALERT: " . $title . " reported" .
        ($locationName ? " in {$locationName}" : '') . ". Please be cautious.";
    $alertStmt = $db->prepare('INSERT INTO alerts (report_id, message, severity, latitude, longitude) VALUES (?, ?, ?, ?, ?)');
    $alertStmt->execute([$reportId, $alertMsg, $aiSeverity, $latitude, $longitude]);
}

jsonResponse(true, 'Report submitted successfully. AI analysis complete.', [
    'report_id'   => $reportId,
    'ai_category' => $aiCategory,
    'ai_severity' => $aiSeverity,
    'ai_summary'  => $aiSummary,
    'sdg_tags'    => $sdgTags,
]);
