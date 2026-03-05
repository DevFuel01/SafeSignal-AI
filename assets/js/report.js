/**
 * SafeSignal AI — Report Submission JavaScript
 * Map pin selection, form validation, AI classification result display
 */

'use strict';

let reportMap, reportMarker;
let selectedLat = null, selectedLng = null;

// =====================================================
// INIT LOCATION PICKER MAP
// =====================================================
function initReportMap() {
    reportMap = L.map('location-map', {
        center: [6.4981, 3.3479],
        zoom: 12,
        zoomControl: true,
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 19,
    }).addTo(reportMap);

    reportMap.on('click', e => {
        const { lat, lng } = e.latlng;
        setLocationPin(lat, lng);
    });
}

function setLocationPin(lat, lng) {
    selectedLat = lat;
    selectedLng = lng;

    if (reportMarker) {
        reportMarker.setLatLng([lat, lng]);
    } else {
        const icon = L.divIcon({
            html: `<div style="width:36px;height:36px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:var(--primary);border:2px solid white;box-shadow:0 0 20px rgba(0,212,255,0.5);display:flex;align-items:center;justify-content:center;"><i class="fas fa-location-dot" style="transform:rotate(45deg);color:white;font-size:14px;"></i></div>`,
            className: '',
            iconSize: [36, 36],
            iconAnchor: [18, 36],
        });
        reportMarker = L.marker([lat, lng], { icon, draggable: true }).addTo(reportMap);
        reportMarker.on('dragend', e => {
            const pos = e.target.getLatLng();
            updateCoords(pos.lat, pos.lng);
        });
    }

    updateCoords(lat, lng);
    document.getElementById('location-picked').style.display = 'flex';
}

function updateCoords(lat, lng) {
    selectedLat = lat;
    selectedLng = lng;
    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');
    const displayEl = document.getElementById('coords-display');
    if (latEl) latEl.value = lat.toFixed(6);
    if (lngEl) lngEl.value = lng.toFixed(6);
    if (displayEl) displayEl.textContent = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
}

// Auto-detect location
document.getElementById('auto-locate-btn')?.addEventListener('click', () => {
    if (!navigator.geolocation) {
        Toast.show('Unavailable', 'Geolocation not supported.', 'warning');
        return;
    }
    const btn = document.getElementById('auto-locate-btn');
    btn.innerHTML = '<span class="spinner spinner-sm"></span> Locating...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(pos => {
        const { latitude: lat, longitude: lng } = pos.coords;
        setLocationPin(lat, lng);
        reportMap.flyTo([lat, lng], 15);
        Toast.show('Location Found!', 'Your location has been added to the pin.', 'success', 2500);
        btn.innerHTML = '<i class="fas fa-crosshairs"></i> Use My Location';
        btn.disabled = false;
    }, () => {
        Toast.show('Error', 'Could not access your location.', 'error');
        btn.innerHTML = '<i class="fas fa-crosshairs"></i> Use My Location';
        btn.disabled = false;
    });
});

// =====================================================
// IMAGE PREVIEW
// =====================================================
document.getElementById('report-image')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        Toast.show('File Too Large', 'Maximum image size is 5MB.', 'error');
        this.value = '';
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        Toast.show('Invalid Format', 'Please upload a JPG, PNG, GIF, or WebP image.', 'error');
        this.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        if (preview && previewImg) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
    };
    reader.readAsDataURL(file);
});

document.getElementById('remove-image-btn')?.addEventListener('click', () => {
    document.getElementById('report-image').value = '';
    document.getElementById('image-preview').style.display = 'none';
});

// =====================================================
// REPORT FORM SUBMISSION
// =====================================================
document.getElementById('report-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    // Validate location
    if (!selectedLat || !selectedLng) {
        Toast.show('Location Required', 'Please select a location on the map or use your current location.', 'error');
        document.getElementById('location-map')?.scrollIntoView({ behavior: 'smooth' });
        return;
    }

    const formData = new FormData(this);
    formData.set('latitude',  selectedLat);
    formData.set('longitude', selectedLng);

    Form.setLoading(this, true, 'Submit Report');
    Form.hideError(this);

    // Show AI analyzing state
    const aiStatus = document.getElementById('ai-analyzing-status');
    if (aiStatus) aiStatus.style.display = 'flex';

    try {
        const res  = await fetch(`${API_BASE}/reports/create.php`, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            // Show success result
            showSuccessState(json.data);
        } else {
            Form.showError(this, json.message || 'Failed to submit report.');
            Form.setLoading(this, false, 'Submit Report');
            if (aiStatus) aiStatus.style.display = 'none';
        }
    } catch(err) {
        Form.showError(this, 'Network error. Please check your connection and try again.');
        Form.setLoading(this, false, 'Submit Report');
        if (aiStatus) aiStatus.style.display = 'none';
    }
});

function showSuccessState(data) {
    const form = document.getElementById('report-form');
    const success = document.getElementById('report-success');

    if (form) form.style.display = 'none';
    if (!success) return;

    success.innerHTML = `
        <div style="text-align:center;padding:3rem 1.5rem;">
            <div style="width:80px;height:80px;border-radius:50%;background:rgba(16,185,129,0.15);border:2px solid var(--success);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2.5rem;color:var(--success);">
                <i class="fas fa-circle-check"></i>
            </div>
            <h2 style="font-size:1.5rem;margin-bottom:0.75rem;">Report Submitted!</h2>
            <p style="color:var(--text-secondary);margin-bottom:2rem;max-width:400px;margin-inline:auto;">Your report has been analyzed by our AI and added to the live map. The community and authorities can now see it.</p>
            
            <div style="background:linear-gradient(135deg,rgba(0,212,255,0.05),rgba(124,58,237,0.05));border:1px solid var(--border-glow);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:2rem;text-align:left;max-width:440px;margin-inline:auto;">
                <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--primary);display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;">
                    <i class="fas fa-robot"></i> AI Classification
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:0.75rem;">
                    <div>
                        <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:0.25rem;">Category</div>
                        <div style="font-weight:600;">${data.ai_category || 'General Safety'}</div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:0.25rem;">Severity</div>
                        ${severityBadge(data.ai_severity || 'Medium')}
                    </div>
                </div>
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:0.25rem;">SDG Alignment</div>
                    <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                        ${(data.sdg_tags || 'SDG11,SDG16').split(',').map(s => sdgBadge(s.trim())).join('')}
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                <a href="/SafeSignal/pages/map.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-map-marked-alt"></i> View on Live Map
                </a>
                <a href="/SafeSignal/pages/report.php" class="btn btn-outline">
                    <i class="fas fa-plus"></i> Submit Another
                </a>
            </div>
        </div>
    `;
    success.style.display = 'block';
    success.scrollIntoView({ behavior: 'smooth' });

    // Auto-redirect after 5 seconds
    setTimeout(() => {
        window.location.href = '/SafeSignal/pages/map.php';
    }, 8000);
}

// =====================================================
// INIT
// =====================================================
document.addEventListener('DOMContentLoaded', () => {
    initReportMap();

    // Character counter for description
    const desc = document.getElementById('report-description');
    const counter = document.getElementById('desc-counter');
    desc?.addEventListener('input', () => {
        if (counter) counter.textContent = desc.value.length;
    });
});
