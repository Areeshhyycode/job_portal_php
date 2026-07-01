<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('company');
$pageTitle = 'Company dashboard';
$u = current_user();

$jobs = db()->prepare(
    "SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS applicants
     FROM jobs j WHERE j.company_id = ? ORDER BY j.created_at DESC"
);
$jobs->execute([$u['id']]);
$jobs = $jobs->fetchAll();

$total     = count($jobs);
$approved  = count(array_filter($jobs, fn($j) => $j['status'] === 'approved'));
$pending   = count(array_filter($jobs, fn($j) => $j['status'] === 'pending'));
$applicants= array_sum(array_column($jobs, 'applicants'));
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-head">
      <h2>Welcome, <?= e($u['company_name'] ?: $u['name']) ?></h2>
      <a class="btn btn-primary" href="<?= url('company/post_job.php') ?>">+ Post a job</a>
    </div>

    <div class="grid grid-4" style="margin-bottom:26px">
      <div class="stat"><div class="n"><?= $total ?></div><div class="l">Total jobs</div></div>
      <div class="stat"><div class="n"><?= $approved ?></div><div class="l">Live</div></div>
      <div class="stat"><div class="n"><?= $pending ?></div><div class="l">Pending approval</div></div>
      <div class="stat"><div class="n"><?= $applicants ?></div><div class="l">Applicants</div></div>
    </div>

    <?php if (!$jobs): ?>
      <div class="empty"><div class="ico">📋</div>No jobs yet. <a href="<?= url('company/post_job.php') ?>">Post your first job →</a></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Title</th><th>Type</th><th>Applicants</th><th>Status</th><th>Posted</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($jobs as $j): ?>
            <tr>
              <td><strong><?= e($j['title']) ?></strong><br><span class="muted"><?= e($j['location']) ?></span></td>
              <td><?= e($j['job_type']) ?></td>
              <td><a href="<?= url('company/applicants.php?id=' . $j['id']) ?>"><?= (int)$j['applicants'] ?> →</a></td>
              <td><span class="badge badge-<?= e($j['status']) ?>"><?= e($j['status']) ?></span></td>
              <td><?= e(time_ago($j['created_at'])) ?></td>
              <td style="white-space:nowrap">
                <a class="btn btn-ghost btn-sm" href="<?= url('jobs/view.php?id=' . $j['id']) ?>">View</a>
                <a class="btn btn-ghost btn-sm" href="<?= url('company/edit_job.php?id=' . $j['id']) ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
