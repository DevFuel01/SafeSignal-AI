<?php if (!defined('APP_URL')) require_once __DIR__ . '/../config/config.php'; ?>
<footer class="footer" role="contentinfo">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <a href="<?= APP_URL ?>" class="footer-logo">
                    <div class="logo-icon"><i class="fas fa-shield-halved"></i></div>
                    <div class="logo-text">
                        <span class="logo-name">SafeSignal</span>
                        <span class="logo-ai">AI</span>
                    </div>
                </a>
                <p class="footer-tagline">Empowering communities through AI-driven safety intelligence. Every report makes the world safer.</p>
                <div class="sdg-badges-footer">
                    <span class="sdg-badge sdg-11" title="SDG 11: Sustainable Cities">
                        <i class="fas fa-city"></i> SDG 11
                    </span>
                    <span class="sdg-badge sdg-16" title="SDG 16: Peace & Justice">
                        <i class="fas fa-balance-scale"></i> SDG 16
                    </span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-links">
                <h3>Platform</h3>
                <ul>
                    <li><a href="<?= APP_URL ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="<?= APP_URL ?>/pages/map.php"><i class="fas fa-map-marked-alt"></i> Live Map</a></li>
                    <li><a href="<?= APP_URL ?>/pages/report.php"><i class="fas fa-circle-exclamation"></i> Report Incident</a></li>
                    <li><a href="<?= APP_URL ?>/pages/about.php"><i class="fas fa-circle-info"></i> About</a></li>
                    <li><a href="<?= APP_URL ?>/pages/contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
            </div>

            <!-- SDG Info -->
            <div class="footer-sdg">
                <h3>Our Impact</h3>
                <div class="footer-sdg-card">
                    <i class="fas fa-city"></i>
                    <div>
                        <strong>SDG 11</strong>
                        <p>Sustainable Cities & Communities — making urban environments inclusive and safe.</p>
                    </div>
                </div>
                <div class="footer-sdg-card">
                    <i class="fas fa-balance-scale"></i>
                    <div>
                        <strong>SDG 16</strong>
                        <p>Peace, Justice & Strong Institutions — reducing violence and enabling access to justice.</p>
                    </div>
                </div>
            </div>

            <!-- Emergency Contacts -->
            <div class="footer-emergency">
                <h3>Emergency Contacts</h3>
                <div class="emergency-number">
                    <i class="fas fa-phone-alt emergency-icon"></i>
                    <div>
                        <span class="em-label">Emergency Hotline</span>
                        <a href="tel:112" class="em-num">112</a>
                    </div>
                </div>
                <div class="emergency-number">
                    <i class="fas fa-fire-extinguisher emergency-icon"></i>
                    <div>
                        <span class="em-label">Fire Service</span>
                        <a href="tel:199" class="em-num">199</a>
                    </div>
                </div>
                <div class="emergency-number">
                    <i class="fas fa-shield-halved emergency-icon"></i>
                    <div>
                        <span class="em-label">Police</span>
                        <a href="tel:199" class="em-num">199</a>
                    </div>
                </div>
                <p class="footer-note">Powered by Gemini AI | Built for Hack4Hope TechHacks 2.0</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> SafeSignal AI. All rights reserved. | <a href="<?= APP_URL ?>/pages/about.php">About</a> | <a href="<?= APP_URL ?>/pages/contact.php">Contact</a></p>
            <div class="footer-social">
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </div>
</footer>

<!-- Leaflet JS (loaded on map pages) -->
<?php if (in_array(basename($_SERVER['PHP_SELF'], '.php'), ['map', 'report'])): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php endif; ?>

<!-- Main JS -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>

<?php if (isset($extraScript)) echo $extraScript; ?>
</body>

</html>