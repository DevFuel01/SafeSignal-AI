<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'About SafeSignal AI';
include __DIR__ . '/../partials/header.php';
?>
<main class="page-content">
    <div class="page-header">
        <div class="container">
            <div class="page-header-title">About SafeSignal AI</div>
            <p class="page-header-sub">Empowering communities through AI-driven safety intelligence, aligned with the UN Sustainable Development Goals.</p>
        </div>
    </div>

    <div class="container" style="padding-bottom:5rem;">

        <!-- Problem -->
        <div style="max-width:800px;margin:0 auto 4rem;">
            <div class="section-header" style="text-align:left;margin-bottom:2rem;">
                <span class="section-eyebrow">The Problem</span>
                <h2 class="section-title" style="text-align:left;">Communities Lack Real-Time Safety Intelligence</h2>
            </div>
            <div class="grid-2 animate-on-scroll">
                <?php foreach (
                    [
                        ['fa-eye-slash', 'Invisible Threats', 'Crimes, floods, and hazards go unreported due to lack of easy, accessible channels. Authorities act too late.'],
                        ['fa-clock-rotate-left', 'Slow Response', 'Incident information takes hours or days to reach authorities, costing lives and increasing damage.'],
                        ['fa-database', 'Missing Data', 'Authorities lack structured, geo-tagged incident data to make evidence-based urban safety decisions.'],
                        ['fa-people-group', 'Disengaged Communities', 'Citizens have no visible mechanism to contribute to community safety or track outcomes of their reports.'],
                    ] as $p
                ): ?>
                    <div class="card card-body">
                        <i class="fas <?= $p[0] ?>" style="font-size:2rem;color:var(--danger);margin-bottom:1rem;display:block;"></i>
                        <h3 style="font-size:1rem;margin-bottom:0.5rem;"><?= $p[1] ?></h3>
                        <p style="font-size:0.875rem;color:var(--text-secondary);line-height:1.6;"><?= $p[2] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Solution -->
        <div style="max-width:800px;margin:0 auto 4rem;">
            <div class="section-header" style="text-align:left;margin-bottom:2rem;">
                <span class="section-eyebrow">Our Solution</span>
                <h2 class="section-title" style="text-align:left;">How SafeSignal AI Helps</h2>
            </div>
            <div class="card animate-on-scroll" style="background:linear-gradient(135deg,rgba(0,212,255,0.05),rgba(124,58,237,0.05));border-color:var(--border-glow);margin-bottom:1.5rem;">
                <div class="card-body" style="padding:2rem;">
                    <p style="font-size:1.05rem;color:var(--text-secondary);line-height:1.8;">
                        SafeSignal AI is a community-driven, AI-powered safety platform that enables ordinary citizens to report incidents in under 60 seconds. Every report is immediately analyzed by <strong style="color:var(--primary);">Google Gemini AI</strong>, which classifies the incident, assesses severity, recommends action steps, and maps it to SDG goals. The report instantly appears on a live community map, triggering alerts for high-severity incidents — creating a real-time safety layer for cities.
                    </p>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <?php foreach (
                    [
                        ['fa-bolt', 'Instant AI Classification', 'Every report gets instant Gemini AI analysis — category, severity, summary, and recommended actions generated within seconds.'],
                        ['fa-map-location-dot', 'Live Safety Map', 'All incidents appear on an interactive map with color-coded severity markers, filterable by category, severity, and time.'],
                        ['fa-bell-concierge', 'Smart Alerts', 'High and critical severity reports automatically generate community alerts, creating a real-time protective network.'],
                        ['fa-shield-halved', 'Authority Dashboard', 'Authorities can review, verify, and resolve reports with full audit trails, enabling accountable, evidence-based responses.'],
                    ] as $s
                ): ?>
                    <div class="card card-body" style="display:flex;align-items:flex-start;gap:1.25rem;padding:1.25rem;">
                        <div style="width:48px;height:48px;border-radius:var(--radius);background:var(--primary-glow);border:1px solid var(--border-glow);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.25rem;flex-shrink:0;">
                            <i class="fas <?= $s[0] ?>"></i>
                        </div>
                        <div>
                            <h3 style="font-size:0.95rem;margin-bottom:0.4rem;"><?= $s[1] ?></h3>
                            <p style="font-size:0.875rem;color:var(--text-secondary);line-height:1.6;"><?= $s[2] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SDG Section -->
        <div style="max-width:800px;margin:0 auto 4rem;">
            <div class="section-header" style="text-align:left;margin-bottom:2rem;">
                <span class="section-eyebrow">UN SDG Alignment</span>
                <h2 class="section-title" style="text-align:left;">Why SDGs 11 & 16?</h2>
            </div>
            <div class="grid-2">
                <div class="card animate-on-scroll" style="border-color:rgba(251,146,60,0.3);">
                    <div class="card-body" style="padding:2rem;">
                        <span class="sdg-badge sdg-11" style="margin-bottom:1rem;display:inline-flex;font-size:0.875rem;padding:0.5rem 1rem;">
                            <i class="fas fa-city"></i> SDG 11
                        </span>
                        <h3 style="margin-bottom:0.75rem;">Sustainable Cities & Communities</h3>
                        <p style="font-size:0.875rem;color:var(--text-secondary);line-height:1.7;">SafeSignal directly addresses SDG 11.7 (safe public spaces) and 11.b (resilient city policies) by creating a real-time community safety network that generates geo-tagged incident data, enabling evidence-based urban safety planning.</p>
                    </div>
                </div>
                <div class="card animate-on-scroll delay-200" style="border-color:rgba(96,165,250,0.3);">
                    <div class="card-body" style="padding:2rem;">
                        <span class="sdg-badge sdg-16" style="margin-bottom:1rem;display:inline-flex;font-size:0.875rem;padding:0.5rem 1rem;">
                            <i class="fas fa-balance-scale"></i> SDG 16
                        </span>
                        <h3 style="margin-bottom:0.75rem;">Peace, Justice & Strong Institutions</h3>
                        <p style="font-size:0.875rem;color:var(--text-secondary);line-height:1.7;">We address SDG 16.1 (reduce violence) and 16.6 (develop accountable institutions) by enabling citizen participation in public safety governance and providing authorities with structured, verifiable incident data.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scalability -->
        <div style="max-width:800px;margin:0 auto;">
            <div class="section-header" style="text-align:left;margin-bottom:2rem;">
                <span class="section-eyebrow">The Future</span>
                <h2 class="section-title" style="text-align:left;">Scalability Vision</h2>
            </div>
            <div class="card animate-on-scroll" style="background:linear-gradient(135deg,rgba(124,58,237,0.06),rgba(0,212,255,0.06));border-color:rgba(124,58,237,0.3);">
                <div class="card-body" style="padding:2rem;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.5rem;">
                        <?php foreach (
                            [
                                ['fa-mobile-screen', 'Mobile App', 'Native iOS/Android apps for offline report submission and push notifications'],
                                ['fa-brain', 'Advanced AI', 'Gemini Vision for automated image analysis and threat detection from CCTV feeds'],
                                ['fa-building-columns', 'Gov Integration', 'Direct API integration with city management systems and national emergency services'],
                                ['fa-earth-africa', 'Multi-Language', 'Support for local languages to enable reporting across all demographics'],
                            ] as $v
                        ): ?>
                            <div style="text-align:center;">
                                <i class="fas <?= $v[0] ?>" style="font-size:2rem;color:var(--accent-light);display:block;margin-bottom:0.75rem;"></i>
                                <h4 style="font-size:0.875rem;margin-bottom:0.4rem;"><?= $v[1] ?></h4>
                                <p style="font-size:0.78rem;color:var(--text-muted);line-height:1.5;"><?= $v[2] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>