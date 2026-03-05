/**
 * SafeSignal AI — Map JavaScript
 * Leaflet map, markers, filters, report modal
 */

'use strict';

let map;
let markers = [];
let allReports = [];
let activeFilters = { category: '', severity: '', status: '', time: '', search: '' };

// =====================================================
// INIT MAP
// =====================================================
function initMap() {
    // Center on Lagos, Nigeria (demo seed data location)
    map = L.map('main-map', {
        center: [6.4981, 3.3479],
        zoom: 13,
        zoomControl: false,
        attributionControl: true,
    });

    // Dark tile layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
    }).addTo(map);

    // Zoom controls (custom position)
    L.control.zoom({ position: 'topright' }).addTo(map);

    loadReports();
}

// =====================================================
// LOAD & RENDER REPORTS
// =====================================================
async function loadReports() {
    try {
        const params = new URLSearchParams();
        if (activeFilters.category) params.set('category', activeFilters.category);
        if (activeFilters.severity) params.set('severity', activeFilters.severity);
        if (activeFilters.status)   params.set('status',   activeFilters.status);
        if (activeFilters.time)     params.set('time',     activeFilters.time);
        if (activeFilters.search)   params.set('search',   activeFilters.search);
        params.set('limit', '100');

        const res  = await fetch(`${API_BASE}/reports/list.php?${params}`);
        const json = await res.json();

        if (!json.success) {
            console.error('Failed to load reports:', json.message);
            return;
        }

        allReports = json.data.reports || [];
        clearMarkers();
        renderMarkers(allReports);
        renderSidebar(allReports);
        updateReportCount(json.data.total || allReports.length);
    } catch(e) {
        console.error('Map load error:', e);
    }
}

function clearMarkers() {
    markers.forEach(m => m.remove());
    markers = [];
}

// =====================================================
// CREATE CUSTOM MARKERS
// =====================================================
function createMarkerIcon(severity, category) {
    const sev = (severity || 'medium').toLowerCase();
    const iconName = categoryIcon(category);
    const size = sev === 'critical' ? 44 : sev === 'high' ? 40 : 36;

    return L.divIcon({
        html: `<div class="marker-icon marker-${sev}"><i class="fas ${iconName}"></i></div>`,
        className: '',
        iconSize: [size, size],
        iconAnchor: [size / 2, size],
        popupAnchor: [0, -size],
    });
}

function renderMarkers(reports) {
    reports.forEach(report => {
        if (!report.latitude || !report.longitude) return;

        const icon   = createMarkerIcon(report.ai_severity, report.ai_category);
        const marker = L.marker([report.latitude, report.longitude], { icon });

        const sev = (report.ai_severity || '').toLowerCase();
        const popup = `
            <div class="popup-content">
                <div class="popup-title">${escHtml(report.title)}</div>
                <div class="popup-meta">
                    <span>${severityBadge(report.ai_severity)}</span>
                    <span>${statusBadge(report.status)}</span>
                </div>
                <div class="popup-meta" style="margin-top:0.35rem">
                    <span><i class="fas fa-tag"></i> ${escHtml(report.ai_category)}</span>
                    ${report.location_name ? `<span><i class="fas fa-location-dot"></i> ${escHtml(report.location_name)}</span>` : ''}
                </div>
                <div class="popup-actions">
                    <button class="popup-btn primary" onclick="openReportModal(${report.id})">
                        <i class="fas fa-expand"></i> Details
                    </button>
                    <button class="popup-btn" onclick="confirmReport(${report.id}, this)">
                        <i class="fas fa-check-double"></i> Confirm (${report.confirm_count})
                    </button>
                </div>
            </div>
        `;

        marker.bindPopup(popup, { maxWidth: 280, minWidth: 220 });
        marker.on('click', () => {
            highlightSidebarItem(report.id);
        });

        marker.addTo(map);
        markers.push(marker);
        marker._reportId = report.id;
    });
}

// =====================================================
// SIDEBAR REPORT LIST
// =====================================================
function renderSidebar(reports) {
    const list = document.getElementById('sidebar-list');
    if (!list) return;

    if (!reports.length) {
        list.innerHTML = `<div class="empty-state"><i class="fas fa-map-location-dot"></i><h3>No reports found</h3><p>Try adjusting your filters or adding a new report.</p></div>`;
        return;
    }

    list.innerHTML = reports.map(report => `
        <div class="sidebar-report-item" data-id="${report.id}" onclick="openReportModal(${report.id})" role="button" tabindex="0">
            <div class="sri-title">${escHtml(report.title)}</div>
            <div style="display:flex;gap:0.4rem;align-items:center;flex-wrap:wrap;margin-bottom:0.35rem;">
                ${severityBadge(report.ai_severity)}
                ${statusBadge(report.status)}
            </div>
            <div class="sri-meta">
                <span><i class="fas fa-tag"></i>${escHtml(report.ai_category)}</span>
                <span><i class="fas fa-clock"></i>${timeAgo(report.created_at)}</span>
                ${report.confirm_count ? `<span><i class="fas fa-users"></i>${report.confirm_count}</span>` : ''}
            </div>
        </div>
    `).join('');

    // Keyboard support
    list.querySelectorAll('.sidebar-report-item').forEach(item => {
        item.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') item.click();
        });
    });
}

function highlightSidebarItem(id) {
    document.querySelectorAll('.sidebar-report-item').forEach(el => el.classList.remove('active'));
    const target = document.querySelector(`.sidebar-report-item[data-id="${id}"]`);
    if (target) {
        target.classList.add('active');
        target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function updateReportCount(count) {
    const badge = document.getElementById('report-count-badge');
    if (badge) badge.textContent = count;
}

// =====================================================
// REPORT DETAIL MODAL
// =====================================================
async function openReportModal(id) {
    const overlay = document.getElementById('report-modal-overlay');
    if (!overlay) return;

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';

    const body = document.getElementById('report-modal-body');
    body.innerHTML = `<div class="flex-center" style="padding:3rem;"><div class="spinner"></div></div>`;

    try {
        const res  = await fetch(`${API_BASE}/reports/get.php?id=${id}`);
        const json = await res.json();

        if (!json.success) {
            body.innerHTML = `<div class="alert-msg error"><i class="fas fa-circle-xmark"></i> Failed to load report.</div>`;
            return;
        }

        const r = json.data.report;
        const sdgs = (r.sdg_list || []).map(s => sdgBadge(s)).join(' ');
        const tags = (r.ai_tags || []).map(t => `<span style="display:inline-block;background:rgba(255,255,255,0.05);border:1px solid var(--border);border-radius:var(--radius-full);padding:2px 10px;font-size:0.75rem;color:var(--text-muted);margin:2px;">#${escHtml(t)}</span>`).join('');
        const actions = (r.ai_recommended_actions || []).map((a, i) => `<li><div class="step-icon">${i + 1}</div>${escHtml(a)}</li>`).join('');
        const imgHtml = r.image_url ? `<img src="${r.image_url}" alt="Incident photo" class="modal-img" onerror="this.style.display='none'">` : '';

        body.innerHTML = `
            ${imgHtml}
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem;">
                ${severityBadge(r.ai_severity)}
                ${statusBadge(r.status)}
                ${sdgs}
            </div>

            <h2 style="font-size:1.15rem;margin-bottom:0.5rem;">${escHtml(r.title)}</h2>
            <div style="font-size:0.78rem;color:var(--text-muted);display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
                <span><i class="fas fa-tag"></i> ${escHtml(r.ai_category)}</span>
                ${r.location_name ? `<span><i class="fas fa-location-dot"></i> ${escHtml(r.location_name)}</span>` : ''}
                <span><i class="fas fa-clock"></i> ${timeAgo(r.created_at)}</span>
                <span><i class="fas fa-user"></i> ${escHtml(r.reporter_name || 'Anonymous')}</span>
                <span><i class="fas fa-check-double"></i> ${r.confirm_count} confirmations</span>
            </div>

            <p style="color:var(--text-secondary);font-size:0.9rem;line-height:1.7;margin-bottom:1rem;">${escHtml(r.description)}</p>

            ${r.ai_summary ? `
            <div class="ai-result-card">
                <div class="ai-result-header"><i class="fas fa-robot"></i> AI Analysis Summary</div>
                <p style="font-size:0.875rem;color:var(--text-secondary);line-height:1.6;">${escHtml(r.ai_summary)}</p>
            </div>` : ''}

            ${actions ? `
            <div class="ai-result-card" style="margin-top:0.75rem;">
                <div class="ai-result-header"><i class="fas fa-list-check"></i> Recommended Actions</div>
                <ul class="ai-actions-list">${actions}</ul>
            </div>` : ''}

            ${tags ? `<div style="margin-top:1rem;">${tags}</div>` : ''}

            ${r.admin_note ? `
            <div class="ai-result-card" style="margin-top:0.75rem;border-color:rgba(16,185,129,0.4);">
                <div class="ai-result-header" style="color:var(--success);"><i class="fas fa-note-sticky"></i> Admin Note</div>
                <p style="font-size:0.875rem;color:var(--text-secondary);">${escHtml(r.admin_note)}</p>
            </div>` : ''}

            <div style="display:flex;gap:0.75rem;margin-top:1.5rem;flex-wrap:wrap;">
                <button class="btn-confirm ${isConfirmed(r.id) ? 'confirmed' : ''}" id="confirm-btn-${r.id}" onclick="confirmReport(${r.id}, this)">
                    <i class="fas fa-check-double"></i>
                    <span>${isConfirmed(r.id) ? 'Confirmed' : `Confirm (${r.confirm_count})`}</span>
                </button>
                <button class="btn btn-ghost btn-sm" onclick="flyToReport(${r.latitude}, ${r.longitude})">
                    <i class="fas fa-map-pin"></i> View on Map
                </button>
            </div>
        `;

        // Update modal title
        document.getElementById('report-modal-title').textContent = r.title;

        // Fly to on map
        if (r.latitude && r.longitude) {
            flyToReport(r.latitude, r.longitude, false);
        }
    } catch(e) {
        body.innerHTML = `<div class="alert-msg error"><i class="fas fa-circle-xmark"></i> Error loading report details.</div>`;
    }
}

function flyToReport(lat, lng, openPopup = true) {
    if (!map) return;
    map.flyTo([lat, lng], 16, { duration: 1.2 });
    if (openPopup) {
        const m = markers.find(m => m.getLatLng().lat == lat && m.getLatLng().lng == lng);
        m?.openPopup();
    }
}

// Confirm (crowd verification)
let confirmedReports = JSON.parse(localStorage.getItem('ss_confirmed') || '[]');
function isConfirmed(id) { return confirmedReports.includes(id); }

async function confirmReport(id, btn) {
    if (isConfirmed(id)) {
        Toast.show('Already Confirmed', 'You already confirmed this report.', 'info');
        return;
    }
    try {
        const res  = await fetch(`${API_BASE}/reports/confirm.php?id=${id}`, { method: 'POST' });
        const json = await res.json();
        if (json.success) {
            confirmedReports.push(id);
            localStorage.setItem('ss_confirmed', JSON.stringify(confirmedReports));
            if (btn) {
                btn.classList.add('confirmed');
                const span = btn.querySelector('span') || btn;
                span.textContent = `Confirmed (${json.data.confirm_count})`;
            }
            Toast.show('Thank You!', 'Your confirmation helps verify this incident.', 'success', 3000);
        } else {
            Toast.show('Error', json.message, 'error');
        }
    } catch(e) {
        Toast.show('Error', 'Could not confirm report.', 'error');
    }
}

window.confirmReport = confirmReport;
window.openReportModal = openReportModal;
window.flyToReport = flyToReport;

// =====================================================
// FILTERS
// =====================================================
function applyFilter(type, value) {
    activeFilters[type] = activeFilters[type] === value ? '' : value;
    loadReports();
    updateFilterUI();
}

function updateFilterUI() {
    document.querySelectorAll('.filter-chip[data-filter]').forEach(chip => {
        const type  = chip.dataset.filter;
        const value = chip.dataset.value;
        const active = activeFilters[type] === value;
        chip.classList.toggle('active', active);
    });
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// =====================================================
// MAIN INIT
// =====================================================
document.addEventListener('DOMContentLoaded', () => {
    initMap();

    // Filter chips
    document.querySelectorAll('.filter-chip[data-filter]').forEach(chip => {
        chip.addEventListener('click', () => {
            applyFilter(chip.dataset.filter, chip.dataset.value);
        });
    });

    // Search
    const searchInput = document.getElementById('map-search');
    let searchTimeout;
    searchInput?.addEventListener('input', e => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            activeFilters.search = e.target.value.trim();
            loadReports();
        }, 400);
    });

    // Modal close
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.modal-overlay').forEach(o => o.classList.remove('open'));
            document.body.style.overflow = '';
        });
    });

    // Locate me button
    document.getElementById('locate-btn')?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            Toast.show('Unavailable', 'Geolocation not supported by your browser.', 'warning');
            return;
        }
        navigator.geolocation.getCurrentPosition(pos => {
            map.flyTo([pos.coords.latitude, pos.coords.longitude], 15);
            Toast.show('Located!', 'Map centered to your location.', 'success', 2000);
        }, () => {
            Toast.show('Error', 'Could not get your location.', 'error');
        });
    });

    // Sidebar toggle
    const sidebar       = document.querySelector('.map-sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    sidebarToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('collapsed');
        map?.invalidateSize();
    });

    // Mobile sidebar
    const mobileSidebarBtn = document.getElementById('mobile-sidebar-btn');
    mobileSidebarBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('mobile-open');
    });

    // Refresh button
    document.getElementById('refresh-map-btn')?.addEventListener('click', () => {
        loadReports();
        Toast.show('Refreshed', 'Map data updated.', 'success', 2000);
    });
});
