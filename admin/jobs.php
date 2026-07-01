<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$pageTitle = 'Manage jobs';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $jobId = (int)($_POST['job_id'] ?? 0);
    $do    = $_POST['do'] ?? '';
    if ($do === 'approve') {
        db()->prepare("UPDATE jobs SET status='approved' WHERE id=?")->execute([$jobId]);
        // Notify the posting company.
        require_once __DIR__ . '/../includes/mailer.php';
        $info = db()->prepare('SELECT j.title, u.email FROM jobs j JOIN users u ON u.id=j.company_id WHERE j.id=?');
        $info->execute([$jobId]);
        if ($row = $info->fetch()) {
            send_mail($row['email'], 'Your job is now live', "Good news! \"{$row['title']}\" has been approved and is now visible to job seekers on " . APP_NAME . '.');
        }
        flash('success', 'Job approved and now live.');
    } elseif ($do === 'reject') {
        db()->prepare("UPDATE jobs SET status='rejected' WHERE id=?")->execute([$jobId]);
        flash('info', 'Job rejected.');
    } elseif ($do === 'delete') {
        db()->prepare('DELETE FROM jobs WHERE id=?')->execute([$jobId]);
        flash('success', 'Job deleted.');
    }
    redirect('admin/jobs.php' . (!empty($_POST['filter']) ? '?filter=' . urlencode($_POST['filter']) : ''));
}

$filter = $_GET['filter'] ?? 'all';
$where = in_array($filter, ['pending','approved','rejected'], true) ? "WHERE j.status='$filter'" : '';
$jobs = db()->query(
    "SELECT j.*, u.company_name,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS applicants
     FROM jobs j JOIN users u ON u.id=j.company_id
     $where ORDER BY j.created_at DESC"
)->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-head"><h2>Manage jobs</h2></div>
    <div class="pill-tabs">
      <?php foreach (['all','pending','approved','rejected'] as $t): ?>
        <a class="<?= $filter===$t?'active':'' ?>" href="<?= url('admin/jobs.php?filter=' . $t) ?>"><?= ucfirst($t) ?></a>
      <?php endforeach; ?>
    </div>
    <?php if (!$jobs): ?>
      <div class="empty"><div class="ico">🗂️</div>No jobs in this view.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Title</th><th>Company</th><th>Applicants</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($jobs as $j): ?>
            <tr>
              <td><a href="<?= url('jobs/view.php?id=' . $j['id']) ?>"><?= e($j['title']) ?></a><br><span class="muted"><?= e($j['location']) ?></span></td>
              <td><?= e($j['company_name']) ?></td>
              <td><?= (int)$j['applicants'] ?></td>
              <td><span class="badge badge-<?= e($j['status']) ?>"><?= e($j['status']) ?></span></td>
              <td style="white-space:nowrap">
                <?php if ($j['status'] !== 'approved'): ?>
                  <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="filter" value="<?= e($filter) ?>"><input type="hidden" name="job_id" value="<?= $j['id'] ?>"><input type="hidden" name="do" value="approve"><button class="btn btn-success btn-sm">Approve</button></form>
                <?php endif; ?>
                <?php if ($j['status'] !== 'rejected'): ?>
                  <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="filter" value="<?= e($filter) ?>"><input type="hidden" name="job_id" value="<?= $j['id'] ?>"><input type="hidden" name="do" value="reject"><button class="btn btn-ghost btn-sm">Reject</button></form>
                <?php endif; ?>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete this job?')"><?= csrf_field() ?><input type="hidden" name="filter" value="<?= e($filter) ?>"><input type="hidden" name="job_id" value="<?= $j['id'] ?>"><input type="hidden" name="do" value="delete"><button class="btn btn-danger btn-sm">Delete</button></form>
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
