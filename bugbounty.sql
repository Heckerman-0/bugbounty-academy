-- ============================================================
--  BUG BOUNTY ACADEMY — Full Database Schema & Seed Data
--  Import this into phpMyAdmin (or via mysql CLI).
--  Drops and recreates all tables, so you get a clean install.
-- ============================================================

CREATE DATABASE IF NOT EXISTS bugbounty_db;
USE bugbounty_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS user_badges;
DROP TABLE IF EXISTS writeups;
DROP TABLE IF EXISTS user_progress;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS lab_flags;
DROP TABLE IF EXISTS content;
DROP TABLE IF EXISTS badges;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Users
-- ------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    streak INT DEFAULT 0,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Content (Lessons, Tools, Labs descriptions)
-- module_group  -> groups items into dashboard modules
-- slug          -> stable identifier used to route to the right
--                  lab / tool page
-- ------------------------------------------------------------
CREATE TABLE content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('lesson','tool','lab') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    body_html LONGTEXT,
    difficulty ENUM('Beginner','Intermediate','Advanced') DEFAULT 'Beginner',
    points INT DEFAULT 10,
    module_group VARCHAR(50) DEFAULT NULL,
    slug VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Flags for labs
CREATE TABLE lab_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT UNIQUE,
    flag_text VARCHAR(255) NOT NULL,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

-- Quizzes
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT,
    title VARCHAR(255),
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

-- Questions (MCQ)
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT,
    question TEXT NOT NULL,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_answer CHAR(1) CHECK (correct_answer IN ('a','b','c','d')),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- User Progress
CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content_id INT,
    status ENUM('started','completed') DEFAULT 'started',
    score INT DEFAULT 0,
    completed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, content_id)
);

-- Badges
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-trophy',
    criteria TEXT
);
INSERT INTO badges (name, icon, criteria) VALUES
('First Blood', 'fa-skull', 'Complete your first lab'),
('Streak Master', 'fa-fire', 'Login for 7 days straight'),
('Quiz Whiz', 'fa-brain', 'Score 100% on a quiz');

-- User Badges
CREATE TABLE user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    badge_id INT,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, badge_id)
);

-- Community Writeups
CREATE TABLE writeups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
--  SEED CONTENT
-- ============================================================

-- --- Module 1: HTTP Fundamentals ---
INSERT INTO content (type, title, description, body_html, difficulty, points, module_group, slug) VALUES
('lesson',
 'Understanding HTTP Requests',
 'Learn how HTTP works for bug bounty.',
 '<div class="lesson-header">
    <span class="badge">🔥 Beginner</span>
    <span class="duration">⏱️ 8 min read</span>
</div>

<h2>🌐 What is HTTP?</h2>
<p>HTTP (Hypertext Transfer Protocol) is the <strong>backbone of the web</strong>. Every time you click a link, submit a form, or load an image, your browser speaks HTTP.</p>

<div class="highlight-box">
    <strong>🎯 Bug Bounty Connection:</strong> Understanding HTTP is <em>mandatory</em> for bug bounty. Every attack (SQLi, XSS, SSRF) travels over HTTP. Master this, and you master the attack surface.
</div>

<h3>⚡ The HTTP Request/Response Cycle</h3>
<p>Your browser (client) sends a <strong>Request</strong> to a server. The server processes it and sends back a <strong>Response</strong>.</p>
<pre><code>Client (Browser) ── Request ──▶ Server (Apache/Nginx)
Client (Browser) ◀── Response ── Server</code></pre>

<h3>🔑 Key Parts of a Request</h3>
<ul>
    <li><strong>Method:</strong> GET (retrieve), POST (submit), PUT (update), DELETE (remove).</li>
    <li><strong>Path:</strong> The resource you want, e.g., <code>/login.php</code>.</li>
    <li><strong>Headers:</strong> Metadata like <code>User-Agent</code>, <code>Cookie</code>, <code>Authorization</code>.</li>
    <li><strong>Body:</strong> Data sent (mostly in POST/PUT).</li>
</ul>

<h3>🚀 Practice Task</h3>
<p>Open your browser Dev Tools (F12) → Network tab. Refresh this page and inspect the initial HTML request. Can you spot the <code>User-Agent</code> header? Then try the <strong>HTTP Header Manipulation Lab</strong>!</p>

<div class="call-to-action">
    <p>✅ Ready to test your knowledge? Head to the <strong>Quiz</strong> section!</p>
</div>',
 'Beginner', 10, 'http', 'http-basics'),

-- --- Lab: SQL Injection ---
('lab',
 'SQL Injection Playground',
 'Find the admin password by breaking the search.',
 '<p>Enter anything into the search box. Try to break it!</p><p><b>Goal:</b> Find the flag (admin password).</p>',
 'Beginner', 20, 'sqli', 'sqli-playground'),

-- --- Tool: Nmap Basics ---
('tool',
 'Nmap Basics',
 'Learn to scan ports like a pro.',
 '<p>Nmap is a network scanner. Use <code>nmap -sV target</code> to detect services and versions.</p>',
 'Beginner', 5, 'nmap', 'nmap-basics'),

-- --- Lab: HTTP Header Manipulation ---
('lab',
 'HTTP Header Manipulation Lab',
 'Bypass the client-side check by modifying your User-Agent.',
 '<p>This lab expects you to change your <code>User-Agent</code> to <code>AdminHacker</code> and then submit the discovered flag.</p>',
 'Intermediate', 25, 'http', 'header-manipulation'),

-- --- Lab: Nmap Simulator + Lab ---
('lab',
 'Nmap Simulator + Lab',
 'Run a simulated scan and submit the hidden port number.',
 '<p>Use the Nmap simulator to scan 127.0.0.1. The flag is a <strong>port number</strong>.</p>',
 'Beginner', 20, 'nmap', 'nmap-lab'),

-- --- Lab: XSS Playground ---
('lab',
 'XSS Playground',
 'Inject a JavaScript alert into the comment box.',
 '<p>This is a classic Reflected XSS lab. Trigger <code><script>alert()</script></code> and submit the flag when you succeed.</p>',
 'Beginner', 20, 'xss', 'xss-playground'),

-- --- Lesson: XSS Theory ---
('lesson',
 'Cross-Site Scripting (XSS) Explained',
 'Reflected, stored and DOM-based XSS — and how to find them.',
 '<div class="lesson-header">
    <span class="badge">🔥 Beginner</span>
    <span class="duration">⏱️ 10 min read</span>
</div>

<h2>💉 What is XSS?</h2>
<p>Cross-Site Scripting (XSS) lets an attacker inject <strong>malicious JavaScript</strong> into a web page that other users view. The injected script runs in the <em>victim browser</em>, so it can steal cookies, session tokens, or perform actions as the victim.</p>

<div class="highlight-box">
    <strong>🎯 Bug Bounty Connection:</strong> XSS is one of the most reported bug bounty findings. Even a "low impact" XSS on a high-profile domain can be valuable.
</div>

<h3>🔁 The Three Types</h3>
<ul>
    <li><strong>Reflected:</strong> Your payload is echoed back immediately by the server (e.g. in a search box).</li>
    <li><strong>Stored:</strong> The payload is saved on the server (e.g. a comment) and runs for every visitor.</li>
    <li><strong>DOM-based:</strong> The payload never touches the server — it is executed purely by client-side JavaScript.</li>
</ul>

<h3>🧪 Test Payloads</h3>
<pre><code><script>alert(1)</script>
<img src=x onerror=alert(1)>
<svg onload=alert(1)>
javascript:alert(1)</code></pre>

<h3>🛡️ How to Prevent It</h3>
<ul>
    <li>Escape all dynamic output (<code>htmlspecialchars()</code> in PHP).</li>
    <li>Use a strict Content-Security-Policy (CSP).</li>
    <li>Never trust user input, even after validation.</li>
</ul>

<div class="call-to-action">
    <p>✅ Now go break things in the <strong>XSS Playground lab</strong>!</p>
</div>',
 'Beginner', 10, 'xss', 'xss-theory'),

-- --- Lesson: OWASP Top 10 ---
('lesson',
 'OWASP Top 10 Introduction',
 'The 2021 OWASP Top 10: the most critical web app risks.',
 '<div class="lesson-header">
    <span class="badge">💀 Intermediate</span>
    <span class="duration">⏱️ 12 min read</span>
</div>

<h2>🛡️ What is OWASP?</h2>
<p>The <strong>Open Worldwide Application Security Project</strong> publishes a consensus list of the most critical web application security risks. Bug bounty hunters use it as a <strong>checklist</strong> of what to test for.</p>

<h3>📋 Top 5 You Must Know</h3>
<ul>
    <li><strong>A01 — Broken Access Control:</strong> Users can access resources they should not. (IDOR is a classic example.)</li>
    <li><strong>A02 — Cryptographic Failures:</strong> Weak hashing, missing TLS, hardcoded secrets.</li>
    <li><strong>A03 — Injection:</strong> SQL injection, command injection, NoSQL injection.</li>
    <li><strong>A04 — Insecure Design:</strong> Missing rate limits, trust boundaries, business logic flaws.</li>
    <li><strong>A05 — Security Misconfiguration:</strong> Default credentials, directory listing, verbose errors.</li>
</ul>

<p>Continue with: <strong>A06 Vulnerable Components</strong>, <strong>A07 Auth Failures</strong>, <strong>A08 Integrity Failures</strong>, <strong>A09 Logging Failures</strong>, <strong>A10 SSRF</strong>.</p>

<div class="call-to-action">
    <p>✅ Know the list, then take the <strong>OWASP Quiz</strong>!</p>
</div>',
 'Intermediate', 15, 'owasp', 'owasp-top10'),

-- --- Lesson: Directory Traversal ---
('lesson',
 'Directory Traversal Attacks',
 'Reading arbitrary files with ../../ sequences.',
 '<div class="lesson-header">
    <span class="badge">💀 Intermediate</span>
    <span class="duration">⏱️ 8 min read</span>
</div>

<h2>📂 What is Path Traversal?</h2>
<p>Directory traversal lets an attacker read files <strong>outside</strong> the web root using <code>../</code> sequences in file parameters.</p>

<h3>🎯 Example Payloads</h3>
<pre><code>../../../../etc/passwd
..%2f..%2f..%2fetc%2fpasswd
....//....//etc/passwd</code></pre>

<div class="highlight-box">
    <strong>🎯 Bug Bounty Connection:</strong> Look for <code>file=</code>, <code>download=</code>, <code>path=</code>, <code>page=</code> parameters. Always test with encoded and double-encoded payloads.
</div>

<h3>🛡️ Preventing It</h3>
<ul>
    <li>Use a whitelist of allowed filenames.</li>
    <li>Canonicalize the path and verify it starts with the allowed base dir.</li>
    <li>Never trust user-supplied file paths.</li>
</ul>',
 'Intermediate', 15, 'dir', 'directory-traversal'),

-- --- Lesson: Auth Bypass ---
('lesson',
 'Authentication Bypass Techniques',
 'Flaws in login, sessions, and password resets.',
 '<div class="lesson-header">
    <span class="badge">💀 Intermediate</span>
    <span class="duration">⏱️ 10 min read</span>
</div>

<h2>🔓 Authentication vs Authorization</h2>
<p>Authentication verifies <em>who you are</em>. Authorization decides <em>what you can do</em>. Bugs hide in both.</p>

<h3>⚔️ Common Bypass Techniques</h3>
<ul>
    <li><strong>SQL injection in the login form:</strong> <code>admin'' -- -</code></li>
    <li><strong>Weak session tokens:</strong> predictable, non-random, or never rotated.</li>
    <li><strong>IDOR on account endpoints:</strong> change <code>user_id</code> in the URL to edit another account.</li>
    <li><strong>Forgot-password flows:</strong> guessable tokens, user-enumerable responses.</li>
    <li><strong>Client-side checks only:</strong> the server trusts anything the browser sends.</li>
</ul>

<div class="highlight-box">
    <strong>🎯 Pro Tip:</strong> Always test the same request in a fresh browser / Burp session. Auth bugs often only show up when you replay the flow exactly.
</div>

<h3>🛡️ Preventing It</h3>
<ul>
    <li>Use password_hash() / password_verify() in PHP.</li>
    <li>Use HttpOnly, Secure, SameSite cookies.</li>
    <li>Always authorize server-side on every request.</li>
</ul>',
'Intermediate', 15, 'auth', 'auth-bypass'),

-- --- Lesson: Command Injection Theory ---
('lesson',
 'Command Injection Explained',
 'Injecting OS commands through vulnerable parameters.',
 '<div class="lesson-header">
    <span class="badge">💀 Intermediate</span>
    <span class="duration">⏱️ 9 min read</span>
</div>

<h2>💣 What is Command Injection?</h2>
<p>Command injection lets an attacker execute <strong>arbitrary OS commands</strong> on the server by injecting shell syntax into a parameter that is passed to a shell (e.g. <code>system()</code>, <code>exec()</code>, <code>shell_exec()</code>).</p>

<div class="highlight-box">
    <strong>🎯 Bug Bounty Connection:</strong> Command injection can lead to full server compromise (RCE). It is a critical-severity finding worth reporting immediately.
</div>

<h3>🎯 Injection Payloads</h3>
<pre><code>127.0.0.1; whoami
127.0.0.1 && cat /etc/passwd
127.0.0.1 | id
$(whoami)
`whoami`</code></pre>

<h3>🛡️ How to Prevent It</h3>
<ul>
    <li>Never pass user input to a shell — use parameterized APIs instead.</li>
    <li>Validate input against a strict allowlist (e.g. only IP addresses).</li>
    <li>Escape shell metacharacters if you must use a shell.</li>
</ul>

<div class="call-to-action">
    <p>✅ Now try the <strong>Command Injection Lab</strong>!</p>
</div>',
 'Intermediate', 15, 'cmd', 'cmd-injection-theory'),

-- --- Lesson: SSRF Theory ---
('lesson',
 'Server-Side Request Forgery (SSRF)',
 'Making the server fetch internal resources.',
 '<div class="lesson-header">
    <span class="badge">💀 Intermediate</span>
    <span class="duration">⏱️ 10 min read</span>
</div>

<h2>🌐 What is SSRF?</h2>
<p>SSRF occurs when an attacker can make the <strong>server itself</strong> make HTTP requests to arbitrary URLs. This lets attackers reach internal services (127.0.0.1, cloud metadata, internal admin panels) that are not exposed to the internet.</p>

<div class="highlight-box">
    <strong>🎯 Bug Bounty Connection:</strong> SSRF is a top-scoring bug. Cloud metadata endpoints like <code>http://169.254.169.254/latest/meta-data/</code> can leak AWS credentials.
</div>

<h3>🎯 Common SSRF Targets</h3>
<pre><code>http://127.0.0.1:8080/admin
http://169.254.169.254/latest/meta-data/
http://localhost:3306
file:///etc/passwd (in some SSRF implementations)</code></pre>

<h3>🛡️ How to Prevent It</h3>
<ul>
    <li>Validate the URL against an allowlist of hosts.</li>
    <li>Block private/loopback IP ranges.</li>
    <li>Use a proxy that denies internal requests.</li>
</ul>

<div class="call-to-action">
    <p>✅ Now try the <strong>SSRF Lab</strong>!</p>
</div>',
 'Intermediate', 15, 'ssrf', 'ssrf-theory'),

-- --- Lesson: IDOR Theory ---
('lesson',
 'Insecure Direct Object References (IDOR)',
 'Accessing other users\' data by changing IDs.',
 '<div class="lesson-header">
    <span class="badge">💀 Intermediate</span>
    <span class="duration">⏱️ 8 min read</span>
</div>

<h2>🎯 What is IDOR?</h2>
<p>IDOR (a type of <strong>Broken Access Control</strong>) happens when an application exposes a direct reference to an object (like a database ID) and fails to verify the user is authorized to access it. Changing <code>user_id=101</code> to <code>user_id=1</code> might let you view the admin profile.</p>

<div class="highlight-box">
    <strong>🎯 Bug Bounty Connection:</strong> IDOR is extremely common and can be critical. Look for numeric IDs in URLs, AJAX calls, and API endpoints.
</div>

<h3>🎯 Example</h3>
<pre><code>GET /profile.php?user_id=1   (admin)
GET /profile.php?user_id=2   (another user)</code></pre>

<h3>🛡️ How to Prevent It</h3>
<ul>
    <li>Always authorize server-side on every object access.</li>
    <li>Use indirect references (UUIDs, opaque tokens).</li>
    <li>Never expose internal database IDs.</li>
</ul>

<div class="call-to-action">
    <p>✅ Now try the <strong>IDOR Lab</strong>!</p>
</div>',
 'Intermediate', 15, 'idor', 'idor-theory'),

-- --- Lesson: File Upload Theory ---
('lesson',
 'File Upload Vulnerabilities',
 'Uploading webshells and bypassing filters.',
 '<div class="lesson-header">
    <span class="badge">💀 Intermediate</span>
    <span class="duration">⏱️ 9 min read</span>
</div>

<h2>📤 What is a File Upload Vulnerability?</h2>
<p>When an application lets users upload files but fails to validate them, an attacker can upload a <strong>webshell</strong> (e.g. <code>shell.php</code>) and execute arbitrary code on the server.</p>

<div class="highlight-box">
    <strong>🎯 Bug Bounty Connection:</strong> File upload bugs can quickly escalate to RCE and full server compromise.
</div>

<h3>🎯 Bypass Techniques</h3>
<ul>
    <li>Double extension: <code>shell.php.jpg</code></li>
    <li>Case tricks: <code>shell.PHP</code>, <code>shell.pHp</code></li>
    <li>Null byte (old): <code>shell.php%00.jpg</code></li>
    <li>Content-Type spoofing: <code>image/png</code> with PHP body</li>
</ul>

<h3>🛡️ How to Prevent It</h3>
<ul>
    <li>Validate the file extension against an allowlist.</li>
    <li>Verify the MIME type and content (magic bytes).</li>
    <li>Store files outside the web root or on a separate storage.</li>
    <li>Serve files with a non-executing Content-Type.</li>
</ul>

<div class="call-to-action">
    <p>✅ Now try the <strong>File Upload Lab</strong>!</p>
</div>',
 'Intermediate', 15, 'upload', 'file-upload-theory'),

-- --- Lesson: CSRF Theory ---
('lesson',
 'Cross-Site Request Forgery (CSRF)',
 'Forcing a victim to make unintended requests.',
 '<div class="lesson-header">
    <span class="badge">💀 Intermediate</span>
    <span class="duration">⏱️ 8 min read</span>
</div>

<h2>🎭 What is CSRF?</h2>
<p>CSRF tricks a logged-in victim into making an <strong>unintended request</strong> (like changing their email or transferring money) to a site where they are authenticated. The attacker hosts a malicious page that sends the request using the victim\'s session cookies.</p>

<div class="highlight-box">
    <strong>🎯 Bug Bounty Connection:</strong> CSRF is rated by impact. Chained with a state-changing action (e.g. password reset), it becomes critical.
</div>

<h3>🎯 Example CSRF HTML</h3>
<pre><code><img src="https://victim.com/change-email.php?new=hacker@evil.com"></code></pre>

<h3>🛡️ How to Prevent It</h3>
<ul>
    <li>Use <strong>CSRF tokens</strong> (random per-session, validated on every state-changing request).</li>
    <li>Use <code>SameSite=Strict</code> cookies.</li>
    <li>Verify custom headers (e.g. <code>X-Requested-With</code>).</li>
</ul>

<div class="call-to-action">
    <p>✅ This whole academy now uses CSRF tokens — see them in action!</p>
</div>',
 'Intermediate', 15, 'csrf', 'csrf-theory'),

-- --- Lab: Command Injection ---
('lab',
 'Command Injection Lab',
 'Break out of the ping command and read the flag.',
 '<p>This tool pings a host. Try injecting a command with <code>;</code> or <code>&&</code> to run <code>whoami</code> and read the flag file.</p><p><b>Goal:</b> Find the flag.</p>',
 'Intermediate', 25, 'cmd', 'cmd-injection'),

-- --- Lab: SSRF ---
('lab',
 'SSRF Lab',
 'Make the server fetch an internal admin page.',
 '<p>This tool fetches URLs on the server side. Try accessing <code>http://127.0.0.1:8080/admin</code> to read the flag.</p><p><b>Goal:</b> Find the flag.</p>',
 'Intermediate', 25, 'ssrf', 'ssrf-lab'),

-- --- Lab: IDOR ---
('lab',
 'IDOR Lab',
 'Access another user\'s profile by changing the user_id.',
 '<p>You are logged in as a normal user. Try to access the admin profile by changing the <code>user_id</code> parameter.</p><p><b>Goal:</b> Find the flag.</p>',
 'Intermediate', 25, 'idor', 'idor-lab'),

-- --- Lab: File Upload ---
('lab',
 'File Upload Lab',
 'Upload a webshell and execute commands to read the flag.',
 '<p>This app lets you upload any file. Upload a PHP webshell and use it to read the flag file.</p><p><b>Goal:</b> Find the flag.</p>',
 'Intermediate', 30, 'upload', 'file-upload');

-- ------------------------------------------------------------
--  LAB FLAGS
-- ------------------------------------------------------------
INSERT INTO lab_flags (content_id, flag_text) VALUES
(2, 'admin_password_123'),      -- SQLi, shown in the dumped table
(4, 'HTTP_UA_BYPASS_9f2'),      -- Header manipulation
(5, 'open_port_8080'),          -- Nmap lab
(6, 'XSS_WINS_123'),            -- XSS playground
(16, 'CMD_EXEC_SUCCESS_7c1'),   -- Command injection
(17, 'SSRF_INTERNAL_3a9'),      -- SSRF
(18, 'IDOR_ADMIN_ACCESS_5f8'),  -- IDOR
(19, 'UPLOAD_WEBSHELL_9d4');    -- File upload

-- ------------------------------------------------------------
--  QUIZZES
-- ------------------------------------------------------------
INSERT INTO quizzes (content_id, title) VALUES
(1, 'HTTP Basics Quiz'),
(7, 'XSS Basics Quiz'),
(8, 'OWASP Top 10 Quiz'),
(11, 'Command Injection Quiz'),  -- cmd-injection-theory
(12, 'SSRF Quiz'),               -- ssrf-theory
(13, 'IDOR Quiz'),               -- idor-theory
(14, 'File Upload Quiz'),        -- file-upload-theory
(15, 'CSRF Quiz');               -- csrf-theory

-- Quiz 1 → HTTP (quiz_id 1)
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(1, 'What does HTTP stand for?', 'Hyper Text Transfer Protocol', 'High Tech Transfer Protocol', 'Hyper Transfer Text Protocol', 'None', 'a'),
(1, 'Which HTTP method is used to retrieve data?', 'POST', 'GET', 'PUT', 'DELETE', 'b'),
(1, 'Which part of the HTTP request identifies your browser type?', 'Cookie', 'User-Agent', 'Host', 'Body', 'b'),
(1, 'Which status code means "Not Found"?', '200', '301', '404', '500', 'c');

-- Quiz 2 → XSS (quiz_id 2)
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(2, 'Which XSS type executes purely in the client browser without touching the server?', 'Reflected', 'Stored', 'DOM-based', 'Blind', 'c'),
(2, 'Which payload would run JavaScript on an old, unpatched browser?', '<b>bold</b>', '<script>alert(1)</script>', '<p>para</p>', 'alert(1)', 'b'),
(2, 'What is the primary defence against stored XSS in PHP?', 'mysql_real_escape_string()', 'htmlspecialchars() on output', 'trim()', 'md5()', 'b');

-- Quiz 3 → OWASP (quiz_id 3)
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(3, 'Which OWASP category is an IDOR usually classified under?', 'Injection', 'Broken Access Control', 'SSRF', 'Misconfiguration', 'b'),
(3, 'Weak password hashing falls under which category?', 'Injection', 'Cryptographic Failures', 'Logging Failures', 'SSRF', 'b'),
(3, 'Which is an example of Security Misconfiguration?', 'Default admin password left enabled', 'SQL injection', 'Missing CSP (that is actually A05 too)', 'Header injection', 'a');

-- Quiz 4 → Command Injection (quiz_id 4)
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(4, 'Which character can be used to chain commands in a shell?', ':', ';', ',', '.', 'b'),
(4, 'What is the most critical impact of command injection?', 'Data breach', 'Remote code execution (RCE)', 'XSS', 'DoS only', 'b'),
(4, 'Which is the BEST defence against command injection?', 'Blacklisting special chars', 'Escaping input', 'Never passing user input to a shell', 'Using md5() on input', 'c');

-- Quiz 5 → SSRF (quiz_id 5)
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(5, 'What does SSRF let an attacker do?', 'Make the server fetch arbitrary URLs', 'Inject SQL', 'Steal cookies via XSS', 'Brute force logins', 'a'),
(5, 'Which endpoint is a classic SSRF target for AWS credential theft?', 'http://127.0.0.1', 'http://169.254.169.254/latest/meta-data/', 'http://google.com', 'http://localhost:80', 'b'),
(5, 'Which is a valid SSRF defence?', 'Blocking private IP ranges', 'URL encoding', 'Using GET instead of POST', 'Adding a password', 'a');

-- Quiz 6 → IDOR (quiz_id 6)
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(6, 'IDOR is a type of which OWASP category?', 'Injection', 'Broken Access Control', 'Logging Failure', 'SSRF', 'b'),
(6, 'Which request pattern is vulnerable to IDOR?', 'GET /profile.php?id=1', 'Static HTML page', 'POST with CSRF token', 'HTTPS request', 'a'),
(6, 'What is the best fix for IDOR?', 'Hide IDs in the DOM', 'Authorize server-side on every access', 'Use POST instead of GET', 'Rate limiting only', 'b');

-- Quiz 7 → File Upload (quiz_id 7)
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(7, 'What is a common file upload bypass?', 'Double extension (shell.php.jpg)', 'Using uppercase file names in URL', 'Adding spaces', 'Renaming to .txt', 'a'),
(7, 'What is a webshell?', 'A UI framework', 'A script that executes commands on the server', 'A type of firewall', 'An antivirus tool', 'b'),
(7, 'Which is the BEST file upload defence?', 'Check extension against allowlist', 'Check file size only', 'Trust the client MIME type', 'Rename file randomly', 'a');

-- Quiz 8 → CSRF (quiz_id 8)
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(8, 'CSRF forces a victim to do what?', 'Brute force a password', 'Make an unintended state-changing request', 'Run a SQL query', 'Scan a port', 'b'),
(8, 'Which is the PRIMARY defence against CSRF?', 'CSRF tokens', 'HTTPS', 'Input validation', 'CAPTCHA on every page', 'a'),
(8, 'What does SameSite=Strict do?', 'Encrypts cookies', 'Restricts cookies to same-site requests', 'Deletes cookies', 'Adds a token to cookies', 'b');

