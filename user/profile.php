<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('seeker');
$pageTitle = 'My profile';
$u = current_user();
$errors = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name     = trim($_POST['name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $resumePath = $u['resume_path'];

    if ($name === '') $errors[] = 'Name is required.';

    if (!empty($_FILES['resume']['name'])) {
        $f = $_FILES['resume'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Resume upload failed.';
        } elseif ($f['size'] > MAX_RESUME_BYTES) {
            $errors[] = 'Resume must be under 3 MB.';
        } else {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
                $errors[] = 'Resume must be a PDF, DOC or DOCX file.';
            } else {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
                $fname = 'resume_' . $u['id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $fname)) {
                    $resumePath = 'uploads/resumes/' . $fname;
                } else {
                    $errors[] = 'Could not save resume file.';
                }
            }
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE users SET name=?, phone=?, location=?, headline=?, bio=?, resume_path=? WHERE id=?');
        $stmt->execute([$name, $phone, $location, $headline, $bio, $resumePath, $u['id']]);
        flash('success', 'Profile updated.');
        redirect('user/profile.php');
    }
}
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <div class="form-wrap form-wide">
    <h2>My profile</h2>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

    <div class="card card-pad">
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row">
          <div class="field-group"><label>Full name</label><input class="input" name="name" value="<?= e($u['name']) ?>" required></div>
          <div class="field-group"><label>Email</label><input class="input" value="<?= e($u['email']) ?>" disabled></div>
        </div>
        <div class="row">
          <div class="field-group"><label>Phone</label><input class="input" name="phone" value="<?= e($u['phone']) ?>" placeholder="03xx-xxxxxxx"></div>
          <div class="field-group"><label>Location</label><input class="input" name="location" value="<?= e($u['location']) ?>" placeholder="City"></div>
        </div>
        <div class="field-group"><label>Headline</label><input class="input" name="headline" value="<?= e($u['headline']) ?>" placeholder="e.g. Frontend Developer"></div>
        <div class="field-group"><label>Bio</label><textarea name="bio" placeholder="A short summary about you…"><?= e($u['bio']) ?></textarea></div>
        <div class="field-group">
          <label>Resume</label>
          <?php if ($u['resume_path']): ?>
            <p class="hint">Current: <a href="<?= url($u['resume_path']) ?>" target="_blank">view resume ↗</a></p>
          <?php endif; ?>
          <input class="input" type="file" name="resume" accept=".pdf,.doc,.docx">
          <p class="hint">PDF, DOC or DOCX, max 3 MB. Used automatically when you apply.</p>
        </div>
        <button class="btn btn-primary" type="submit">Save changes</button>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
