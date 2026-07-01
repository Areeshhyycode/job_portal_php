<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('seeker');
$pageTitle = 'My applications';
$u = current_user();

$stmt = db()->prepare(
    "SELECT a.*, j.title, j.location, u.company_name
     FROM applications a
     JOIN jobs j  ON j.id = a.job_id
     JOIN users u ON u.id = j.company_id
     WHERE a.seeker_id = ?
     ORDER BY a.created_at DESC"
);
$stmt->execute([$u['id']]);
$apps = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-head"><h2>My applications</h2></div>
    <?php if (!$apps): ?>
      <div class="empty"><div class="ico">📭</div>You haven't applied to any jobs yet. <a href="<?= url('jobs/index.php') ?>">Browse jobs →</a></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Job</th><th>Company</th><th>Applied</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($apps as $a): ?>
            <tr>
              <td><strong><?= e($a['title']) ?></strong><br><span class="muted"><?= e($a['location']) ?></span></td>
              <td><?= e($a['company_name']) ?></td>
              <td><?= e(time_ago($a['created_at'])) ?></td>
              <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
              <td><a class="btn btn-ghost btn-sm" href="<?= url('jobs/view.php?id=' . $a['job_id']) ?>">View job</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
