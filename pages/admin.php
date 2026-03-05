<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();
$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../partials/header.php';
?>

<div class="admin-layout">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin navigation">
        <div style="padding:1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:0.6rem;">
            <div class="logo-icon" style="width:30px;height:30px;font-size:0.9rem;"><i class="fas fa-shield-cat"></i></div>
            <div>
                <div style="font-size:0.875rem;font-weight:700;color:var(--text-primary);">Admin Panel</div>
                <div style="font-size:0.72rem;color:var(--text-muted);"><?= h($_SESSION['user_name'] ?? 'Admin') ?></div>
            </div>
        </div>

        <div class="admin-nav-section">
            <div class="admin-nav-title">Overview</div>
            <a class="admin-nav-link active" data-panel="panel-overview" href="#"><i class="fas fa-gauge-high"></i> Dashboard</a>
            <a class="admin-nav-link" data-panel="panel-analytics" href="#"><i class="fas fa-chart-bar"></i> Analytics</a>
        </div>

        <div class="admin-nav-section">
            <div class="admin-nav-title">Incident Management</div>
            <a class="admin-nav-link" data-panel="panel-reports" href="#"><i class="fas fa-file-circle-exclamation"></i> All Reports <span class="badge" id="pending-badge">–</span></a>
            <a class="admin-nav-link" data-panel="panel-alerts" href="#"><i class="fas fa-bell"></i> Alerts</a>
        </div>

        <div class="admin-nav-section">
            <div class="admin-nav-title">Community</div>
            <a class="admin-nav-link" data-panel="panel-messages" href="#"><i class="fas fa-envelope"></i> Messages</a>
        </div>

        <div style="padding:1rem;margin-top:auto;border-top:1px solid var(--border);">
            <a href="<?= APP_URL ?>/pages/map.php" class="admin-nav-link"><i class="fas fa-map"></i> Live Map</a>
            <a href="<?= APP_URL ?>/" class="admin-nav-link"><i class="fas fa-home"></i> Public Site</a>
            <a href="#" id="admin-logout-btn" class="admin-nav-link" style="color:var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Admin Main -->
    <main class="admin-main">

        <!-- OVERVIEW PANEL -->
        <div id="panel-overview" class="admin-panel active">
            <div class="admin-page-header">
                <h1 class="admin-page-title">Overview Dashboard</h1>
                <div style="display:flex;gap:0.75rem;align-items:center;">
                    <span style="font-size:0.78rem;color:var(--text-muted);">Data refreshes automatically</span>
                    <button onclick="loadAdminReports()" class="btn btn-ghost btn-sm"><i class="fas fa-rotate"></i> Refresh</button>
                </div>
            </div>

            <div class="admin-stats-grid">
                <div class="admin-stat-card stat-total">
                    <div class="admin-stat-num text-primary-color" id="stat-total">–</div>
                    <div class="admin-stat-label">Total Reports</div>
                    <i class="fas fa-file-alt admin-stat-icon"></i>
                </div>
                <div class="admin-stat-card stat-pending">
                    <div class="admin-stat-num" style="color:var(--warning);" id="stat-pending">–</div>
                    <div class="admin-stat-label">Pending Review</div>
                    <i class="fas fa-clock admin-stat-icon"></i>
                </div>
                <div class="admin-stat-card stat-verified">
                    <div class="admin-stat-num text-primary-color" id="stat-verified">–</div>
                    <div class="admin-stat-label">Verified</div>
                    <i class="fas fa-shield-halved admin-stat-icon"></i>
                </div>
                <div class="admin-stat-card stat-resolved">
                    <div class="admin-stat-num" style="color:var(--success);" id="stat-resolved">–</div>
                    <div class="admin-stat-label">Resolved</div>
                    <i class="fas fa-check-double admin-stat-icon"></i>
                </div>
                <div class="admin-stat-card stat-critical">
                    <div class="admin-stat-num" style="color:var(--critical);" id="stat-critical">–</div>
                    <div class="admin-stat-label">Critical Incidents</div>
                    <i class="fas fa-skull-crossbones admin-stat-icon"></i>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-card-title">Reports by Category</div>
                    <div class="chart-wrapper"><canvas id="category-chart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-card-title">Reports by Severity</div>
                    <div class="chart-wrapper"><canvas id="severity-chart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- ANALYTICS PANEL -->
        <div id="panel-analytics" class="admin-panel">
            <div class="admin-page-header">
                <h1 class="admin-page-title">Analytics & Trends</h1>
            </div>
            <div class="charts-grid">
                <div class="chart-card" style="grid-column:1/-1;">
                    <div class="chart-card-title">Incidents Over Last 14 Days</div>
                    <div class="chart-wrapper" style="height:300px;"><canvas id="timeline-chart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-card-title">Resolution Status</div>
                    <div class="chart-wrapper"><canvas id="status-chart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-card-title">Severity Distribution</div>
                    <div class="chart-wrapper"><canvas id="severity-chart-2"></canvas></div>
                </div>
            </div>
        </div>

        <!-- REPORTS PANEL -->
        <div id="panel-reports" class="admin-panel">
            <div class="admin-page-header">
                <h1 class="admin-page-title">All Reports</h1>
                <button class="export-btn" onclick="exportCSV()"><i class="fas fa-download"></i> Export CSV</button>
            </div>

            <div class="table-wrapper">
                <div class="table-toolbar">
                    <div class="search-box" style="flex:1;min-width:200px;">
                        <i class="fas fa-search"></i>
                        <input type="text" id="admin-search" class="form-control" placeholder="Search reports..." style="padding-left:2.5rem;height:36px;font-size:0.85rem;border-radius:var(--radius-full);">
                    </div>
                    <select id="admin-cat-filter" class="form-control" style="width:180px;height:36px;font-size:0.82rem;">
                        <option value="">All Categories</option>
                        <?php foreach (['Crime', 'Flood', 'Fire', 'Harassment', 'Accident', 'Infrastructure Damage', 'Pollution', 'Medical Emergency', 'General Safety'] as $cat): ?>
                            <option value="<?= h($cat) ?>"><?= h($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="admin-sev-filter" class="form-control" style="width:140px;height:36px;font-size:0.82rem;">
                        <option value="">All Severities</option>
                        <option value="Critical">Critical</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                    <select id="admin-status-filter" class="form-control" style="width:140px;height:36px;font-size:0.82rem;">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title / Location</th>
                                <th>Category</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Confirms</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reports-tbody">
                            <tr>
                                <td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">
                                    <div class="spinner" style="margin:0 auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-pagination">
                    <span id="table-info" style="color:var(--text-muted);"></span>
                    <div class="pagination-btns" id="page-btns"></div>
                </div>
            </div>
        </div>

        <!-- ALERTS PANEL -->
        <div id="panel-alerts" class="admin-panel">
            <div class="admin-page-header">
                <h1 class="admin-page-title">System Alerts</h1>
            </div>
            <div id="admin-alerts-list" class="card" style="padding:1.5rem;">
                <div class="flex-center" style="padding:2rem;">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <!-- MESSAGES PANEL -->
        <div id="panel-messages" class="admin-panel">
            <div class="admin-page-header">
                <h1 class="admin-page-title">Contact Messages</h1>
            </div>
            <div id="admin-messages-list" class="table-wrapper">
                <div class="flex-center" style="padding:2rem;">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Report Detail Modal -->
<div class="modal-overlay" id="admin-detail-overlay" role="dialog" aria-modal="true" aria-labelledby="admin-detail-title">
    <div class="modal" style="max-width:760px;">
        <div class="modal-header">
            <h2 class="modal-title" id="admin-detail-title">Report Detail</h2>
            <button class="modal-close" data-modal-close aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="admin-detail-body">
            <div class="flex-center" style="padding:3rem;">
                <div class="spinner"></div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScript = '<script src="' . APP_URL . '/assets/js/admin.js"></script>
<script>
// Load alerts for alerts panel
async function loadAdminAlerts() {
    const res  = await fetch("../api/alerts/latest.php");
    const json = await res.json();
    const container = document.getElementById("admin-alerts-list");
    const alerts = json.data?.alerts || [];
    if (!alerts.length) {
        container.innerHTML = \'<div class="empty-state"><i class="fas fa-bell-slash"></i><h3>No alerts</h3></div>\';
        return;
    }
    container.innerHTML = alerts.map(a => `
        <div class="notification-item" style="margin-bottom:0.5rem;">
            <div class="notif-icon alert"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="notif-body">
                <div class="notif-title">${(a.message||"").replace(/</g,"&lt;")}</div>
                <div class="notif-time">${severityBadge(a.severity)} · ${timeAgo(a.created_at)}</div>
            </div>
            ${a.report_id ? `<a href="#" onclick="openAdminDetail(${a.report_id});switchPanel(\'panel-reports\');return false;" class="btn btn-ghost btn-sm">View Report</a>` : ""}
        </div>
    `).join("");
}

// Messages
async function loadAdminMessages() {
    const container = document.getElementById("admin-messages-list");
    try {
        const res  = await fetch("../api/admin/messages/list.php");
        const json = await res.json();
        const data = json.data?.messages || [];

        if (!data.length) {
            container.innerHTML = `
                <div class="empty-state" style="padding:3rem;">
                    <i class="fas fa-envelope-open-text" style="font-size:3rem;color:var(--text-muted);margin-bottom:1rem;"></i>
                    <h3>No messages yet</h3>
                    <p>Contact submissions will appear here.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = `
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(m => `
                            <tr>
                                <td style="font-weight:600;">${(m.name||"").replace(/</g,"&lt;")}</td>
                                <td style="font-size:0.8rem;color:var(--text-muted);">${(m.email||"").replace(/</g,"&lt;")}</td>
                                <td style="font-size:0.875rem;">${(m.subject||"").replace(/</g,"&lt;")}</td>
                                <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.85rem;" title="${(m.message||"").replace(/"/g,"&quot;")}">
                                    ${(m.message||"").replace(/</g,"&lt;")}
                                </td>
                                <td style="font-size:0.75rem;color:var(--text-muted);">${timeAgo(m.created_at)}</td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            </div>
        `;
    } catch(e) {
        container.innerHTML = `
            <div class="alert-msg error">
                <i class="fas fa-triangle-exclamation"></i>
                <span>Failed to load messages.</span>
            </div>
        `;
    }
}

// Nav clicks
document.querySelectorAll(".admin-nav-link[data-panel]").forEach(link => {
    link.addEventListener("click", e => {
        e.preventDefault();
        const panel = link.dataset.panel;
        switchPanel(panel);
        if (panel === "panel-alerts") loadAdminAlerts();
        if (panel === "panel-messages") loadAdminMessages();
    });
});

// Update pending badge
setInterval(async () => {
    const badge = document.getElementById("pending-badge");
    if (badge && window.allAdminReports) {
        const pending = allAdminReports.filter(r => r.status === "pending").length;
        badge.textContent = pending || "–";
    }
}, 5000);
</script>';
include __DIR__ . '/../partials/footer.php';
?>