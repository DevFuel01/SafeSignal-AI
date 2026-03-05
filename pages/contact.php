<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Contact Us';
include __DIR__ . '/../partials/header.php';
?>
<main class="page-content">
    <div class="page-header">
        <div class="container">
            <div class="page-header-title">Contact Us</div>
            <p class="page-header-sub">Have questions, suggestions, or want to partner with SafeSignal AI? We'd love to hear from you.</p>
        </div>
    </div>

    <div class="container" style="padding-bottom:5rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:start;max-width:900px;margin:0 auto;">

            <!-- Form -->
            <div>
                <div id="contact-success" class="alert-msg success mb-3" style="display:none;">
                    <i class="fas fa-circle-check"></i>
                    <span>Message sent! We'll get back to you shortly.</span>
                </div>
                <div id="contact-error" class="alert-msg error mb-3" style="display:none;">
                    <i class="fas fa-circle-xmark"></i><span></span>
                </div>

                <form id="contact-form" novalidate>
                    <div class="form-group">
                        <label class="form-label" for="contact-name">Your Name</label>
                        <input type="text" id="contact-name" name="name" class="form-control" placeholder="Full name" required minlength="2">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact-email">Email Address</label>
                        <input type="email" id="contact-email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact-subject">Subject</label>
                        <input type="text" id="contact-subject" name="subject" class="form-control" placeholder="What's this about?">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact-message">Message</label>
                        <textarea id="contact-message" name="message" class="form-control" rows="6" placeholder="Tell us more..." required minlength="10"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="contact-submit-btn">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>

            <!-- Info -->
            <div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h3 style="font-size:1.05rem;margin-bottom:1.25rem;">Get In Touch</h3>
                        <div style="display:flex;flex-direction:column;gap:1rem;">
                            <?php foreach (
                                [
                                    ['fa-envelope', 'Email', 'contact@safesignal.ai'],
                                    ['fa-phone', 'Emergency Hotline', '112'],
                                    ['fa-map-location-dot', 'Location', 'Lagos, Nigeria (Demo HQ)'],
                                ] as $c
                            ): ?>
                                <div style="display:flex;align-items:center;gap:0.85rem;">
                                    <div style="width:40px;height:40px;border-radius:var(--radius-sm);background:var(--primary-glow);border:1px solid var(--border-glow);display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0;">
                                        <i class="fas <?= $c[0] ?>"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;"><?= $c[1] ?></div>
                                        <div style="font-size:0.875rem;color:var(--text-primary);font-weight:500;"><?= $c[2] ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-color:rgba(0,212,255,0.25);">
                    <div class="card-body">
                        <h3 style="font-size:0.95rem;margin-bottom:0.75rem;"><i class="fas fa-code" style="color:var(--primary);"></i> Tech Stack</h3>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                            <?php foreach (['PHP', 'MySQL', 'Gemini AI', 'Leaflet.js', 'Vanilla JS', 'HTML/CSS'] as $tech): ?>
                                <span style="background:var(--bg-glass);border:1px solid var(--border);border-radius:var(--radius-full);padding:0.25rem 0.75rem;font-size:0.78rem;color:var(--text-secondary);"><?= $tech ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$extraScript = '<script>
"use strict";
document.getElementById("contact-form")?.addEventListener("submit", async function(e) {
    e.preventDefault();
    const successEl = document.getElementById("contact-success");
    const errorEl   = document.getElementById("contact-error");
    successEl.style.display = "none";
    errorEl.style.display   = "none";

    const btn = document.getElementById("contact-submit-btn");
    btn.disabled = true;
    btn.innerHTML = \'<span class="spinner spinner-sm"></span> Sending...\';

    const data = {
        name:    this.name.value.trim(),
        email:   this.email.value.trim(),
        subject: this.subject?.value?.trim() || "",
        message: this.message.value.trim(),
    };

    try {
        const res  = await fetch("/SafeSignal/api/contact/submit.php", { method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify(data) });
        const json = await res.json();
        if (json.success) {
            successEl.style.display = "flex";
            this.reset();
        } else {
            errorEl.querySelector("span").textContent = json.message;
            errorEl.style.display = "flex";
        }
    } catch(err) {
        errorEl.querySelector("span").textContent = "Network error. Please try again.";
        errorEl.style.display = "flex";
    }
    btn.disabled = false;
    btn.innerHTML = \'<i class="fas fa-paper-plane"></i> Send Message\';
});
</script>';
include __DIR__ . '/../partials/footer.php'; ?>