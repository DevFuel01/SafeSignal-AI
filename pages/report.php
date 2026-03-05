<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Report an Incident';
$preCategory = htmlspecialchars($_GET['category'] ?? '', ENT_QUOTES, 'UTF-8');
include __DIR__ . '/../partials/header.php';
?>
<main class="page-content">
    <div class="page-header">
        <div class="container">
            <div class="page-header-title"><i class="fas fa-circle-exclamation" style="color:var(--danger);"></i> Report an Incident</div>
            <p class="page-header-sub">Submit a community safety report. Our AI will automatically classify and analyze it within seconds.</p>
        </div>
    </div>

    <div class="container" style="padding-bottom:4rem;">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start;">

            <!-- FORM -->
            <div>
                <div id="report-success" style="display:none;"></div>

                <form id="report-form" enctype="multipart/form-data" novalidate>
                    <!-- Step 1: Basic Info -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-glow);border:1px solid var(--border-glow);display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;">1</div>
                                <span style="font-weight:700;">Incident Details</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label" for="report-title">Incident Title *</label>
                                <input type="text" id="report-title" name="title" class="form-control" placeholder="Short, descriptive title (e.g. 'Flooded road blocking traffic')" required minlength="5" maxlength="255">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="report-description">
                                    Description * <span style="font-weight:400;color:var(--text-muted);">(<span id="desc-counter">0</span>/2000)</span>
                                </label>
                                <textarea id="report-description" name="description" class="form-control" rows="5" maxlength="2000" placeholder="Describe what happened in detail: what, when, who, any injuries, danger level..." required minlength="20"></textarea>
                            </div>
                            <div class="form-row cols-2">
                                <div class="form-group mb-0">
                                    <label class="form-label" for="category">Category <span style="color:var(--text-muted);font-weight:400;">(or let AI decide)</span></label>
                                    <select id="category" name="category" class="form-control">
                                        <option value="">🤖 Let AI classify</option>
                                        <option value="Crime" <?= $preCategory === 'Crime' ? 'selected' : '' ?>>🔫 Crime</option>
                                        <option value="Flood" <?= $preCategory === 'Flood' ? 'selected' : '' ?>>🌊 Flood</option>
                                        <option value="Fire" <?= $preCategory === 'Fire' ? 'selected' : '' ?>>🔥 Fire</option>
                                        <option value="Harassment" <?= $preCategory === 'Harassment' ? 'selected' : '' ?>>⚠️ Harassment</option>
                                        <option value="Accident" <?= $preCategory === 'Accident' ? 'selected' : '' ?>>💥 Accident</option>
                                        <option value="Infrastructure Damage" <?= $preCategory === 'Infrastructure Damage' ? 'selected' : '' ?>>🏗️ Infrastructure</option>
                                        <option value="Pollution" <?= $preCategory === 'Pollution' ? 'selected' : '' ?>>☁️ Pollution</option>
                                        <option value="Medical Emergency" <?= $preCategory === 'Medical Emergency' ? 'selected' : '' ?>>🚑 Medical Emergency</option>
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label" for="severity">Severity <span style="color:var(--text-muted);font-weight:400;">(or let AI decide)</span></label>
                                    <select id="severity" name="severity" class="form-control">
                                        <option value="">🤖 Let AI assess</option>
                                        <option value="Low">🟢 Low</option>
                                        <option value="Medium">🟡 Medium</option>
                                        <option value="High">🟠 High</option>
                                        <option value="Critical">🔴 Critical</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Location -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-glow);border:1px solid var(--border-glow);display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;">2</div>
                                <span style="font-weight:700;">Incident Location</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label" for="location_name">Location Name (optional)</label>
                                <input type="text" id="location_name" name="location_name" class="form-control" placeholder="e.g. 'Marina Road, Lagos Island'">
                            </div>
                            <div style="display:flex;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap;">
                                <button type="button" id="auto-locate-btn" class="btn btn-ghost btn-sm">
                                    <i class="fas fa-crosshairs"></i> Use My Location
                                </button>
                                <span style="font-size:0.8rem;color:var(--text-muted);align-self:center;">or click on the map to pin location</span>
                            </div>
                            <div id="location-map" style="height:300px;border-radius:var(--radius);border:1px solid var(--border);overflow:hidden;"></div>
                            <div id="location-picked" style="display:none;margin-top:0.75rem;" class="alert-msg info">
                                <i class="fas fa-location-dot"></i>
                                <span>Location pinned: <strong id="coords-display"></strong></span>
                            </div>
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                        </div>
                    </div>

                    <!-- Step 3: Photo -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-glow);border:1px solid var(--border-glow);display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;">3</div>
                                <span style="font-weight:700;">Photo Evidence <span style="color:var(--text-muted);font-weight:400;">(optional)</span></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <label for="report-image" style="display:block;border:2px dashed var(--border);border-radius:var(--radius);padding:2rem;text-align:center;cursor:pointer;transition:var(--transition);" id="upload-label">
                                <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--text-muted);display:block;margin-bottom:0.75rem;"></i>
                                <span style="color:var(--text-secondary);font-size:0.875rem;">Click to upload or drag photo here</span><br>
                                <span style="font-size:0.75rem;color:var(--text-muted);">JPG, PNG, WebP — max 5MB</span>
                            </label>
                            <input type="file" id="report-image" name="image" accept="image/*" style="display:none;">
                            <div id="image-preview" style="display:none;margin-top:0.75rem;position:relative;">
                                <img id="preview-img" src="" alt="Preview" style="width:100%;max-height:200px;object-fit:cover;border-radius:var(--radius);">
                                <button type="button" id="remove-image-btn" style="position:absolute;top:0.5rem;right:0.5rem;background:rgba(0,0,0,0.7);color:white;border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.8rem;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div id="ai-analyzing-status" class="ai-analyzing" style="display:none;margin-bottom:1rem;">
                        <div class="spinner spinner-sm"></div>
                        <span>🤖 Gemini AI is analyzing your report... Please wait.</span>
                    </div>

                    <?php if (!isLoggedIn()): ?>
                        <div class="alert-msg info mb-3">
                            <i class="fas fa-circle-info"></i>
                            <span>You need to <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--primary);font-weight:600;">log in</a> to submit a report.</span>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-danger btn-xl w-100" id="submit-report-btn" <?= !isLoggedIn() ? 'disabled' : '' ?>>
                        <i class="fas fa-paper-plane"></i> Submit Report & Run AI Analysis
                    </button>
                    <p style="text-align:center;font-size:0.78rem;color:var(--text-muted);margin-top:0.75rem;">
                        <i class="fas fa-lock"></i> Your identity is protected. Reports are used solely for community safety.
                    </p>
                </form>
            </div>

            <!-- SIDEBAR INFO -->
            <div style="position:sticky;top:5rem;">
                <div class="card mb-3" style="border-color:rgba(0,212,255,0.25);">
                    <div class="card-body">
                        <h3 style="font-size:0.95rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                            <i class="fas fa-robot" style="color:var(--primary);"></i> How AI Helps
                        </h3>
                        <ul style="display:flex;flex-direction:column;gap:0.75rem;">
                            <?php foreach (
                                [
                                    ['fa-tag', 'Auto-classifies category & severity'],
                                    ['fa-file-lines', 'Writes a professional summary'],
                                    ['fa-list-check', 'Recommends action steps'],
                                    ['fa-tag', 'Maps to relevant SDGs'],
                                    ['fa-bell', 'Triggers alerts for high severity'],
                                ] as $item
                            ): ?>
                                <li style="display:flex;align-items:center;gap:0.6rem;font-size:0.85rem;color:var(--text-secondary);">
                                    <i class="fas <?= $item[0] ?>" style="color:var(--primary);width:16px;text-align:center;"></i> <?= $item[1] ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="card mb-3" style="border-color:rgba(239,68,68,0.25);">
                    <div class="card-body">
                        <h3 style="font-size:0.95rem;margin-bottom:0.75rem;color:var(--danger);">
                            <i class="fas fa-triangle-exclamation"></i> Emergency?
                        </h3>
                        <p style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:0.75rem;">If there's an immediate threat to life, call emergency services FIRST.</p>
                        <a href="tel:112" class="btn btn-danger btn-sm w-100"><i class="fas fa-phone-alt"></i> Call 112 — Emergency</a>
                        <a href="tel:199" class="btn btn-ghost btn-sm w-100 mt-1"><i class="fas fa-fire"></i> Call 199 — Fire/Police</a>
                    </div>
                </div>

                <div class="card" style="border-color:rgba(124,58,237,0.25);">
                    <div class="card-body">
                        <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                            <span class="sdg-badge sdg-11"><i class="fas fa-city"></i> SDG 11</span>
                            <span class="sdg-badge sdg-16"><i class="fas fa-balance-scale"></i> SDG 16</span>
                        </div>
                        <p style="font-size:0.82rem;color:var(--text-muted);">Every report contributes to safer, more sustainable communities worldwide.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$extraScript = '<script src="' . APP_URL . '/assets/js/report.js"></script>
<script>
document.getElementById("upload-label")?.addEventListener("dragover", function(e) { e.preventDefault(); this.style.borderColor = "var(--primary)"; });
document.getElementById("upload-label")?.addEventListener("dragleave", function(e) { this.style.borderColor = "var(--border)"; });
document.getElementById("upload-label")?.addEventListener("drop", function(e) {
    e.preventDefault(); this.style.borderColor = "var(--border)";
    const dt = e.dataTransfer;
    if (dt.files[0]) { document.getElementById("report-image").files = dt.files; document.getElementById("report-image").dispatchEvent(new Event("change")); }
});
</script>';
include __DIR__ . '/../partials/footer.php';
?>