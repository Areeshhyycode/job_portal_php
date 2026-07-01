<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('seeker');
$pageTitle = 'Saved jobs';
$u = current_user();

$stmt = db()->prepare(
    "SELECT j.*, u.company_name
     FROM saved_jobs s
     JOIN jobs j  ON j.id = s.job_id
     JOIN users u ON u.id = j.company_id
     WHERE s.seeker_id = ? AND j.status='approved'
     ORDER BY s.created_at DESC"
);
$stmt->execute([$u['id']]);
$jobs = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-head"><h2>Saved jobs</h2></div>
    <?php if (!$jobs): ?>
      <div class="empty"><div class="ico">★</div>No saved jobs yet. Tap “Save job” on any listing to keep it here.</div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($jobs as $job) { include __DIR__ . '/../includes/job_card.php'; } ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
