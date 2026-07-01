<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('seeker');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('jobs/index.php');
verify_csrf();

$jobId  = (int)($_POST['job_id'] ?? 0);
$action = $_POST['action'] ?? 'save';
$uid    = current_user()['id'];

// Ensure job exists & approved
$chk = db()->prepare("SELECT 1 FROM jobs WHERE id=? AND status='approved'");
$chk->execute([$jobId]);
if (!$chk->fetchColumn()) { flash('error', 'Job not available.'); redirect('jobs/index.php'); }

if ($action === 'unsave') {
    db()->prepare('DELETE FROM saved_jobs WHERE job_id=? AND seeker_id=?')->execute([$jobId, $uid]);
    flash('info', 'Removed from saved jobs.');
} else {
    $stmt = db()->prepare('INSERT IGNORE INTO saved_jobs (job_id, seeker_id) VALUES (?,?)');
    $stmt->execute([$jobId, $uid]);
    flash('success', 'Job saved!');
}
redirect('jobs/view.php?id=' . $jobId);
