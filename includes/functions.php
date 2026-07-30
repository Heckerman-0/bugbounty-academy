<?php
require_once 'db.php';

function updateStreak($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT last_login, streak FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $u = $stmt->fetch();
    if (!$u) return;
    
    $today = date('Y-m-d');
    $last = $u['last_login'] ? date('Y-m-d', strtotime($u['last_login'])) : null;
    
    if ($last == $today) return;
    
    $diff = $last ? (strtotime($today) - strtotime($last)) / 86400 : 2;
    $new_streak = ($diff == 1) ? $u['streak'] + 1 : 1;
    
    $upd = $pdo->prepare("UPDATE users SET streak=?, last_login=NOW() WHERE id=?");
    $upd->execute([$new_streak, $user_id]);
    
    if ($new_streak == 7) awardBadge($user_id, 2);
}

function awardBadge($user_id, $badge_id) {
    global $pdo;
    $check = $pdo->prepare("SELECT * FROM user_badges WHERE user_id=? AND badge_id=?");
    $check->execute([$user_id, $badge_id]);
    if ($check->rowCount() == 0) {
        $ins = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?,?)");
        $ins->execute([$user_id, $badge_id]);
    }
}

function markComplete($user_id, $content_id) {
    global $pdo;
    $chk = $pdo->prepare("SELECT * FROM user_progress WHERE user_id=? AND content_id=?");
    $chk->execute([$user_id, $content_id]);
    $row = $chk->fetch();
    if ($row && $row['status'] == 'completed') return;
    
    if ($row) {
        $upd = $pdo->prepare("UPDATE user_progress SET status='completed', completed_at=NOW() WHERE user_id=? AND content_id=?");
        $upd->execute([$user_id, $content_id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO user_progress (user_id, content_id, status, completed_at) VALUES (?,?, 'completed', NOW())");
        $ins->execute([$user_id, $content_id]);
    }
    
    $chkLab = $pdo->prepare("SELECT * FROM content WHERE id=? AND type='lab'");
    $chkLab->execute([$content_id]);
    if ($chkLab->rowCount() > 0) {
        awardBadge($user_id, 1);
    }
}

function getProgress($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT content_id, status FROM user_progress WHERE user_id=?");
    $stmt->execute([$user_id]);
    $results = [];
    while ($row = $stmt->fetch()) {
        $results[$row['content_id']] = $row['status'];
    }
    return $results;
}

function getBadges($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT b.* FROM badges b JOIN user_badges ub ON b.id = ub.badge_id WHERE ub.user_id=?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}
?>