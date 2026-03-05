<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Live Incident Map';
include __DIR__ . '/../partials/header.php';
?>

<main class="page-content map-page">
    <div class="map-layout">
        <!-- Sidebar -->
        <aside class="map-sidebar" id="map-sidebar" aria-label="Report filters and list">
            <div class="sidebar-header">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                    <span class="sidebar-title">Live Reports</span>
                    <span class="report-count-badge" id="report-count-badge">0</span>
                </div>

                <!-- Search -->
                <div class="search-box mb-2">
                    <i class="fas fa-search"></i>
                    <input type="text" id="map-search" class="form-control" placeholder="Search incidents..." aria-label="Search reports">
                </div>

                <!-- Severity Filters -->
                <div style="margin-bottom:0.5rem;">
                    <div style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;font-weight:600;">Severity</div>
                    <div class="filter-row">
                        <button class="filter-chip active-danger" data-filter="severity" data-value="Critical">🔴 Critical</button>
                        <button class="filter-chip active-danger" data-filter="severity" data-value="High">🟠 High</button>
                        <button class="filter-chip active-warning" data-filter="severity" data-value="Medium">🟡 Medium</button>
                        <button class="filter-chip active-success" data-filter="severity" data-value="Low">🟢 Low</button>
                    </div>
                </div>

                <!-- Status Filters -->
                <div style="margin-bottom:0.5rem;">
                    <div style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;font-weight:600;">Status</div>
                    <div class="filter-row">
                        <button class="filter-chip" data-filter="status" data-value="pending"><i class="fas fa-clock"></i> Pending</button>
                        <button class="filter-chip" data-filter="status" data-value="verified"><i class="fas fa-check"></i> Verified</button>
                        <button class="filter-chip" data-filter="status" data-value="resolved"><i class="fas fa-check-double"></i> Resolved</button>
                    </div>
                </div>

                <!-- Time Filters -->
                <div>
                    <div style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;font-weight:600;">Time Range</div>
                    <div class="filter-row">
                        <button class="filter-chip" data-filter="time" data-value="24h"><i class="fas fa-clock"></i> 24h</button>
                        <button class="filter-chip" data-filter="time" data-value="7d">7 Days</button>
                        <button class="filter-chip" data-filter="time" data-value="30d">30 Days</button>
                    </div>
                </div>
            </div>

            <!-- Report list -->
            <div class="sidebar-list" id="sidebar-list" role="list" aria-label="Incident reports list">
                <div class="flex-center" style="padding:3rem;flex-direction:column;gap:1rem;">
                    <div class="spinner"></div>
                    <span style="font-size:0.8rem;color:var(--text-muted);">Loading incidents...</span>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:0.75rem;border-top:1px solid var(--border);flex-shrink:0;">
                <a href="<?= APP_URL ?>/pages/report.php" class="btn btn-danger w-100 btn-sm">
                    <i class="fas fa-plus"></i> Report New Incident
                </a>
            </div>
        </aside>

        <!-- Sidebar Toggle -->
        <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
            <i class="fas fa-chevron-left" id="sidebar-toggle-icon"></i>
        </button>

        <!-- Map -->
        <div class="map-container">
            <div id="main-map"></div>

            <!-- Map Controls -->
            <div class="map-controls">
                <button class="map-control-btn" id="refresh-map-btn" title="Refresh map data">
                    <i class="fas fa-rotate"></i>
                </button>
                <button class="map-control-btn" id="locate-btn" title="Go to my location">
                    <i class="fas fa-location-crosshairs"></i>
                </button>
                <button class="map-control-btn" id="mobile-sidebar-btn" title="Toggle filters" style="display:none;">
                    <i class="fas fa-filter"></i>
                </button>
            </div>

            <!-- Legend -->
            <div class="map-legend">
                <div class="legend-title">Severity Legend</div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:var(--sev-critical);box-shadow:0 0 6px var(--sev-critical);animation:glow-pulse 1.5s infinite;"></div> Critical
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:var(--sev-high);"></div> High
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:var(--sev-medium);"></div> Medium
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:var(--sev-low);"></div> Low
                </div>
            </div>
        </div>
    </div>

    <!-- Report Detail Modal -->
    <div class="modal-overlay" id="report-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title" id="report-modal-title">Report Details</h2>
                <button class="modal-close" data-modal-close aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="report-modal-body">
                <div class="flex-center" style="padding:3rem;">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$extraScript = '<script src="' . APP_URL . '/assets/js/map.js"></script>
<script>
// Sidebar toggle
const sidebarToggle = document.getElementById("sidebar-toggle");
const sidebar = document.getElementById("map-sidebar");
const icon = document.getElementById("sidebar-toggle-icon");
sidebarToggle?.addEventListener("click", () => {
    const collapsed = sidebar.classList.toggle("collapsed");
    icon.classList.toggle("fa-chevron-left", !collapsed);
    icon.classList.toggle("fa-chevron-right", collapsed);
    sidebarToggle.style.left = collapsed ? "0" : "360px";
    setTimeout(() => map?.invalidateSize(), 300);
});
// Mobile
if (window.innerWidth <= 768) {
    document.getElementById("mobile-sidebar-btn").style.display = "flex";
}
</script>';
include __DIR__ . '/../partials/footer.php';
?>