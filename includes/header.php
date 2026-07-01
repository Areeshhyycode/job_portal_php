<?php
require_once __DIR__ . '/functions.php';
$u = current_user();
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐝</text></svg>">
</head>
<body>
<nav class="nav">
  <div class="container nav-inner">
    <a class="brand" href="<?= url('index.php') ?>">
      <span class="logo">🐝</span> <?= APP_NAME ?>
    </a>
    <div class="nav-links">
      <a href="<?= url('jobs/index.php') ?>">Browse Jobs</a>
      <?php if ($u): ?>
        <?php if ($u['role'] === 'seeker'): ?>
          <a href="<?= url('user/applications.php') ?>">My Applications</a>
          <a href="<?= url('user/saved.php') ?>">Saved</a>
          <a href="<?= url('user/profile.php') ?>">Profile</a>
        <?php elseif ($u['role'] === 'company'): ?>
          <a href="<?= url('company/dashboard.php') ?>">Dashboard</a>
          <a href="<?= url('company/post_job.php') ?>">Post a Job</a>
        <?php elseif ($u['role'] === 'admin'): ?>
          <a href="<?= url('admin/dashboard.php') ?>">Admin</a>
          <a href="<?= url('admin/jobs.php') ?>">Jobs</a>
          <a href="<?= url('admin/users.php') ?>">Users</a>
        <?php endif; ?>
        <div class="nav-user">
          <span class="avatar btn-sm" style="width:34px;height:34px;font-size:.8rem;background:<?= avatar_color($u['email']) ?>"><?= e(initials($u['company_name'] ?: $u['name'])) ?></span>
          <a class="btn btn-ghost btn-sm" href="<?= url('auth/logout.php') ?>">Logout</a>
        </div>
      <?php else: ?>
        <a href="<?= url('auth/login.php') ?>">Login</a>
        <a class="btn btn-primary btn-sm" href="<?= url('auth/register.php') ?>">Sign up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main>
<?php if ($m = flash('success')): ?><div class="container" style="margin-top:18px"><div class="alert alert-success"><?= e($m) ?></div></div><?php endif; ?>
<?php if ($m = flash('error')): ?><div class="container" style="margin-top:18px"><div class="alert alert-error"><?= e($m) ?></div></div><?php endif; ?>
<?php if ($m = flash('info')): ?><div class="container" style="margin-top:18px"><div class="alert alert-info"><?= e($m) ?></div></div><?php endif; ?>
