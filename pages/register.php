<?php
require_once __DIR__ . '/../config/config.php';
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/pages/dashboard.php');
    exit;
}
$pageTitle = 'Create Account';
include __DIR__ . '/../partials/header.php';
?>

<main class="page-content">
    <div class="flex-center" style="min-height:calc(100vh - 57px);padding:2rem;">
        <div style="width:100%;max-width:440px;">
            <div style="text-align:center;margin-bottom:2rem;">
                <a href="<?= APP_URL ?>" class="nav-logo" style="display:inline-flex;margin-bottom:1.25rem;">
                    <div class="logo-icon"><i class="fas fa-shield-halved"></i></div>
                    <div class="logo-text"><span class="logo-name">SafeSignal</span><span class="logo-ai">AI</span></div>
                </a>
                <h1 style="font-size:1.75rem;margin-bottom:0.5rem;">Join SafeSignal</h1>
                <p style="color:var(--text-muted);font-size:0.9rem;">Create your account to start making your community safer.</p>
            </div>

            <div class="card">
                <div class="card-body" style="padding:2rem;">
                    <div id="reg-error" class="alert-msg error mb-3" style="display:none;">
                        <i class="fas fa-circle-xmark"></i><span></span>
                    </div>

                    <form id="register-form" novalidate>
                        <div class="form-group">
                            <label class="form-label" for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Your full name" autocomplete="name" required minlength="2">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" autocomplete="email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <div style="position:relative;">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Min 8 chars, uppercase, number" autocomplete="new-password" required minlength="8">
                                <button type="button" id="toggle-pw" style="position:absolute;right:0.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);" aria-label="Toggle password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <!-- Password strength meter -->
                            <div style="margin-top:0.5rem;height:4px;background:var(--border);border-radius:2px;overflow:hidden;">
                                <div id="pw-strength-bar" style="height:100%;width:0;transition:all 0.3s ease;border-radius:2px;"></div>
                            </div>
                            <span id="pw-strength-label" class="form-help"></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password2">Confirm Password</label>
                            <div style="position:relative;">
                                <input type="password" id="password2" name="password2" class="form-control" placeholder="Re-enter password" autocomplete="new-password" required>
                                <button type="button" id="toggle-pw2" style="position:absolute;right:0.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);" aria-label="Toggle password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg" id="register-btn" style="margin-top:0.5rem;">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>

                    <p style="text-align:center;margin-top:1.25rem;font-size:0.875rem;color:var(--text-muted);">
                        Already have an account? <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--primary);font-weight:600;">Log in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$extraScript = '<script>
"use strict";
document.getElementById("toggle-pw")?.addEventListener("click", function() {
    const pw = document.getElementById("password");
    const icon = this.querySelector("i");
    if (pw.type === "password") { pw.type="text"; icon.classList.replace("fa-eye","fa-eye-slash"); }
    else { pw.type="password"; icon.classList.replace("fa-eye-slash","fa-eye"); }
});

document.getElementById("toggle-pw2")?.addEventListener("click", function() {
    const pw = document.getElementById("password2");
    const icon = this.querySelector("i");
    if (pw.type === "password") { pw.type="text"; icon.classList.replace("fa-eye","fa-eye-slash"); }
    else { pw.type="password"; icon.classList.replace("fa-eye-slash","fa-eye"); }
});

document.getElementById("password")?.addEventListener("input", function() {
    const val = this.value;
    const bar = document.getElementById("pw-strength-bar");
    const label = document.getElementById("pw-strength-label");
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ["#ef4444","#f59e0b","#10b981","#00d4ff"];
    const labels = ["Weak","Fair","Good","Strong"];
    bar.style.width = (score*25) + "%";
    bar.style.background = colors[score-1] || "#ef4444";
    label.textContent = score > 0 ? labels[score-1] : "";
    label.style.color = colors[score-1] || "var(--text-muted)";
});

document.getElementById("register-form")?.addEventListener("submit", async function(e) {
    e.preventDefault();
    const errEl = document.getElementById("reg-error");
    errEl.style.display = "none";

    const password  = document.getElementById("password").value;
    const password2 = document.getElementById("password2").value;

    if (password !== password2) {
        errEl.querySelector("span").textContent = "Passwords do not match.";
        errEl.style.display = "flex"; return;
    }

    const btn = document.getElementById("register-btn");
    btn.disabled = true;
    btn.innerHTML = \'<span class="spinner spinner-sm"></span> Creating Account...\';

    const data = { name: this.name.value.trim(), email: this.email.value.trim(), password };
    try {
        const res  = await fetch("/SafeSignal/api/auth/register.php", { method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify(data) });
        const json = await res.json();
        if (json.success) {
            window.location.href = "/SafeSignal/pages/dashboard.php";
        } else {
            errEl.querySelector("span").textContent = json.message;
            errEl.style.display = "flex";
            btn.disabled = false;
            btn.innerHTML = \'<i class="fas fa-user-plus"></i> Create Account\';
        }
    } catch(err) {
        errEl.querySelector("span").textContent = "Network error.";
        errEl.style.display = "flex";
        btn.disabled = false;
        btn.innerHTML = \'<i class="fas fa-user-plus"></i> Create Account\';
    }
});
</script>';
include __DIR__ . '/../partials/footer.php';
?>