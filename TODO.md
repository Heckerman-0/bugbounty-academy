# Bug Bounty Academy — Full Improvement Plan

## Phase A — Security Hardening (In Progress)
- [x] CSRF tokens on nmap.php flag form
- [x] CSRF tokens on sqli.php flag form
- [x] CSRF tokens on header_lab.php flag form
- [x] CSRF tokens on xss.php flag form
- [x] CSRF tokens on admin/index.php (GET actions: delete, approve, reject)
- [ ] `intval()` on all IDs (lessons/view.php, quizzes/take.php, admin/index.php, labs)
- [ ] CSRF tokens on profile.php forms (email + password change)
- [ ] Consistent output escaping across all pages
- [ ] Fix `db.php` fallback (improve error handling)
- [ ] Remove obsolete one-time scripts

## Phase B — New Modules & Content
- [x] **New Lab:** Command Injection lab
- [x] **New Lab:** SSRF lab
- [x] **New Lab:** IDOR lab
- [x] **New Lab:** File Upload lab
- [x] **New Lesson:** Command Injection theory
- [x] **New Lesson:** SSRF theory
- [x] **New Lesson:** IDOR theory
- [x] **New Lesson:** File Upload Vulnerabilities theory
- [x] **New Lesson:** CSRF theory
- [x] **New Quiz Questions** for each new module
- [x] **Update `bugbounty.sql`** with new seed content
- [x] **Update `dashboard.php` routing** to support new labs
- [x] **Update `modules/labs/index.php`** routing for new labs
- [x] **Create new lab files:** cmd_inject.php, ssrf.php, idor.php, file_upload.php

## Phase C — Repo Polish
- [ ] Update `README.md` with new features & modules
- [ ] Clean up `nav.php` formatting
- [ ] Add difficulty-based filtering to labs index
- [ ] Add search/filter to leaderboard

