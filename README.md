# SafeSignal AI 🛡️

> **AI-powered community safety & hazard reporting platform**  
> Built for **TechHacks 2.0 — Hack4Hope** | SDG 11 & SDG 16 Aligned

[![SDG 11](https://img.shields.io/badge/SDG%2011-Sustainable%20Cities-orange)](#) [![SDG 16](https://img.shields.io/badge/SDG%2016-Peace%20%26%20Justice-blue)](#) [![Powered by Gemini](https://img.shields.io/badge/AI-Gemini%20AI-cyan)](#)

---

## 📋 Project Description

SafeSignal AI enables citizens to **report real-world incidents** (crime, flood, fire, harassment, accidents, infrastructure damage, pollution) which are then **automatically classified by Gemini AI**, visualized on a **live interactive map**, and used to **trigger alerts** for nearby users. An admin dashboard allows authorities to review, verify, and resolve reports.

---

## 🎯 SDG Alignment

| SDG | Goal | How SafeSignal Helps |
|-----|------|----------------------|
| **SDG 11** | Sustainable Cities & Communities | Real-time urban hazard reporting enables evidence-based city planning |
| **SDG 16** | Peace, Justice & Strong Institutions | Formal crime/harassment reporting with AI documentation supports law enforcement |

---

## ✨ Key Features

- 🤖 **Gemini AI Classification** — Auto-classifies category, severity, generates summary & recommended actions
- 🗺️ **Live Leaflet Map** — Color-coded severity markers with filters (category/severity/status/time)
- 🔔 **Realtime Alerts** — Polling every 8 seconds for high-severity incident toasts
- 📊 **Admin Dashboard** — Data tables, Chart.js analytics, status management, CSV export
- 🔒 **Secure Auth** — BCrypt hashing, session-based, rate limiting
- 📱 **Fully Responsive** — Mobile, tablet, desktop support
- 🌐 **Crowd Verification** — Citizens can confirm reports to increase reliability

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3 (Vanilla), JavaScript (ES2022) |
| Backend | PHP 8.0+ (routing + API + controllers) |
| Database | MySQL 5.7+ / MariaDB |
| AI | Google Gemini 1.5 Flash API |
| Maps | Leaflet.js 1.9.4 |
| Charts | Chart.js 4.4 |
| Icons | Font Awesome 6.5 |
| Fonts | Google Fonts (Inter, Space Grotesk) |

---

## 📁 Project Structure

```
SafeSignal/
├── index.php               # Landing page
├── config/
│   └── config.php          # DB config, API keys, helpers
├── database/
│   ├── schema.sql          # DB tables
│   └── seed.sql            # Admin user + 10 sample reports
├── services/
│   └── gemini.php          # Gemini AI service class
├── api/
│   ├── auth/               # register.php, login.php, logout.php
│   ├── reports/            # create.php, list.php, get.php, confirm.php
│   ├── admin/reports/      # status.php
│   ├── alerts/             # latest.php
│   └── contact/            # submit.php
├── pages/
│   ├── login.php
│   ├── register.php
│   ├── map.php             # Live map page
│   ├── report.php          # Submit report form
│   ├── dashboard.php       # User dashboard
│   ├── admin.php           # Admin panel
│   ├── about.php
│   └── contact.php
├── partials/
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/
│   │   ├── style.css       # Global design system
│   │   ├── map.css
│   │   ├── admin.css
│   │   └── dashboard.css
│   └── js/
│       ├── main.js         # Toast, alerts polling, navbar
│       ├── map.js          # Leaflet map, markers, modal
│       ├── report.js       # Report form, location picker
│       ├── admin.js        # Admin dashboard, charts
│       └── dashboard.js
├── uploads/                # User-uploaded images
└── README.md
```

---

## 🚀 Setup Instructions (XAMPP/WAMP)

### Prerequisites
- XAMPP or WAMP installed (PHP 8.0+, MySQL 5.7+)
- XAMPP running (Apache + MySQL)

### Step 1: Place Project Files
The project should already be at:
```
C:\xampp\htdocs\SafeSignal\
```

### Step 2: Import the Database

**Option A — phpMyAdmin:**
1. Open `http://localhost/phpmyadmin`
2. Click **New** → Create database named `safesignal`
3. Select the `safesignal` database
4. Click **Import** → Choose `database/schema.sql` → Click **Go**
5. Click **Import** again → Choose `database/seed.sql` → Click **Go**

**Option B — MySQL CLI (in XAMPP Shell):**
```bash
mysql -u root -p
CREATE DATABASE safesignal;
exit;
mysql -u root safesignal < C:/xampp/htdocs/SafeSignal/database/schema.sql
mysql -u root safesignal < C:/xampp/htdocs/SafeSignal/database/seed.sql
```

### Step 3: Gemini AI API Key

The Gemini API key has already been configured in `config/config.php` as provided. You can now use the AI features immediately.

---

### Step 4: Configure Database (if different from defaults)

Edit `config/config.php` if your MySQL credentials differ:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'safesignal');
define('DB_USER', 'root');     // Change if needed
define('DB_PASS', '');         // Add password if set
```

### Step 5: Launch

1. Start XAMPP (Apache + MySQL)
2. Open: **http://localhost/SafeSignal/**
3. 🎉 SafeSignal AI is live!

---

## 🔑 Demo Login Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@safesignal.ai | Admin@123 |
| **User** | john@example.com | Admin@123 |
| **User** | jane@example.com | Admin@123 |

---

## 🔌 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register.php` | Register new user |
| POST | `/api/auth/login.php` | Login |
| POST | `/api/auth/logout.php` | Logout |
| POST | `/api/reports/create.php` | Submit + AI analyze report |
| GET | `/api/reports/list.php` | List reports (filterable) |
| GET | `/api/reports/get.php?id=X` | Get single report |
| POST | `/api/reports/confirm.php?id=X` | Crowd-verify report |
| POST | `/api/admin/reports/status.php` | Update status (admin) |
| GET | `/api/alerts/latest.php` | Get recent alerts |
| POST | `/api/contact/submit.php` | Submit contact message |

---

## 🔒 Security Features

- ✅ PDO prepared statements (all DB queries)
- ✅ Password hashing with `password_hash()` (bcrypt cost 12)
- ✅ Session-based authentication
- ✅ Admin route protection (`requireAdmin()`)
- ✅ File upload validation (type + size + MIME)
- ✅ Input sanitization with `htmlspecialchars()`
- ✅ Rate limiting on report submission (5/hour per IP)
- ✅ Gemini API key from environment variable

---

## 🌐 Live Demo Flow

1. Visit **http://localhost/SafeSignal/**
2. Create an account or login with demo credentials
3. Click **Report Incident** → fill form + pin location → Submit
4. Watch AI classify the report in seconds
5. View on **Live Map** — marker appears immediately
6. Login as admin to verify/resolve + view analytics

---

*Built with ❤️ for Hack4Hope TechHacks 2.0 | Aligned with UN SDGs 11 & 16*
