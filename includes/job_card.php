<?php
/** Expects $job (array with joined company_name). */
$companyName = $job['company_name'] ?: $job['company_display'] ?? 'Company';
?>
<div class="job-card">
  <div class="job-top">
    <div class="avatar" style="background:<?= avatar_color($companyName) ?>"><?= e(initials($companyName)) ?></div>
    <div>
      <h3 class="job-title"><a href="<?= url('jobs/view.php?id=' . $job['id']) ?>"><?= e($job['title']) ?></a></h3>
      <div class="job-company"><?= e($companyName) ?> · <?= e($job['location']) ?></div>
    </div>
  </div>
  <div class="job-meta">
    <span class="tag"><?= e($job['job_type']) ?></span>
    <span class="tag gray"><?= e($job['category']) ?></span>
  </div>
  <div class="job-foot">
    <span class="salary"><?= e(salary_range($job['salary_min'], $job['salary_max'])) ?></span>
    <span class="muted" style="font-size:.82rem"><?= e(time_ago($job['created_at'])) ?></span>
  </div>
</div>
