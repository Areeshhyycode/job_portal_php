<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$pageTitle = 'Admin dashboard';

$stats = [
    'users'    => db()->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'seekers'  => db()->query("SELECT COUNT(*) FROM users WHERE role='seeker'")->fetchColumn(),
    'companies'=> db()->query("SELECT COUNT(*) FROM users WHERE role='company'")->fetchColumn(),
    'jobs'     => db()->query("SELECT COUNT(*) FROM jobs")->fetchColumn(),
    'pending'  => db()->query("SELECT COUNT(*) FROM jobs WHERE status='pending'")->fetchColumn(),
    'apps'     => db()->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
];
$pendingJobs = db()->query(
    "SELECT j.*, u.company_name FROM jobs j JOIN users u ON u.id=j.company_id
     WHERE j.status='pending' ORDER BY j.created_at DESC LIMIT 5"
)->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-head"><h2>Admin dashboard</h2></div>
    <div class="grid grid-4" style="margin-bottom:16px">
      <div class="stat"><div class="n"><?= (int)$stats['users'] ?></div><div class="l">Total users</div></div>
      <div class="stat"><div class="n"><?= (int)$stats['seekers'] ?></div><div class="l">Job seekers</div></div>
      <div class="stat"><div class="n"><?= (int)$stats['companies'] ?></div><div class="l">Companies</div></div>
      <div class="stat"><div class="n"><?= (int)$stats['jobs'] ?></div><div class="l">Jobs</div></div>
    </div>
    <div class="grid grid-2" style="margin-bottom:26px">
      <div class="stat" style="border-color:#f4d9ac"><div class="n" style="color:var(--warn)"><?= (int)$stats['pending'] ?></div><div class="l">Jobs awaiting approval</div></div>
      <div class="stat"><div class="n"><?= (int)$stats['apps'] ?></div><div class="l">Applications submitted</div></div>
    </div>

    <div class="section-head"><h2 style="font-size:1.2rem">Pending approvals</h2><a href="<?= url('admin/jobs.php') ?>">Manage all jobs →</a></div>
    <?php if (!$pendingJobs): ?>
      <div class="empty"><div class="ico">✅</div>Nothing pending. All caught up!</div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Title</th><th>Company</th><th>Posted</th><th>Action</th></tr></thead>
          <tbody>
          <?php foreach ($pendingJobs as $j): ?>
            <tr>
              <td><a href="<?= url('jobs/view.php?id=' . $j['id']) ?>"><?= e($j['title']) ?></a></td>
              <td><?= e($j['company_name']) ?></td>
              <td><?= e(time_ago($j['created_at'])) ?></td>
              <td>
                <form method="post" action="<?= url('admin/jobs.php') ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                  <input type="hidden" name="do" value="approve">
                  <button class="btn btn-success btn-sm" type="submit">Approve</button>
                </form>
                <form method="post" action="<?= url('admin/jobs.php') ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                  <input type="hidden" name="do" value="reject">
                  <button class="btn btn-danger btn-sm" type="submit">Reject</button>
                </form>
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
