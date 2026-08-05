# Bug Bounty Academy — Improvement Plan

## Goal: Every module has a full **Lesson → Lab → Quiz** flow, with quizzes based on lessons and unlocked only after completing both the lesson and lab.

### Steps

- [x] **1.** Add missing lessons to `content`:
  - SQL Injection Explained (module: sqli) — id 20
  - Network Scanning with Nmap (module: nmap) — id 21
- [x] **2.** Add missing labs to `content` + `lab_flags`:
  - OWASP Top 10 Challenge Lab (module: owasp) — id 22
  - Directory Traversal Lab (module: dir) — id 23
  - Authentication Bypass Lab (module: auth) — id 24
  - CSRF Exploitation Lab (module: csrf) — id 25
- [x] **3.** Create 4 new lab PHP files in `modules/labs/`:
  - `owasp_lab.php`
  - `dir_traversal.php`
  - `auth_bypass.php`
  - `csrf_lab.php`
- [x] **4.** Add quizzes + questions for the new content
  - Quizzes for: sqli, nmap, owasp, dir, auth
- [x] **5.** Update `modules/quizzes/take.php`:
  - Redesign quiz interface (modern UI)
  - Lock quiz until module's lesson AND lab completed
- [x] **6.** Update `dashboard.php`:
  - Order each module as: 📖 Lesson → 💉 Lab → 📝 Quiz
  - Show quiz as locked/unlocked
- [x] **7.** Update `modules/labs/index.php` routing for new lab slugs
- [x] **8.** Update `bugbounty.sql` dump so changes persist
- [x] **9.** Test everything end-to-end
