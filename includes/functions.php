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
    // Safety: ensure the user exists before inserting progress (prevents FK errors).
    $uChk = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $uChk->execute([$user_id]);
    if (!$uChk->fetch()) return;

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

/**
 * Get the quiz associated with a module (via the linked lesson).
 */
function getModuleQuiz($module_group) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT q.id AS quiz_id, q.title AS quiz_title, q.content_id
        FROM quizzes q
        JOIN content c ON c.id = q.content_id
        WHERE c.module_group = ?
        LIMIT 1
    ");
    $stmt->execute([$module_group]);
    return $stmt->fetch();
}

/**
 * Check whether the user has completed BOTH the lesson and the lab
 * for a given module. This is the unlock condition for the quiz.
 */
function isModuleLessonLabComplete($user_id, $module_group) {
    global $pdo;
    $progress = getProgress($user_id);
    $stmt = $pdo->prepare("SELECT id, type FROM content WHERE module_group=? AND type IN ('lesson','lab')");
    $stmt->execute([$module_group]);
    $items = $stmt->fetchAll();
    $hasLesson = false; $lessonDone = false;
    $hasLab = false; $labDone = false;
    foreach ($items as $it) {
        if ($it['type'] == 'lesson') {
            $hasLesson = true;
            if (isset($progress[$it['id']]) && $progress[$it['id']] == 'completed') $lessonDone = true;
        } elseif ($it['type'] == 'lab') {
            $hasLab = true;
            if (isset($progress[$it['id']]) && $progress[$it['id']] == 'completed') $labDone = true;
        }
    }
    $lessonOk = (!$hasLesson || $lessonDone);
    $labOk = (!$hasLab || $labDone);
    return ($lessonOk && $labOk);
}

/**
 * Get the quiz linked to a specific lesson id.
 */
function getQuizByLesson($lesson_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE content_id=? LIMIT 1");
    $stmt->execute([$lesson_id]);
    return $stmt->fetch();
}
?>
