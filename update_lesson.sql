-- Run this in phpMyAdmin to update the HTTP lesson with rich content
UPDATE content SET body_html = ''
<div class="lesson-header">
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
<p>Open your browser''s Dev Tools (F12) → Network tab. Refresh this page and inspect the initial HTML request. Can you spot the <code>User-Agent</code> header?</p>

<div class="call-to-action">
    <p>✅ Ready to test your knowledge? Head to the <strong>Quiz</strong> section!</p>
</div>
'' WHERE id = 1;