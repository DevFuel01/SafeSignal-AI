<?php
if (!defined('APP_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SafeSignal AI — AI-powered community safety and hazard reporting platform. Report incidents, visualize on live map, and get alerts powered by Gemini AI.">
    <meta name="keywords" content="SafeSignal, community safety, hazard reporting, AI, SDG11, SDG16, incident map">
    <meta property="og:title" content="<?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>SafeSignal AI">
    <meta property="og:description" content="AI-powered community safety & hazard reporting platform. SDG 11 & 16 aligned.">
    <meta property="og:type" content="website">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>SafeSignal AI</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Leaflet (for map pages) -->
    <?php if (in_array($currentPage, ['map', 'report'])): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <?php endif; ?>

    <!-- Chart.js (for dashboard/admin) -->
    <?php if (in_array($currentPage, ['dashboard', 'admin'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php endif; ?>

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <?php if ($currentPage === 'map'): ?>
        <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/map.css">
    <?php elseif ($currentPage === 'admin'): ?>
        <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
    <?php elseif ($currentPage === 'dashboard'): ?>
        <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard.css">
    <?php endif; ?>

    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>

<body class="page-<?= h($currentPage) ?>">

    <!-- Alert Toast Container -->
    <div id="toast-container" class="toast-container" role="alert" aria-live="polite"></div>

    <!-- Live Alert Banner -->
    <div id="alert-banner" class="alert-banner" style="display:none;" aria-live="assertive"></div>

    <!-- Navigation -->
    <nav class="navbar" id="navbar" role="navigation" aria-label="Main Navigation">
        <div class="nav-container">
            <a href="<?= APP_URL ?>" class="nav-logo" aria-label="SafeSignal AI Home">
                <div class="logo-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div class="logo-text">
                    <span class="logo-name">SafeSignal</span>
                    <span class="logo-ai">AI</span>
                </div>
            </a>

            <ul class="nav-menu" id="nav-menu" role="menubar">
                <li role="none"><a href="<?= APP_URL ?>" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" role="menuitem">
                        <i class="fas fa-home"></i> <span>Home</span>
                    </a></li>
                <li role="none"><a href="<?= APP_URL ?>/pages/map.php" class="nav-link <?= $currentPage === 'map' ? 'active' : '' ?>" role="menuitem">
                        <i class="fas fa-map-marked-alt"></i> <span>Live Map</span>
                    </a></li>
                <li role="none"><a href="<?= APP_URL ?>/pages/report.php" class="nav-link nav-link-report <?= $currentPage === 'report' ? 'active' : '' ?>" role="menuitem">
                        <i class="fas fa-circle-exclamation"></i> <span>Report</span>
                    </a></li>
                <li role="none"><a href="<?= APP_URL ?>/pages/about.php" class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" role="menuitem">
                        <i class="fas fa-circle-info"></i> <span>About</span>
                    </a></li>
                <li role="none"><a href="<?= APP_URL ?>/pages/contact.php" class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" role="menuitem">
                        <i class="fas fa-envelope"></i> <span>Contact</span>
                    </a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-dropdown" role="none">
                        <button class="nav-link nav-user-btn" aria-haspopup="true" aria-expanded="false" id="user-menu-btn">
                            <i class="fas fa-user-circle"></i> <span><?= h($_SESSION['user_name'] ?? 'User') ?></span> <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="nav-dropdown-menu" role="menu" aria-labelledby="user-menu-btn">
                            <li role="none"><a href="<?= APP_URL ?>/pages/dashboard.php" role="menuitem"><i class="fas fa-gauge-high"></i> Dashboard</a></li>
                            <?php if (isAdmin()): ?>
                                <li role="none"><a href="<?= APP_URL ?>/pages/admin.php" role="menuitem"><i class="fas fa-shield-cat"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            <li role="none"><a href="#" id="logout-btn" role="menuitem"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li role="none"><a href="<?= APP_URL ?>/pages/login.php" class="nav-link nav-link-login <?= $currentPage === 'login' ? 'active' : '' ?>" role="menuitem">
                            <i class="fas fa-sign-in-alt"></i> <span>Login</span>
                        </a></li>
                <?php endif; ?>
            </ul>

            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="nav-menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>
    <!-- End Navigation -->