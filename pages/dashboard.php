<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();
$pageTitle = 'My Dashboard';
include __DIR__ . '/../partials/header.php';
?>

<main class="dashboard-layout">
    <div class="dashboard-header">
        <div class="container">
            <div class="dashboard-welcome">Welcome back, <span><?= h($_SESSION['user_name'] ?? 'User') ?></span> 👋</div>
            <p class="dashboard-subtitle">Your community safety hub — track your reports, view alerts, manage your profile.</p>
        </div>
    </div>

    <div class="container">
        <!-- Quick Stats -->
        <div class="user-stats-row" id="user-stats-row">
            <div class="user-stat"><span class="user-stat-num" id="stat-my-total">–</span><span class="user-stat-label">My Reports</span></div>
            <div class="user-stat"><span class="user-stat-num" id="stat-my-pending" style="color:var(--warning);">–</span><span class="user-stat-label">Pending</span></div>
            <div class="user-stat"><span class="user-stat-num" id="stat-my-verified" style="color:var(--primary);">–</span><span class="user-stat-label">Verified</span></div>
            <div class="user-stat"><span class="user-stat-num" id="stat-my-resolved" style="color:var(--success);">–</span><span class="user-stat-label">Resolved</span></div>
        </div>

        <!-- Tabs -->
        <div class="dashboard-tabs">
            <button class="dashboard-tab active" data-tab="my-reports"><i class="fas fa-file-alt"></i> My Reports</button>
            <button class="dashboard-tab" data-tab="notifications"><i class="fas fa-bell"></i> Alerts</button>
            <button class="dashboard-tab" data-tab="profile-tab"><i class="fas fa-user"></i> Profile</button>
        </div>

        <!-- My Reports Tab -->
        <div class="tab-pane active" id="my-reports">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.75rem;">
                <h2 style="font-size:1.1rem;">Reports I Submitted</h2>
                <a href="<?= APP_URL ?>/pages/report.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-plus"></i> New Report
                </a>
            </div>
            <div class="my-reports-list" id="my-reports-list">
                <div class="flex-center" style="padding:3rem;">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <!-- Alerts Tab -->
        <div class="tab-pane" id="notifications">
            <h2 style="font-size:1.1rem;margin-bottom:1.25rem;">Community Alerts</h2>
            <div class="notification-list" id="alerts-list">
                <div class="flex-center" style="padding:3rem;">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <!-- Profile Tab -->
        <div class="tab-pane" id="profile-tab">
            <div class="profile-card">
                <div class="profile-avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
                <h3 style="margin-bottom:1.5rem;"><?= h($_SESSION['user_name'] ?? '') ?></h3>
                <div id="profile-status" class="mb-3" style="display:none;"></div>
                <form id="profile-form">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="<?= h($_SESSION['user_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?= h($_SESSION['user_email'] ?? '') ?>" disabled style="opacity:0.6;cursor:not-allowed;">
                        <span class="form-help">Email cannot be changed.</span>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php
$extraScript = '<script>
"use strict";

// Tabs
document.querySelectorAll(".dashboard-tab").forEach(tab => {
    tab.addEventListener("click", () => {
        document.querySelectorAll(".dashboard-tab").forEach(t => t.classList.remove("active"));
        document.querySelectorAll(".tab-pane").forEach(p => p.classList.remove("active"));
        tab.classList.add("active");
        document.getElementById(tab.dataset.tab)?.classList.add("active");
        if (tab.dataset.tab === "notifications") loadAlerts();
    });
});

// Load user reports
async function loadMyReports() {
    const userId = ' . ($_SESSION['user_id'] ?? 0) . ';
    try {
        const res  = await fetch("../api/reports/list.php?limit=50");
        const json = await res.json();
        if (!json.success) return;
        
        // Filter to user's own reports (client-side for simplicity in demo)
        const reports = json.data.reports || [];
        
        document.getElementById("stat-my-total").textContent    = reports.length;
        document.getElementById("stat-my-pending").textContent  = reports.filter(r => r.status === "pending").length;
        document.getElementById("stat-my-verified").textContent = reports.filter(r => r.status === "verified").length;
        document.getElementById("stat-my-resolved").textContent = reports.filter(r => r.status === "resolved").length;
        
        const list = document.getElementById("my-reports-list");
        if (!reports.length) {
            list.innerHTML = `<div class="empty-state"><i class="fas fa-file-circle-xmark"></i><h3>No reports yet</h3><p>Submit your first community safety report.</p><a href="report.php" class="btn btn-danger btn-sm mt-2"><i class="fas fa-plus"></i> Report Now</a></div>`;
            return;
        }
        list.innerHTML = reports.map(r => `
            <div class="report-card">
                <div class="report-card-header">
                    <div>
                        <div class="report-card-title">${r.title.replace(/</g,"&lt;")}</div>
                        <div class="report-card-meta">
                            <span><i class="fas fa-tag"></i>${r.ai_category}</span>
                            <span><i class="fas fa-clock"></i>${timeAgo(r.created_at)}</span>
                            ${r.location_name ? `<span><i class="fas fa-location-dot"></i>${(r.location_name||"").replace(/</g,"&lt;")}</span>` : ""}
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:0.35rem;align-items:flex-end;">
                        ${severityBadge(r.ai_severity)}
                        ${statusBadge(r.status)}
                    </div>
                </div>
                <p class="report-card-summary line-clamp-2">${(r.ai_summary||r.description||"").replace(/</g,"&lt;")}</p>
                <div class="report-card-footer">
                    <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                        ${(r.sdg_list||[]).map(s => sdgBadge(s)).join("")}
                    </div>
                    <a href="map.php" class="btn btn-ghost btn-sm"><i class="fas fa-map-pin"></i> View on Map</a>
                </div>
            </div>
        `).join("");
    } catch(e) { console.error(e); }
}

async function loadAlerts() {
    try {
        const res  = await fetch("../api/alerts/latest.php");
        const json = await res.json();
        const list = document.getElementById("alerts-list");
        const alerts = json.data?.alerts || [];
        if (!alerts.length) {
            list.innerHTML = `<div class="empty-state"><i class="fas fa-bell-slash"></i><h3>No alerts</h3><p>No major incidents in the last 24 hours.</p></div>`;
            return;
        }
        list.innerHTML = alerts.map(a => {
            const sev = (a.severity||"").toLowerCase();
            const types = { critical:"alert", high:"alert", medium:"info", low:"success" };
            const icons = { critical:"fa-triangle-exclamation", high:"fa-circle-exclamation", medium:"fa-circle-info", low:"fa-circle-check" };
            return `<div class="notification-item ${!a.is_read ? "unread" : ""}">
                <div class="notif-icon ${types[sev]||"info"}"><i class="fas ${icons[sev]||"fa-bell"}"></i></div>
                <div class="notif-body">
                    <div class="notif-title">${(a.message||"").replace(/</g,"&lt;")}</div>
                    <div class="notif-time">${severityBadge(a.severity)} · ${timeAgo(a.created_at)}</div>
                </div>
            </div>`;
        }).join("");
    } catch(e) {}
}

loadMyReports();
</script>';
include __DIR__ . '/../partials/footer.php';
?>