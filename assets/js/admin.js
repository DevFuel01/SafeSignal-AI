/**
 * SafeSignal AI — Admin Dashboard JavaScript
 * Reports table, analytics charts, status management
 */

'use strict';

let allAdminReports = [];
let filteredReports  = [];
let currentViewReport = null;
let charts = {};
const PER_PAGE = 15;
let currentPage = 1;

// =====================================================
// PANEL NAVIGATION
// =====================================================
function switchPanel(panelId) {
    document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.admin-nav-link').forEach(l => l.classList.remove('active'));
    document.getElementById(panelId)?.classList.add('active');
    document.querySelector(`[data-panel="${panelId}"]`)?.classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
window.switchPanel = switchPanel;

// =====================================================
// LOAD REPORTS
// =====================================================
async function loadAdminReports(filters = {}) {
    const params = new URLSearchParams({ limit: 500, ...filters });
    try {
        const res  = await fetch(`${API_BASE}/reports/list.php?${params}`);
        const json = await res.json();
        if (!json.success) return;

        allAdminReports = json.data.reports || [];
        filteredReports = [...allAdminReports];
        currentPage = 1;
        renderTable();
        updateAdminStats();
        renderCharts();
    } catch(e) {
        console.error('Admin load error:', e);
    }
}

// =====================================================
// TABLE RENDER
// =====================================================
function renderTable() {
    const tbody = document.getElementById('reports-tbody');
    if (!tbody) return;

    const start = (currentPage - 1) * PER_PAGE;
    const page  = filteredReports.slice(start, start + PER_PAGE);

    if (!page.length) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No reports found</td></tr>`;
        renderPagination();
        return;
    }

    tbody.innerHTML = page.map(r => `
        <tr>
            <td><span style="color:var(--text-muted);font-size:0.78rem;">#${r.id}</span></td>
            <td>
                <div class="table-title" title="${escHtml(r.title)}">${escHtml(r.title)}</div>
                <div class="table-location">${r.location_name ? `<i class="fas fa-location-dot"></i> ${escHtml(r.location_name)}` : ''}</div>
            </td>
            <td><span style="font-size:0.8rem;">${escHtml(r.ai_category)}</span></td>
            <td>${severityBadge(r.ai_severity)}</td>
            <td>${statusBadge(r.status)}</td>
            <td><span style="color:var(--text-muted);font-size:0.78rem;">${timeAgo(r.created_at)}</span></td>
            <td><span style="color:var(--text-muted);font-size:0.78rem;">${r.confirm_count}</span></td>
            <td>
                <div class="table-actions">
                    <button class="tbl-btn view" title="View Details" onclick="openAdminDetail(${r.id})"><i class="fas fa-eye"></i></button>
                    ${r.status !== 'verified' ? `<button class="tbl-btn verify" title="Mark Verified" onclick="quickStatus(${r.id},'verified',this)"><i class="fas fa-shield-check"></i></button>` : ''}
                    ${r.status !== 'resolved' ? `<button class="tbl-btn resolve" title="Mark Resolved" onclick="quickStatus(${r.id},'resolved',this)"><i class="fas fa-check-double"></i></button>` : ''}
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination();
}

function renderPagination() {
    const totalPages = Math.ceil(filteredReports.length / PER_PAGE);
    const info = document.getElementById('table-info');
    const pageBtns = document.getElementById('page-btns');
    if (info) info.textContent = `Showing ${Math.min(filteredReports.length, (currentPage-1)*PER_PAGE+1)}–${Math.min(filteredReports.length, currentPage*PER_PAGE)} of ${filteredReports.length}`;
    if (!pageBtns) return;

    let html = `<button class="pg-btn" onclick="goPage(${currentPage-1})" ${currentPage<=1?'disabled':''}><i class="fas fa-chevron-left"></i></button>`;
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || Math.abs(i - currentPage) <= 2) {
            html += `<button class="pg-btn ${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</button>`;
        } else if (html.slice(-10) !== '.....</but') {
            html += `<span style="color:var(--text-muted);padding:0 0.25rem;font-size:0.8rem;">...</span>`;
        }
    }
    html += `<button class="pg-btn" onclick="goPage(${currentPage+1})" ${currentPage>=totalPages?'disabled':''}><i class="fas fa-chevron-right"></i></button>`;
    pageBtns.innerHTML = html;
}

function goPage(p) {
    const totalPages = Math.ceil(filteredReports.length / PER_PAGE);
    if (p < 1 || p > totalPages) return;
    currentPage = p;
    renderTable();
}
window.goPage = goPage;

// =====================================================
// ADMIN STATS
// =====================================================
function updateAdminStats() {
    const counts = {
        total:    allAdminReports.length,
        pending:  allAdminReports.filter(r => r.status   === 'pending').length,
        verified: allAdminReports.filter(r => r.status   === 'verified').length,
        resolved: allAdminReports.filter(r => r.status   === 'resolved').length,
        critical: allAdminReports.filter(r => r.ai_severity === 'Critical').length,
    };

    Object.keys(counts).forEach(key => {
        const el = document.getElementById(`stat-${key}`);
        if (el) {
            el.textContent = '0';
            animateCounter(el, counts[key]);
        }
    });
}

// =====================================================
// CHARTS
// =====================================================
function renderCharts() {
    renderCategoryChart();
    renderSeverityChart();
    renderTimelineChart();
    renderStatusChart();
}

function chartDefaults() {
    return {
        plugins: {
            legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } } },
        },
        scales: {
            x: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.05)' }, border: { color: 'rgba(255,255,255,0.1)' } },
            y: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.05)' }, border: { color: 'rgba(255,255,255,0.1)' } },
        },
    };
}

function renderCategoryChart() {
    const canvas = document.getElementById('category-chart');
    if (!canvas) return;
    const catCounts = {};
    allAdminReports.forEach(r => { catCounts[r.ai_category] = (catCounts[r.ai_category] || 0) + 1; });
    const labels = Object.keys(catCounts);
    const data   = Object.values(catCounts);
    const colors = ['#00d4ff','#7c3aed','#ef4444','#f59e0b','#10b981','#a78bfa','#60a5fa','#34d399','#fb923c'];

    if (charts.category) charts.category.destroy();
    charts.category = new Chart(canvas, {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderColor: 'rgba(8,12,20,0.8)', borderWidth: 2 }] },
        options: { plugins: { legend: { position: 'right', labels: { color: '#94a3b8', font: { size: 10 }, boxWidth: 12, padding: 10 } } }, cutout: '65%' },
    });
}

function renderSeverityChart() {
    const canvas = document.getElementById('severity-chart');
    if (!canvas) return;
    const sevMap = { Critical: 0, High: 0, Medium: 0, Low: 0 };
    allAdminReports.forEach(r => { if (sevMap[r.ai_severity] !== undefined) sevMap[r.ai_severity]++; });

    if (charts.severity) charts.severity.destroy();
    charts.severity = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: Object.keys(sevMap),
            datasets: [{
                label: 'Reports',
                data: Object.values(sevMap),
                backgroundColor: ['rgba(255,45,85,0.7)','rgba(239,68,68,0.7)','rgba(245,158,11,0.7)','rgba(16,185,129,0.7)'],
                borderColor:     ['#ff2d55','#ef4444','#f59e0b','#10b981'],
                borderWidth: 1, borderRadius: 6,
            }],
        },
        options: { ...chartDefaults(), plugins: { legend: { display: false } } },
    });
}

function renderTimelineChart() {
    const canvas = document.getElementById('timeline-chart');
    if (!canvas) return;

    // Group by day (last 14 days)
    const days = {};
    for (let i = 13; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        days[d.toISOString().split('T')[0]] = 0;
    }
    allAdminReports.forEach(r => {
        const day = r.created_at.split(' ')[0] || r.created_at.split('T')[0];
        if (days[day] !== undefined) days[day]++;
    });

    if (charts.timeline) charts.timeline.destroy();
    charts.timeline = new Chart(canvas, {
        type: 'line',
        data: {
            labels: Object.keys(days).map(d => {
                const dt = new Date(d);
                return dt.toLocaleDateString('en', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Reports',
                data: Object.values(days),
                borderColor: '#00d4ff',
                backgroundColor: 'rgba(0,212,255,0.08)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#00d4ff',
            }],
        },
        options: { ...chartDefaults(), plugins: { legend: { display: false } } },
    });
}

function renderStatusChart() {
    const canvas = document.getElementById('status-chart');
    if (!canvas) return;
    const statusMap = { pending: 0, verified: 0, resolved: 0 };
    allAdminReports.forEach(r => { if (statusMap[r.status] !== undefined) statusMap[r.status]++; });

    if (charts.status) charts.status.destroy();
    charts.status = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Verified', 'Resolved'],
            datasets: [{
                data: Object.values(statusMap),
                backgroundColor: ['rgba(245,158,11,0.7)','rgba(0,212,255,0.7)','rgba(16,185,129,0.7)'],
                borderColor:     ['#f59e0b','#00d4ff','#10b981'],
                borderWidth: 1, borderRadius: 6,
            }],
        },
        options: { ...chartDefaults(), plugins: { legend: { display: false } }, indexAxis: 'y' },
    });
}

// =====================================================
// ADMIN REPORT DETAIL
// =====================================================
async function openAdminDetail(id) {
    const overlay = document.getElementById('admin-detail-overlay');
    const body    = document.getElementById('admin-detail-body');
    if (!overlay || !body) return;

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    body.innerHTML = `<div class="flex-center" style="padding:3rem;"><div class="spinner"></div></div>`;

    try {
        const res  = await fetch(`${API_BASE}/reports/get.php?id=${id}`);
        const json = await res.json();
        if (!json.success) return;

        currentViewReport = json.data.report;
        const r = currentViewReport;
        const sdgs = (r.sdg_list||[]).map(s => sdgBadge(s)).join(' ');
        const actions = (r.ai_recommended_actions||[]).map((a,i) => `<li><div class="step-icon">${i+1}</div>${escHtml(a)}</li>`).join('');

        body.innerHTML = `
            ${r.image_url ? `<img src="${r.image_url}" alt="Incident" class="modal-img" onerror="this.style.display='none'">` : ''}
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;">${severityBadge(r.ai_severity)} ${statusBadge(r.status)} ${sdgs}</div>
            <h2 style="font-size:1.1rem;margin-bottom:0.75rem;">${escHtml(r.title)}</h2>

            <div class="detail-section">
                <h4>Report Details</h4>
                <div class="detail-grid">
                    <div class="detail-field"><label>Category</label><span>${escHtml(r.ai_category)}</span></div>
                    <div class="detail-field"><label>Severity</label><span>${severityBadge(r.ai_severity)}</span></div>
                    <div class="detail-field"><label>Status</label><span>${statusBadge(r.status)}</span></div>
                    <div class="detail-field"><label>Confirmations</label><span>${r.confirm_count}</span></div>
                    <div class="detail-field"><label>Reporter</label><span>${escHtml(r.reporter_name)}</span></div>
                    <div class="detail-field"><label>Submitted</label><span>${new Date(r.created_at).toLocaleString()}</span></div>
                    <div class="detail-field"><label>Location</label><span>${r.location_name || `${r.latitude}, ${r.longitude}`}</span></div>
                    <div class="detail-field"><label>SDG Tags</label><span>${escHtml(r.sdg_tags)}</span></div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Description</h4>
                <p style="color:var(--text-secondary);font-size:0.875rem;line-height:1.7;">${escHtml(r.description)}</p>
            </div>

            <div class="detail-section">
                <h4>AI Analysis</h4>
                <p style="color:var(--text-secondary);font-size:0.875rem;line-height:1.6;margin-bottom:0.75rem;">${escHtml(r.ai_summary||'No AI summary available.')}</p>
                ${actions ? `<ul class="ai-actions-list">${actions}</ul>` : ''}
            </div>

            ${r.admin_note ? `<div class="detail-section"><h4>Previous Admin Note</h4><p style="color:var(--text-secondary);font-size:0.875rem;">${escHtml(r.admin_note)}</p></div>` : ''}

            <div class="status-update-form">
                <h4><i class="fas fa-shield-halved"></i> Update Status</h4>
                <div class="form-row cols-2" style="margin-bottom:0.75rem;">
                    <div class="form-group mb-0">
                        <label class="form-label">New Status</label>
                        <select class="form-control" id="admin-status-select">
                            <option value="pending"  ${r.status==='pending'  ?'selected':''}>Pending</option>
                            <option value="verified" ${r.status==='verified' ?'selected':''}>Verified</option>
                            <option value="resolved" ${r.status==='resolved' ?'selected':''}>Resolved</option>
                        </select>
                    </div>
                    <div class="form-group mb-0" style="align-self:end;">
                        <button class="btn btn-primary w-100" onclick="submitAdminStatus(${r.id})">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Admin Note / Resolution Details</label>
                    <textarea class="form-control" id="admin-note-input" rows="3" placeholder="Add context, resolution steps, or notes for the community...">${escHtml(r.admin_note||'')}</textarea>
                </div>
            </div>
        `;

        document.getElementById('admin-detail-title').textContent = r.title;
    } catch(e) {
        body.innerHTML = `<div class="alert-msg error"><i class="fas fa-circle-xmark"></i> Error loading report.</div>`;
    }
}

async function submitAdminStatus(reportId) {
    const status    = document.getElementById('admin-status-select')?.value;
    const adminNote = document.getElementById('admin-note-input')?.value || '';

    try {
        const res  = await fetch(`${API_BASE}/admin/reports/status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ report_id: reportId, status, admin_note: adminNote }),
        });
        const json = await res.json();

        if (json.success) {
            Toast.show('Status Updated', `Report marked as ${status}.`, 'success');
            Modal.closeAll();
            loadAdminReports(); // Refresh
        } else {
            Toast.show('Error', json.message, 'error');
        }
    } catch(e) {
        Toast.show('Error', 'Failed to update status.', 'error');
    }
}

async function quickStatus(id, status, btn) {
    try {
        const res  = await fetch(`${API_BASE}/admin/reports/status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ report_id: id, status }),
        });
        const json = await res.json();
        if (json.success) {
            Toast.show('Updated', `Report marked as ${status}.`, 'success', 3000);
            loadAdminReports();
        } else {
            Toast.show('Error', json.message, 'error');
        }
    } catch(e) {
        Toast.show('Error', 'Update failed.', 'error');
    }
}

window.openAdminDetail   = openAdminDetail;
window.submitAdminStatus = submitAdminStatus;
window.quickStatus       = quickStatus;

// =====================================================
// FILTERS & SEARCH
// =====================================================
function applyAdminFilters() {
    const search   = (document.getElementById('admin-search')?.value || '').toLowerCase();
    const category = document.getElementById('admin-cat-filter')?.value || '';
    const severity = document.getElementById('admin-sev-filter')?.value || '';
    const status   = document.getElementById('admin-status-filter')?.value || '';

    filteredReports = allAdminReports.filter(r => {
        const matchSearch = !search ||
            r.title.toLowerCase().includes(search) ||
            (r.location_name||'').toLowerCase().includes(search) ||
            (r.ai_summary||'').toLowerCase().includes(search);
        const matchCat  = !category || r.ai_category === category;
        const matchSev  = !severity || r.ai_severity === severity;
        const matchStat = !status   || r.status === status;
        return matchSearch && matchCat && matchSev && matchStat;
    });
    currentPage = 1;
    renderTable();
}

// =====================================================
// CSV EXPORT
// =====================================================
function exportCSV() {
    if (!filteredReports.length) {
        Toast.show('No Data', 'No reports to export.', 'warning');
        return;
    }
    const headers = ['ID','Title','Category','Severity','Status','Location','Reporter','Created At','Confirmations','SDG Tags'];
    const rows = filteredReports.map(r => [
        r.id, `"${(r.title||'').replace(/"/g,'""')}"`, r.ai_category, r.ai_severity,
        r.status, `"${(r.location_name||r.latitude+','+r.longitude||'').replace(/"/g,'""')}"`,
        r.reporter_name, r.created_at, r.confirm_count, r.sdg_tags
    ]);
    const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = `safesignal_reports_${Date.now()}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    Toast.show('Exported!', `${filteredReports.length} reports exported to CSV.`, 'success');
}
window.exportCSV = exportCSV;

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// =====================================================
// INIT
// =====================================================
document.addEventListener('DOMContentLoaded', () => {
    loadAdminReports();

    // Filter events
    ['admin-search','admin-cat-filter','admin-sev-filter','admin-status-filter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener(id === 'admin-search' ? 'input' : 'change', applyAdminFilters);
        }
    });

    // Nav links
    document.querySelectorAll('.admin-nav-link[data-panel]').forEach(link => {
        link.addEventListener('click', () => switchPanel(link.dataset.panel));
    });

    // Modal close
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.modal-overlay').forEach(o => o.classList.remove('open'));
            document.body.style.overflow = '';
        });
    });


    // Overlay click to close
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    });
});
