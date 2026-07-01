<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('company');
$pageTitle = 'Post a job';
$u = current_user();
$errors = [];

$types = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Remote'];
$categories = ['Software', 'Design', 'Marketing', 'Sales', 'Finance', 'Support'];
$f = ['title'=>'','location'=>'','job_type'=>'Full-time','category'=>'Software','salary_min'=>'','salary_max'=>'','description'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($f as $k => $_) $f[$k] = trim($_POST[$k] ?? '');

    if ($f['title'] === '')       $errors[] = 'Title is required.';
    if ($f['location'] === '')    $errors[] = 'Location is required.';
    if ($f['description'] === '') $errors[] = 'Description is required.';
    if (!in_array($f['job_type'], $types, true))     $errors[] = 'Invalid job type.';
    if (!in_array($f['category'], $categories, true)) $errors[] = 'Invalid category.';
    $min = $f['salary_min'] !== '' ? (int)$f['salary_min'] : null;
    $max = $f['salary_max'] !== '' ? (int)$f['salary_max'] : null;
    if ($min !== null && $max !== null && $min > $max) $errors[] = 'Minimum salary cannot exceed maximum.';

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO jobs (company_id, title, description, location, job_type, category, salary_min, salary_max, status)
             VALUES (?,?,?,?,?,?,?,?,\'pending\')'
        );
        $stmt->execute([$u['id'], $f['title'], $f['description'], $f['location'], $f['job_type'], $f['category'], $min, $max]);
        flash('success', 'Job posted! It will be visible once an admin approves it.');
        redirect('company/dashboard.php');
    }
}
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <div class="form-wrap form-wide">
    <h2>Post a new job</h2>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <div class="card card-pad">
      <form method="post">
        <?= csrf_field() ?>
        <div class="field-group"><label>Job title</label><input class="input" name="title" value="<?= e($f['title']) ?>" placeholder="e.g. Senior PHP Developer" required></div>
        <div class="row">
          <div class="field-group"><label>Location</label><input class="input" name="location" value="<?= e($f['location']) ?>" placeholder="City / Remote" required></div>
          <div class="field-group"><label>Job type</label>
            <select class="input" name="job_type"><?php foreach ($types as $t): ?><option <?= $f['job_type']===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select>
          </div>
        </div>
        <div class="row">
          <div class="field-group"><label>Category</label>
            <select class="input" name="category"><?php foreach ($categories as $c): ?><option <?= $f['category']===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select>
          </div>
          <div class="field-group"><label>Salary min (PKR)</label><input class="input" type="number" name="salary_min" value="<?= e($f['salary_min']) ?>" placeholder="optional"></div>
          <div class="field-group"><label>Salary max (PKR)</label><input class="input" type="number" name="salary_max" value="<?= e($f['salary_max']) ?>" placeholder="optional"></div>
        </div>
        <div class="field-group"><label>Description</label><textarea name="description" style="min-height:180px" placeholder="Responsibilities, requirements, perks…" required><?= e($f['description']) ?></textarea></div>
        <button class="btn btn-primary" type="submit">Publish job</button>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
