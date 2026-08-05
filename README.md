# 🛡️ Bug Bounty Academy

A self-hosted learning platform built with XAMPP (PHP/MySQL) to teach web security, bug bounty techniques, and ethical hacking through vulnerable labs, quizzes, and gamification.

## ⚠️ CRITICAL WARNING
**This application contains intentionally vulnerable code (SQL Injection, XSS, Command Injection, SSRF, etc.).**
- **DO NOT** deploy this on a public web server without authentication or a firewall.
- Use only on **localhost**, **LAN**, or behind a **VPN** (e.g., Tailscale).

## Features
- 📚 Theory Lessons (OWASP Top 10, HTTP, XSS, Command Injection, SSRF, IDOR, File Upload, CSRF)
- 💻 Interactive Vulnerable Labs (SQLi, XSS, Header Manipulation, Nmap, Command Injection, SSRF, IDOR, File Upload)
- 🛠️ Tool Simulators (Nmap, etc.)
- 🧠 Quizzes with MCQs (8 quizzes, 28 questions)
- 📊 Progress Tracking
- 🏅 Gamification (Streaks & Badges)
- 🥇 Leaderboard
- 📝 Community Write-up Sharing
- 🔐 CSRF protection on all state-changing forms

## Installation

### 1. Install XAMPP
Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL** from the XAMPP Control Panel.

### 2. Clone the repo
Clone into your `htdocs` folder:
```
git clone https://github.com/Heckerman-0/bugbounty-academy.git C:\xampp\htdocs\bugbounty
```

### 3. Create the config file (IMPORTANT)
> `includes/config.php` contains your database credentials and is **gitignored** (never committed). You must create it from the template or the app will show a "Missing configuration" message.

**Windows (XAMPP):**
```
copy includes\config.example.php includes\config.php
```
**macOS / Linux:**
```
cp includes/config.example.php includes/config.php
```

Then open `includes/config.php` and adjust the database credentials if needed (defaults are `root` with no password for a stock XAMPP install).

### 4. Import the database
1. Open phpMyAdmin at `http://localhost/phpmyadmin`.
2. Create a database named `bugbounty_db` (or change `DB_NAME` in `config.php`).
3. Select it, go to the **Import** tab, and choose `bugbounty.sql`.
4. Click **Go**. This creates all tables and seed data (lessons, labs, flags, quizzes).

### 5. Run the app
Open `http://localhost/bugbounty/` in your browser.

### 6. Create an account
- **Normal user:** Use the **Register** page.
- **Admin access:** After registering, change your `role` to `admin` directly in the `users` table in phpMyAdmin to unlock the admin panel.

> **Note:** The Command Injection lab uses `shell_exec()` and the File Upload lab allows PHP uploads. Ensure you are running this **only on localhost** and disable them in production.

## Project Structure
```
├── admin/                 # Admin panel (moderate writeups/content)
├── assets/                # CSS/JS
├── includes/              # auth, db, config, functions, nav
├── modules/
│   ├── labs/              # Vulnerable labs (SQLi, XSS, CMD, SSRF, IDOR, Upload)
│   ├── lessons/           # Theory lesson viewer
│   ├── quizzes/           # Quiz taking
│   ├── tools/             # Tool simulators (Nmap)
│   └── writeups/          # Community write-ups
├── bugbounty.sql          # Full schema + seed data
├── dashboard.php          # Main learning dashboard
├── leaderboard.php        # Rankings
├── index.php / login.php / register.php / profile.php / logout.php
└── TODO.md                # Improvement roadmap
```

## Tech Stack
- PHP 8.x
- MySQL (via phpMyAdmin)
- Apache (XAMPP)
- Pure HTML/CSS (No external frameworks required)

## Contributing
Pull requests are welcome! Just ensure you keep the intentionally vulnerable code isolated inside the `labs/` folder.
