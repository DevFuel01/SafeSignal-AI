<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'AI-Powered Community Safety Platform';
$extraHead = '<style>body{padding-top:0!important;}.navbar{position:fixed;}</style>';
include __DIR__ . '/partials/header.php';
?>

<main>
    <!-- ===========================
     HERO SECTION
============================= -->
    <section class="hero" style="min-height:100vh;">
        <div class="container">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;">
                <div class="hero-content">
                    <div class="hero-tag animate-on-scroll">
                        <i class="fas fa-satellite-dish"></i>
                        Live Incident Monitoring · AI-Powered
                    </div>
                    <h1>
                        <span class="hero-gradient-text">Report Incidents.</span><br>
                        Protect Communities.<br>
                        <span style="color:var(--accent-light);">Powered by AI.</span>
                    </h1>
                    <p class="hero-subtitle">
                        SafeSignal AI uses Gemini Artificial Intelligence to automatically classify your reports,
                        recommend actions, and alert nearby communities — making cities safer, together.
                    </p>
                    <div class="hero-cta">
                        <a href="<?= APP_URL ?>/pages/report.php" class="btn btn-danger btn-xl">
                            <i class="fas fa-circle-exclamation"></i> Report Incident
                        </a>
                        <a href="<?= APP_URL ?>/pages/map.php" class="btn btn-outline btn-xl">
                            <i class="fas fa-map-marked-alt"></i> Live Map
                        </a>
                    </div>
                    <div class="hero-sdg-badges">
                        <span class="sdg-badge sdg-11"><i class="fas fa-city"></i> SDG 11 – Sustainable Cities</span>
                        <span class="sdg-badge sdg-16"><i class="fas fa-balance-scale"></i> SDG 16 – Peace & Justice</span>
                    </div>
                </div>

                <!-- Stats panel -->
                <div class="hero-visual animate-on-scroll delay-200" style="display:flex;flex-direction:column;gap:1rem;">
                    <div class="hero-stats-card">
                        <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:1.5rem;">
                            <div style="width:10px;height:10px;border-radius:50%;background:var(--success);animation:pulse-dot 1.5s ease infinite;box-shadow:0 0 8px var(--success);"></div>
                            <span style="font-size:0.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.1em;">Live System Status</span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                            <div>
                                <span class="stat-number" data-counter="2847">0</span>
                                <span class="stat-label">Reports Processed</span>
                            </div>
                            <div>
                                <span class="stat-number" data-counter="156">0</span>
                                <span class="stat-label">Resolved Today</span>
                            </div>
                            <div>
                                <span class="stat-number" data-counter="98">0</span>
                                <span class="stat-label">AI Accuracy %</span>
                            </div>
                            <div>
                                <span class="stat-number" data-counter="12">0</span>
                                <span class="stat-label">Active Alerts</span>
                            </div>
                        </div>
                        <a href="<?= APP_URL ?>/pages/map.php" class="btn btn-primary w-100">
                            <i class="fas fa-map-marked-alt"></i> Open Live Map
                        </a>
                    </div>

                    <!-- Recent Alert Preview -->
                    <div class="card" style="border-color:rgba(255,45,85,0.25);background:rgba(255,45,85,0.04);">
                        <div class="card-body" style="padding:1rem 1.25rem;">
                            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                                <i class="fas fa-bell" style="color:var(--critical);font-size:0.9rem;"></i>
                                <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--critical);">Latest Alert</span>
                            </div>
                            <p style="font-size:0.85rem;color:var(--text-secondary);line-height:1.5;margin-bottom:0.75rem;">CRITICAL: Armed robbery reported near Obalende Bus Stop. Avoid the area. Police en route.</p>
                            <div style="display:flex;gap:0.5rem;">
                                <span class="severity-badge severity-critical"><i class="fas fa-circle"></i>Critical</span>
                                <span style="font-size:0.75rem;color:var(--text-muted);align-self:center;">2 min ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating gradient orb -->
        <div style="position:absolute;bottom:-100px;left:-100px;width:400px;height:400px;background:radial-gradient(circle,rgba(124,58,237,0.12) 0%,transparent 70%);pointer-events:none;"></div>
    </section>

    <!-- ===========================
     STATS SECTION
============================= -->
    <section class="stats-section section-sm">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item animate-on-scroll">
                    <span class="stat-value" data-counter="2847">0</span>
                    <span class="stat-desc">Total Incidents Reported</span>
                </div>
                <div class="stat-item animate-on-scroll delay-100">
                    <span class="stat-value" data-counter="94">0</span>
                    <span class="stat-desc">% AI Classification Accuracy</span>
                </div>
                <div class="stat-item animate-on-scroll delay-200">
                    <span class="stat-value" data-counter="1240">0</span>
                    <span class="stat-desc">Community Actions Taken</span>
                </div>
                <div class="stat-item animate-on-scroll delay-300">
                    <span class="stat-value" data-counter="48">0</span>
                    <span class="stat-desc">Average Response Time (hours)</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ===========================
     SDG SECTION
============================= -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-eyebrow">Our Purpose</span>
                <h2 class="section-title">Aligned With Global Goals</h2>
                <p class="section-subtitle">SafeSignal AI directly contributes to two critical United Nations Sustainable Development Goals.</p>
            </div>

            <div class="grid-2" style="gap:2rem;">
                <div class="card animate-on-scroll" style="border-color:rgba(251,146,60,0.3);background:rgba(251,146,60,0.04);">
                    <div class="card-body" style="padding:2rem;">
                        <div style="width:64px;height:64px;border-radius:var(--radius);background:rgba(251,146,60,0.15);display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem;font-size:2rem;">
                            🏙️
                        </div>
                        <span class="sdg-badge sdg-11" style="margin-bottom:1rem;display:inline-flex;">SDG 11 — Sustainable Cities</span>
                        <h3 style="font-size:1.2rem;margin-bottom:0.75rem;">Making Cities Safer & Inclusive</h3>
                        <p style="color:var(--text-secondary);font-size:0.9rem;line-height:1.7;">SafeSignal enables citizens to report infrastructure damage, flooding, accidents, and urban hazards — creating a real-time safety layer for city authorities and planners to build resilient urban environments.</p>
                    </div>
                </div>
                <div class="card animate-on-scroll delay-200" style="border-color:rgba(96,165,250,0.3);background:rgba(96,165,250,0.04);">
                    <div class="card-body" style="padding:2rem;">
                        <div style="width:64px;height:64px;border-radius:var(--radius);background:rgba(96,165,250,0.15);display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem;font-size:2rem;">
                            ⚖️
                        </div>
                        <span class="sdg-badge sdg-16" style="margin-bottom:1rem;display:inline-flex;">SDG 16 — Peace & Justice</span>
                        <h3 style="font-size:1.2rem;margin-bottom:0.75rem;">Empowering Justice & Accountability</h3>
                        <p style="color:var(--text-secondary);font-size:0.9rem;line-height:1.7;">By enabling citizens to formally report crimes, harassment, and violence — with AI-powered documentation — SafeSignal creates an evidence trail that supports law enforcement and institutional accountability.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===========================
     HOW IT WORKS
============================= -->
    <section class="section" style="background:linear-gradient(180deg,transparent,rgba(0,212,255,0.03),transparent);">
        <div class="container">
            <div class="section-header">
                <span class="section-eyebrow">How It Works</span>
                <h2 class="section-title">From Report to Resolution</h2>
                <p class="section-subtitle">Our AI-powered pipeline ensures every incident is classified, routed, and acted upon.</p>
            </div>
            <div class="steps-grid">
                <?php
                $steps = [
                    ['fa-pen-to-square', '1', 'Submit Report', 'Describe what happened, pick a location on the map, add a photo if available.'],
                    ['fa-robot', '2', 'AI Analyzes', 'Gemini AI instantly classifies the category, severity, and maps it to SDGs.'],
                    ['fa-map-location-dot', '3', 'Map Updates', 'Your report appears on the live community safety map with colored severity markers.'],
                    ['fa-bell', '4', 'Alerts Sent', 'Nearby citizens and authorities receive alerts about high-severity incidents.'],
                    ['fa-shield-halved', '5', 'Resolution', 'Authorities review, verify, and resolve incidents with full accountability trail.'],
                ];
                foreach ($steps as $i => $step): ?>
                    <div class="card step-card animate-on-scroll delay-<?= $i * 100 ?>">
                        <div class="step-num"><i class="fas <?= $step[0] ?>"></i></div>
                        <h3><?= $step[2] ?></h3>
                        <p><?= $step[3] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===========================
     INCIDENT CATEGORIES
============================= -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-eyebrow">What You Can Report</span>
                <h2 class="section-title">Incident Categories</h2>
            </div>
            <div class="category-grid">
                <?php
                $cats = [
                    ['fa-user-secret', 'Crime', 'Crime'],
                    ['fa-water', 'Flood', 'Flood'],
                    ['fa-fire', 'Fire', 'Fire'],
                    ['fa-hand-fist', 'Harassment', 'Harassment'],
                    ['fa-car-burst', 'Accident', 'Accident'],
                    ['fa-road-barrier', 'Infrastructure', 'Infrastructure Damage'],
                    ['fa-smog', 'Pollution', 'Pollution'],
                    ['fa-kit-medical', 'Medical', 'Medical Emergency'],
                ];
                foreach ($cats as $cat): ?>
                    <a href="<?= APP_URL ?>/pages/report.php?category=<?= urlencode($cat[2]) ?>" class="category-card">
                        <i class="fas <?= $cat[0] ?>"></i>
                        <span><?= $cat[1] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===========================
     CTA SECTION
============================= -->
    <section class="section">
        <div class="container">
            <div class="card animate-on-scroll" style="text-align:center;padding:4rem 2rem;background:linear-gradient(135deg,rgba(0,212,255,0.08),rgba(124,58,237,0.08));border-color:var(--border-glow);">
                <div style="font-size:3rem;margin-bottom:1rem;">🚨</div>
                <h2 style="font-size:2rem;margin-bottom:1rem;">See Something? Report It.</h2>
                <p style="color:var(--text-secondary);font-size:1.05rem;max-width:500px;margin:0 auto 2rem;line-height:1.7;">Every report you submit makes your community safer. It takes less than 60 seconds and our AI handles the rest.</p>
                <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                    <a href="<?= APP_URL ?>/pages/report.php" class="btn btn-danger btn-xl">
                        <i class="fas fa-circle-exclamation"></i> Report Now
                    </a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-outline btn-xl">
                            <i class="fas fa-user-plus"></i> Create Account
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>