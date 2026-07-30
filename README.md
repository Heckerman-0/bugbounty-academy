# 🛡️ Bug Bounty Academy

A self-hosted learning platform built with XAMPP (PHP/MySQL) to teach web security, bug bounty techniques, and ethical hacking through vulnerable labs, quizzes, and gamification.

## ⚠️ CRITICAL WARNING
**This application contains intentionally vulnerable code (SQL Injection, XSS, etc.).**
- **DO NOT** deploy this on a public web server without authentication or a firewall.
- Use only on **localhost**, **LAN**, or behind a **VPN** (e.g., Tailscale).

## Features
- 📚 Theory Lessons (OWASP Top 10, HTTP, etc.)
- 💻 Interactive Vulnerable Labs (SQLi, XSS)
- 🛠️ Tool Simulators (Nmap, etc.)
- 🧠 Quizzes with MCQs
- 📊 Progress Tracking
- 🏅 Gamification (Streaks & Badges)
- 📝 Community Write-up Sharing

## Installation
1. Install [XAMPP](https://www.apachefriends.org/).
2. Clone this repo into `C:\xampp\htdocs\bugbounty`.
3. Open phpMyAdmin, create `bugbounty_db`, and import `bugbounty.sql`.
4. Open `http://localhost/bugbounty/`.
5. Register a user. To get admin access, change `role` to `admin` in the database.

## Tech Stack
- PHP 8.x
- MySQL (via phpMyAdmin)
- Apache (XAMPP)
- Pure HTML/CSS (No external frameworks required)

## Contributing
Pull requests are welcome! Just ensure you keep the intentionally vulnerable code isolated inside the `labs/` folder.