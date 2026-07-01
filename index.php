<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Find your next role';

// Featured = latest approved jobs
$jobs = db()->query(
    "SELECT j.*, u.company_name
     FROM jobs j JOIN users u ON u.id = j.company_id
     WHERE j.status = 'approved'
     ORDER BY j.created_at DESC LIMIT 6"
)->fetchAll();

$stats = [
    'jobs'      => db()->query("SELECT COUNT(*) FROM jobs WHERE status='approved'")->fetchColumn(),
    'companies' => db()->query("SELECT COUNT(*) FROM users WHERE role='company'")->fetchColumn(),
    'seekers'   => db()->query("SELECT COUNT(*) FROM users WHERE role='seeker'")->fetchColumn(),
];

$categories = ['Software', 'Design', 'Marketing', 'Sales', 'Finance', 'Support'];

require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="container">
    <h1>Find a job that <span>loves you back</span></h1>
    <p>Browse thousands of openings from top companies. Apply in one click, save the ones you like, and track everything in one place.</p>
    <form class="searchbar" action="<?= url('jobs/index.php') ?>" method="get">
      <div class="field">
        🔍 <input type="text" name="q" placeholder="Job title or keyword">
      </div>
      <div class="field">
        📍 <input type="text" name="location" placeholder="Location">
      </div>
      <div class="field">
        <select name="category">
          <option value="">All categories</option>
          <?php foreach ($categories as $c): ?><option><?= $c ?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary" type="submit">Search</button>
    </form>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="grid grid-3">
      <div class="stat"><div class="n"><?= (int)$stats['jobs'] ?></div><div class="l">Open positions</div></div>
      <div class="stat"><div class="n"><?= (int)$stats['companies'] ?></div><div class="l">Hiring companies</div></div>
      <div class="stat"><div class="n"><?= (int)$stats['seekers'] ?></div><div class="l">Job seekers</div></div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:10px">
  <div class="container">
    <div class="section-head">
      <h2>Latest openings</h2>
      <a class="btn btn-ghost btn-sm" href="<?= url('jobs/index.php') ?>">View all jobs →</a>
    </div>
    <?php if (!$jobs): ?>
      <div class="empty"><div class="ico">🗂️</div>No jobs posted yet. Check back soon!</div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($jobs as $job) { include __DIR__ . '/includes/job_card.php'; } ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
