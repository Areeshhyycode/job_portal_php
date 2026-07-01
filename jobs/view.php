<?php
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare(
    "SELECT j.*, u.company_name, u.about, u.website, u.email AS company_email
     FROM jobs j JOIN users u ON u.id = j.company_id
     WHERE j.id = ?"
);
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job || ($job['status'] !== 'approved' && user_role() !== 'admin' && ($job['company_id'] ?? 0) != ($_SESSION['user_id'] ?? -1))) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/../includes/header.php';
    echo '<div class="container"><div class="empty"><div class="ico">🚫</div>Job not found or not available.</div></div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}
$pageTitle = $job['title'];

$u = current_user();
$hasApplied = false;
$hasSaved = false;
if ($u && $u['role'] === 'seeker') {
    $a = db()->prepare('SELECT 1 FROM applications WHERE job_id=? AND seeker_id=?');
    $a->execute([$id, $u['id']]);
    $hasApplied = (bool)$a->fetchColumn();
    $s = db()->prepare('SELECT 1 FROM saved_jobs WHERE job_id=? AND seeker_id=?');
    $s->execute([$id, $u['id']]);
    $hasSaved = (bool)$s->fetchColumn();
}
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <a class="muted" href="<?= url('jobs/index.php') ?>">← Back to jobs</a>
    <div class="detail-grid" style="margin-top:16px">
      <div>
        <div class="card card-pad">
          <div class="job-top" style="align-items:center">
            <div class="avatar" style="width:60px;height:60px;font-size:1.3rem;background:<?= avatar_color($job['company_name']) ?>"><?= e(initials($job['company_name'])) ?></div>
            <div>
              <h1 style="margin:0;font-size:1.7rem"><?= e($job['title']) ?></h1>
              <div class="muted"><?= e($job['company_name']) ?> · <?= e($job['location']) ?></div>
            </div>
          </div>
          <div class="job-meta" style="margin-top:16px">
            <span class="tag"><?= e($job['job_type']) ?></span>
            <span class="tag gray"><?= e($job['category']) ?></span>
            <span class="tag gray">Posted <?= e(time_ago($job['created_at'])) ?></span>
            <?php if ($job['status'] !== 'approved'): ?><span class="badge badge-<?= e($job['status']) ?>"><?= e($job['status']) ?></span><?php endif; ?>
          </div>
          <div class="divider"></div>
          <h3>Job description</h3>
          <div class="prose"><?= e($job['description']) ?></div>

          <?php if ($job['about']): ?>
            <div class="divider"></div>
            <h3>About <?= e($job['company_name']) ?></h3>
            <div class="prose"><?= e($job['about']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <aside>
        <div class="side-box">
          <div class="kv"><span class="k">Salary</span><span><?= e(salary_range($job['salary_min'], $job['salary_max'])) ?></span></div>
          <div class="kv"><span class="k">Job type</span><span><?= e($job['job_type']) ?></span></div>
          <div class="kv"><span class="k">Category</span><span><?= e($job['category']) ?></span></div>
          <div class="kv"><span class="k">Location</span><span><?= e($job['location']) ?></span></div>
          <?php if ($job['website']): ?><div class="kv"><span class="k">Website</span><a href="<?= e($job['website']) ?>" target="_blank" rel="noopener">Visit ↗</a></div><?php endif; ?>

          <div style="margin-top:16px;display:flex;flex-direction:column;gap:10px">
            <?php if (!$u): ?>
              <a class="btn btn-primary btn-block" href="<?= url('auth/login.php') ?>">Log in to apply</a>
            <?php elseif ($u['role'] === 'seeker'): ?>
              <?php if ($hasApplied): ?>
                <button class="btn btn-success btn-block" disabled>✓ Applied</button>
              <?php else: ?>
                <a class="btn btn-primary btn-block" href="<?= url('jobs/apply.php?id=' . $job['id']) ?>">Apply now</a>
              <?php endif; ?>
              <form method="post" action="<?= url('jobs/save.php') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                <input type="hidden" name="action" value="<?= $hasSaved ? 'unsave' : 'save' ?>">
                <button class="btn btn-ghost btn-block" type="submit"><?= $hasSaved ? '★ Saved — remove' : '☆ Save job' ?></button>
              </form>
            <?php elseif ($u['role'] === 'company' && $job['company_id'] == $u['id']): ?>
              <a class="btn btn-ghost btn-block" href="<?= url('company/edit_job.php?id=' . $job['id']) ?>">Edit this job</a>
              <a class="btn btn-primary btn-block" href="<?= url('company/applicants.php?id=' . $job['id']) ?>">View applicants</a>
            <?php else: ?>
              <p class="muted text-center mb-0" style="font-size:.9rem">Only job seekers can apply.</p>
            <?php endif; ?>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
