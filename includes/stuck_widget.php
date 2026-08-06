<?php
// Reusable "Stuck?" help widget for labs.
// Each lab sets an array named $stuckSteps (list of step strings) plus
// an optional $stuckTip (a short "hint" string) before including this file.
if (!isset($stuckSteps)) $stuckSteps = [];
if (!isset($stuckTip)) $stuckTip = '';
?>
<div class="stuck-wrap">
    <button class="stuck-btn" onclick="toggleStuck()">🙋 Stuck?</button>
    <div class="stuck-panel" id="stuckPanel">
        <div class="stuck-head">
            <h3>🙋 Need a hand?</h3>
            <button class="stuck-close" onclick="toggleStuck()">✕</button>
        </div>
        <div class="stuck-body">
            <p>Here's a step-by-step walkthrough of how this lab works and how to solve it:</p>
            <ol>
                <?php foreach ($stuckSteps as $step): ?>
                    <li><?= htmlspecialchars($step) ?></li>
                <?php endforeach; ?>
            </ol>
            <?php if ($stuckTip): ?>
                <div class="hint-tip">💡 <strong>Pro tip:</strong> <?= htmlspecialchars($stuckTip) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    function toggleStuck() {
        var p = document.getElementById('stuckPanel');
        p.classList.toggle('open');
    }
    // Close when clicking outside
    document.addEventListener('click', function(e) {
        var p = document.getElementById('stuckPanel');
        var w = document.querySelector('.stuck-wrap');
        if (p.classList.contains('open') && w && !w.contains(e.target)) {
            p.classList.remove('open');
        }
    });
</script>
