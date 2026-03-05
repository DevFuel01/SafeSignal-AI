<?php

/**
 * SafeSignal AI - Configuration File
 * All sensitive keys loaded from environment variables.
 */

// --- Database Configuration ---
define('DB_HOST', getenv('SS_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('SS_DB_NAME') ?: 'safesignal');
define('DB_USER', getenv('SS_DB_USER') ?: 'root');
define('DB_PASS', getenv('SS_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// --- Gemini AI API Key ---
// Set the environment variable GEMINI_API_KEY on your system
// or place it in a .env file (not committed to source control)
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY');
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent');

// --- App Settings ---
define('APP_NAME', 'SafeSignal AI');
define('APP_URL', 'http://localhost/SafeSignal');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// --- Session Settings ---
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// --- Rate Limiting ---
define('RATE_LIMIT_REPORTS', 5);     // max reports per hour per IP
define('RATE_LIMIT_WINDOW', 3600);   // 1 hour in seconds

// --- Database Connection (PDO Singleton) ---
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
        }
    }
    return $pdo;
}

// --- Auth Helpers ---
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        if (isAjax()) {
            http_response_code(401);
            die(json_encode(['success' => false, 'message' => 'Authentication required.']));
        }
        header('Location: ' . APP_URL . '/pages/login.php');
        exit;
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        if (isAjax()) {
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'Admin access required.']));
        }
        header('Location: ' . APP_URL . '/pages/login.php?error=unauthorized');
        exit;
    }
}

function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// --- JSON Response Helper ---
function jsonResponse(bool $success, string $message, $data = null, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    $resp = ['success' => $success, 'message' => $message];
    if ($data !== null) $resp['data'] = $data;
    echo json_encode($resp);
    exit;
}

// --- Rate Limiting Helper ---
function checkRateLimit(string $action, int $limit, int $window): bool
{
    $key = $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $now = time();
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [];
    }
    // Clean old entries
    $_SESSION['rate_limits'][$key] = array_filter(
        $_SESSION['rate_limits'][$key],
        fn($t) => ($now - $t) < $window
    );
    if (count($_SESSION['rate_limits'][$key]) >= $limit) {
        return false;
    }
    $_SESSION['rate_limits'][$key][] = $now;
    return true;
}

// --- Sanitize Output ---
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
