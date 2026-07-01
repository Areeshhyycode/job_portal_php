<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('company');
$u = current_user();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM jobs WHERE id=? AND company_id=?');
$stmt->execute([$id, $u['id']]);
$job = $stmt->fetch();
if (!$job) { flash('error', 'Job not found.'); redirect('company/dashboard.php'); }

$pageTitle = 'Edit · ' . $job['title'];
$errors = [];
$types = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Remote'];
$categories = ['Software', 'Design', 'Marketing', 'Sales', 'Finance', 'Support'];
$f = [
    'title'=>$job['title'],'location'=>$job['location'],'job_type'=>$job['job_type'],
    'category'=>$job['category'],'salary_min'=>$job['salary_min'],'salary_max'=>$job['salary_max'],
    'description'=>$job['description'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'update') {
    verify_csrf();
    foreach (['title','location','job_type','category','salary_min','salary_max','description'] as $k) $f[$k] = trim($_POST[$k] ?? '');

    if ($f['title'] === '')       $errors[] = 'Title is required.';
    if ($f['location'] === '')    $errors[] = 'Location is required.';
    if ($f['description'] === '') $errors[] = 'Description is required.';
    $min = $f['salary_min'] !== '' ? (int)$f['salary_min'] : null;
    $max = $f['salary_max'] !== '' ? (int)$f['salary_max'] : null;
    if ($min !== null && $max !== null && $min > $max) $errors[] = 'Minimum salary cannot exceed maximum.';

    if (!$errors) {
        // Editing an approved job sends it back for re-approval.
        $newStatus = $job['status'] === 'rejected' ? 'pending' : $job['status'];
        $up = db()->prepare('UPDATE jobs SET title=?,description=?,location=?,job_type=?,category=?,salary_min=?,salary_max=? WHERE id=? AND company_id=?');
        $up->execute([$f['title'],$f['description'],$f['location'],$f['job_type'],$f['category'],$min,$max,$id,$u['id']]);
        flash('success', 'Job updated.');
        redirect('company/dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    verify_csrf();
    db()->prepare('DELETE FROM jobs WHERE id=? AND company_id=?')->execute([$id, $u['id']]);
    flash('success', 'Job deleted.');
    redirect('company/dashboard.php');
}
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <div class="form-wrap form-wide">
    <div class="section-head"><h2 class="mb-0">Edit job</h2><span class="badge badge-<?= e($job['status']) ?>"><?= e($job['status']) ?></span></div>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <div class="card card-pad">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="update">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="field-group"><label>Job title</label><input class="input" name="title" value="<?= e($f['title']) ?>" required></div>
        <div class="row">
          <div class="field-group"><label>Location</label><input class="input" name="location" value="<?= e($f['location']) ?>" required></div>
          <div class="field-group"><label>Job type</label>
            <select class="input" name="job_type"><?php foreach ($types as $t): ?><option <?= $f['job_type']===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select>
          </div>
        </div>
        <div class="row">
          <div class="field-group"><label>Category</label>
            <select class="input" name="category"><?php foreach ($categories as $c): ?><option <?= $f['category']===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select>
          </div>
          <div class="field-group"><label>Salary min</label><input class="input" type="number" name="salary_min" value="<?= e($f['salary_min']) ?>"></div>
          <div class="field-group"><label>Salary max</label><input class="input" type="number" name="salary_max" value="<?= e($f['salary_max']) ?>"></div>
        </div>
        <div class="field-group"><label>Description</label><textarea name="description" style="min-height:180px" required><?= e($f['description']) ?></textarea></div>
        <div style="display:flex;gap:10px">
          <button class="btn btn-primary" type="submit">Save changes</button>
          <a class="btn btn-ghost" href="<?= url('company/dashboard.php') ?>">Cancel</a>
        </div>
      </form>
      <div class="divider"></div>
      <form method="post" onsubmit="return confirm('Delete this job permanently? Applications will also be removed.')">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="delete">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button class="btn btn-danger btn-sm" type="submit">Delete job</button>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
