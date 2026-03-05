/**
 * SafeSignal AI — Main JavaScript
 * Global utilities: navbar, toasts, alerts polling, animations
 */

'use strict';

// =====================================================
// GLOBAL CONST
// =====================================================
const APP_URL = (() => {
    // Detect base path by checking where /assets/ is located
    const scripts = document.getElementsByTagName('script');
    for (let s of scripts) {
        if (s.src.includes('/assets/js/main.js')) {
            return s.src.split('/assets/js/main.js')[0];
        }
    }
    // Fallback to origin if script detection fails
    return window.location.origin;
})();

const API_BASE = APP_URL + '/api';

// =====================================================
// TOAST NOTIFICATION SYSTEM
// =====================================================
const Toast = (() => {
    const container = document.getElementById('toast-container');
    const icons = {
        success: 'fa-circle-check',
        error:   'fa-circle-xmark',
        warning: 'fa-triangle-exclamation',
        info:    'fa-circle-info',
        critical:'fa-skull-crossbones',
    };

    function show(title, message = '', type = 'info', duration = 5000) {
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info} toast-icon"></i>
            <div class="toast-body">
                <div class="toast-title">${title}</div>
                ${message ? `<div class="toast-message">${message}</div>` : ''}
            </div>
            <button class="toast-close" aria-label="Close"><i class="fas fa-times"></i></button>
        `;

        const close = toast.querySelector('.toast-close');
        close.addEventListener('click', () => dismiss(toast));

        container.appendChild(toast);

        if (duration > 0) {
            setTimeout(() => dismiss(toast), duration);
        }
        return toast;
    }

    function dismiss(toast) {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }

    return { show, dismiss };
})();

window.Toast = Toast;

// =====================================================
// NAVBAR
// =====================================================
(function initNavbar() {
    const navbar   = document.getElementById('navbar');
    const toggle   = document.getElementById('nav-toggle');
    const menu     = document.getElementById('nav-menu');
    const logoutBtn= document.getElementById('logout-btn');

    // Scroll effect
    window.addEventListener('scroll', () => {
        navbar?.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    // Hamburger toggle
    toggle?.addEventListener('click', () => {
        const open = menu.classList.toggle('open');
        toggle.classList.toggle('active', open);
        toggle.setAttribute('aria-expanded', String(open));
    });

    // Close menu when clicking outside
    document.addEventListener('click', e => {
        if (menu?.classList.contains('open') && !navbar?.contains(e.target)) {
            menu.classList.remove('open');
            toggle?.classList.remove('active');
        }
    });

    // Logout
    logoutBtn?.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            await fetch(`${API_BASE}/auth/logout.php`, { method: 'POST' });
        } catch(err) {}
        window.location.href = '/SafeSignal/';
    });

    // Dropdown accessibility
    document.querySelectorAll('.nav-dropdown').forEach(dd => {
        const btn = dd.querySelector('.nav-user-btn');
        btn?.addEventListener('click', () => {
            const open = dd.classList.toggle('open');
            btn.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', e => {
            if (!dd.contains(e.target)) {
                dd.classList.remove('open');
                btn?.setAttribute('aria-expanded', 'false');
            }
        });
    });
})();

// =====================================================
// ALERTS POLLING
// =====================================================
(function initAlertsPolling() {
    const banner = document.getElementById('alert-banner');
    let lastChecked = null;
    let shown = new Set();

    async function pollAlerts() {
        try {
            const isInitial = (lastChecked === null);
            const params = lastChecked ? `?since=${encodeURIComponent(lastChecked)}` : '';
            const res = await fetch(`${API_BASE}/alerts/latest.php${params}`);
            const json = await res.json();
            
            if (!json.success) return;

            const alerts = json.data?.alerts || [];
            if (!alerts.length) {
                lastChecked = new Date().toISOString();
                return;
            }

            alerts.forEach((alert, index) => {
                if (shown.has(alert.id)) return;
                shown.add(alert.id);

                // On first load, only show the absolute newest one as a toast
                // All current 24h history is marked as 'shown' but won't pop up
                if (isInitial && index > 0) return;

                const sev = (alert.severity || '').toLowerCase();
                const type = sev === 'critical' ? 'critical' : sev === 'high' ? 'error' : sev === 'medium' ? 'warning' : 'info';
                Toast.show(`🚨 ${alert.severity} Alert`, alert.message, type, 8000);

                if (sev === 'critical' && banner) {
                    banner.innerHTML = `<i class="fas fa-triangle-exclamation"></i> ${alert.message} <a href="/SafeSignal/pages/map.php" style="color:inherit;margin-left:0.5rem;text-decoration:underline">View on Map →</a>`;
                    banner.style.display = 'flex';
                    setTimeout(() => { banner.style.display = 'none'; }, 10000);
                }
            });

            lastChecked = new Date().toISOString();
        } catch(e) { /* silent fail */ }
    }

    // Poll every 8 seconds
    pollAlerts();
    setInterval(pollAlerts, 8000);
})();

// =====================================================
// SCROLL ANIMATIONS
// =====================================================
(function initScrollAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    if (!elements.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(el => observer.observe(el));
})();

// =====================================================
// ANIMATED COUNTERS
// =====================================================
function animateCounter(el, target, duration = 1800) {
    const start = performance.now();
    const startVal = 0;

    function update(timestamp) {
        const elapsed = timestamp - start;
        const progress = Math.min(elapsed / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        const current = Math.round(startVal + (target - startVal) * ease);
        el.textContent = current.toLocaleString();
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

(function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.counter, 10);
                animateCounter(entry.target, target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(el => observer.observe(el));
})();

// =====================================================
// MODAL SYSTEM
// =====================================================
const Modal = (() => {
    function open(id) {
        const overlay = document.getElementById(id + '-overlay') || document.getElementById(id);
        overlay?.classList.add('open');
        document.body.style.overflow = 'hidden';
        overlay?.querySelector('.modal-close')?.focus();
    }
    function close(id) {
        const overlay = document.getElementById(id + '-overlay') || document.getElementById(id);
        overlay?.classList.remove('open');
        document.body.style.overflow = '';
    }
    function closeAll() {
        document.querySelectorAll('.modal-overlay.open').forEach(o => {
            o.classList.remove('open');
        });
        document.body.style.overflow = '';
    }

    // Close on overlay click
    document.addEventListener('click', e => {
        if (e.target.classList.contains('modal-overlay')) closeAll();
        if (e.target.closest('.modal-close')) closeAll();
    });
    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeAll();
    });

    return { open, close, closeAll };
})();

window.Modal = Modal;

// =====================================================
// FORM UTILITIES
// =====================================================
const Form = (() => {
    function setLoading(formEl, loading, btnText = 'Submit') {
        const btn = formEl.querySelector('[type="submit"]');
        if (!btn) return;
        btn.disabled = loading;
        const spinner = loading ? '<span class="spinner spinner-sm"></span>' : '';
        btn.innerHTML = loading ? `${spinner} Processing...` : `<i class="fas fa-paper-plane"></i> ${btnText}`;
    }

    function showError(formEl, message) {
        let errEl = formEl.querySelector('.form-submit-error');
        if (!errEl) {
            errEl = document.createElement('div');
            errEl.className = 'alert-msg error form-submit-error';
            errEl.innerHTML = `<i class="fas fa-circle-xmark"></i><span></span>`;
            formEl.prepend(errEl);
        }
        errEl.querySelector('span').textContent = message;
        errEl.style.display = 'flex';
        errEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideError(formEl) {
        formEl.querySelector('.form-submit-error')?.remove();
    }

    async function submit(url, data, options = {}) {
        const res = await fetch(url, {
            method: options.method || 'POST',
            body: data instanceof FormData ? data : JSON.stringify(data),
            headers: data instanceof FormData ? {} : { 'Content-Type': 'application/json' },
        });
        const json = await res.json().catch(() => ({ success: false, message: 'Invalid server response.' }));
        return json;
    }

    return { setLoading, showError, hideError, submit };
})();

window.Form = Form;

// =====================================================
// SEVERITY / STATUS BADGE HELPER
// =====================================================
function severityBadge(sev) {
    const s = (sev || '').toLowerCase();
    return `<span class="severity-badge severity-${s}"><i class="fas fa-circle"></i>${sev}</span>`;
}
function statusBadge(status) {
    const icons = { pending: 'fa-clock', verified: 'fa-circle-check', resolved: 'fa-check-double' };
    return `<span class="status-badge status-${status}"><i class="fas ${icons[status] || 'fa-circle'}"></i>${status}</span>`;
}
function sdgBadge(sdg) {
    const num = sdg.replace('SDG', '').trim();
    const icons = { '11': 'fa-city', '16': 'fa-balance-scale', '3': 'fa-heartbeat', '9': 'fa-network-wired', '5': 'fa-venus', '6': 'fa-tint', '13': 'fa-leaf' };
    return `<span class="sdg-badge sdg-${num}" title="${sdg}"><i class="fas ${icons[num] || 'fa-tag'}"></i>${sdg}</span>`;
}
function timeAgo(dateStr) {
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1)  return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24)  return `${hrs}h ago`;
    const days = Math.floor(hrs / 24);
    if (days < 30) return `${days}d ago`;
    return new Date(dateStr).toLocaleDateString();
}
function categoryIcon(cat) {
    const map = {
        'Crime': 'fa-user-secret', 'Flood': 'fa-water', 'Fire': 'fa-fire',
        'Harassment': 'fa-hand-fist', 'Accident': 'fa-car-burst',
        'Infrastructure Damage': 'fa-road-barrier', 'Pollution': 'fa-smog',
        'Medical Emergency': 'fa-kit-medical', 'General Safety': 'fa-shield-halved',
    };
    return map[cat] || 'fa-circle-exclamation';
}

window.severityBadge = severityBadge;
window.statusBadge   = statusBadge;
window.sdgBadge      = sdgBadge;
window.timeAgo       = timeAgo;
window.categoryIcon  = categoryIcon;
window.API_BASE      = API_BASE;
