<?php
require_once __DIR__ . '/../config/config.php';
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/pages/dashboard.php');
    exit;
}
$pageTitle = 'Login';
include __DIR__ . '/../partials/header.php';
?>

<main class="page-content">
    <div class="flex-center" style="min-height:calc(100vh - 57px);padding:2rem;">
        <div style="width:100%;max-width:420px;">
            <!-- Logo + Title -->
            <div style="text-align:center;margin-bottom:2rem;">
                <a href="<?= APP_URL ?>" class="nav-logo" style="display:inline-flex;margin-bottom:1.25rem;">
                    <div class="logo-icon"><i class="fas fa-shield-halved"></i></div>
                    <div class="logo-text"><span class="logo-name">SafeSignal</span><span class="logo-ai">AI</span></div>
                </a>
                <h1 style="font-size:1.75rem;margin-bottom:0.5rem;">Welcome back</h1>
                <p style="color:var(--text-muted);font-size:0.9rem;">Log in to report incidents and protect your community.</p>
            </div>

            <div class="card">
                <div class="card-body" style="padding:2rem;">
                    <div id="login-error" class="alert-msg error mb-3" style="display:none;">
                        <i class="fas fa-circle-xmark"></i><span></span>
                    </div>

                    <form id="login-form" novalidate>
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" autocomplete="email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <div style="position:relative;">
                                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password" required>
                                <button type="button" id="toggle-pw" style="position:absolute;right:0.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:0.9rem;" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye" id="toggle-pw-icon"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg" id="login-btn" style="margin-top:0.5rem;">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>

                    <div class="divider" style="margin:1.25rem 0;"></div>

                    <div style="padding:1rem;background:rgba(0,212,255,0.05);border:1px solid var(--border-glow);border-radius:var(--radius);font-size:0.8rem;color:var(--text-secondary);">
                        <strong style="color:var(--primary);">Demo Credentials:</strong><br>
                        Admin: admin@safesignal.ai / Admin@123<br>
                        User: john@example.com / Admin@123
                    </div>

                    <p style="text-align:center;margin-top:1.25rem;font-size:0.875rem;color:var(--text-muted);">
                        Don't have an account? <a href="<?= APP_URL ?>/pages/register.php" style="color:var(--primary);font-weight:600;">Sign up free</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$extraScript = '<script>
"use strict";
const loginForm = document.getElementById("login-form");
const loginErr  = document.getElementById("login-error");

// Toggle password
document.getElementById("toggle-pw")?.addEventListener("click", () => {
    const pw = document.getElementById("password");
    const icon = document.getElementById("toggle-pw-icon");
    if (pw.type === "password") { pw.type = "text"; icon.classList.replace("fa-eye","fa-eye-slash"); }
    else { pw.type = "password"; icon.classList.replace("fa-eye-slash","fa-eye"); }
});

loginForm?.addEventListener("submit", async function(e) {
    e.preventDefault();
    loginErr.style.display = "none";
    const btn = document.getElementById("login-btn");
    btn.disabled = true;
    btn.innerHTML = \'<span class="spinner spinner-sm"></span> Logging in...\';

    const data = { email: this.email.value.trim(), password: this.password.value };
    try {
        const res  = await fetch("/SafeSignal/api/auth/login.php", { method:"POST", headers:{"Content-Type":"application/json"}, body: JSON.stringify(data) });
        const json = await res.json();
        if (json.success) {
            window.location.href = json.data.user.role === "admin" ? "/SafeSignal/pages/admin.php" : "/SafeSignal/pages/dashboard.php";
        } else {
            loginErr.querySelector("span").textContent = json.message;
            loginErr.style.display = "flex";
            btn.disabled = false;
            btn.innerHTML = \'<i class="fas fa-sign-in-alt"></i> Login\';
        }
    } catch(err) {
        loginErr.querySelector("span").textContent = "Network error. Please try again.";
        loginErr.style.display = "flex";
        btn.disabled = false;
        btn.innerHTML = \'<i class="fas fa-sign-in-alt"></i> Login\';
    }
});
</script>';
include __DIR__ . '/../partials/footer.php';
?>