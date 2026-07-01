<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('seeker');

$id = (int)($_GET['id'] ?? $_POST['job_id'] ?? 0);
$u  = current_user();

$stmt = db()->prepare("SELECT j.*, u.company_name FROM jobs j JOIN users u ON u.id=j.company_id WHERE j.id=? AND j.status='approved'");
$stmt->execute([$id]);
$job = $stmt->fetch();
if (!$job) { flash('error', 'Job not available.'); redirect('jobs/index.php'); }

// Already applied?
$chk = db()->prepare('SELECT 1 FROM applications WHERE job_id=? AND seeker_id=?');
$chk->execute([$id, $u['id']]);
if ($chk->fetchColumn()) { flash('info', 'You already applied to this job.'); redirect('jobs/view.php?id=' . $id); }

$errors = [];
$pageTitle = 'Apply · ' . $job['title'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $cover = trim($_POST['cover_letter'] ?? '');
    $resumePath = $u['resume_path']; // default to profile resume

    // Optional fresh resume upload
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

    if (!$resumePath) $errors[] = 'Please upload a resume (or add one on your profile first).';

    if (!$errors) {
        $ins = db()->prepare('INSERT INTO applications (job_id, seeker_id, cover_letter, resume_path) VALUES (?,?,?,?)');
        $ins->execute([$id, $u['id'], $cover ?: null, $resumePath]);

        // Notify the employer by email.
        require_once __DIR__ . '/../includes/mailer.php';
        $co = db()->prepare('SELECT email FROM users WHERE id=?');
        $co->execute([$job['company_id']]);
        if ($email = $co->fetchColumn()) {
            send_mail(
                $email,
                'New application for ' . $job['title'],
                "{$u['name']} ({$u['email']}) just applied for \"{$job['title']}\".\n\n" .
                "Log in to your dashboard to review the applicant."
            );
        }

        flash('success', 'Application submitted! The employer will review it soon.');
        redirect('user/applications.php');
    }
}
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <div class="form-wrap form-wide">
    <div class="card card-pad">
      <h2 class="mt-0">Apply for <?= e($job['title']) ?></h2>
      <p class="muted" style="margin-top:-6px"><?= e($job['company_name']) ?> · <?= e($job['location']) ?></p>

      <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="job_id" value="<?= $id ?>">

        <div class="field-group">
          <label>Cover letter <span class="muted">(optional)</span></label>
          <textarea name="cover_letter" placeholder="Tell the employer why you're a great fit…"><?= e($_POST['cover_letter'] ?? '') ?></textarea>
        </div>

        <div class="field-group">
          <label>Resume</label>
          <?php if ($u['resume_path']): ?>
            <p class="hint">On file: <a href="<?= url($u['resume_path']) ?>" target="_blank">current resume</a>. Upload below to replace for this application.</p>
          <?php endif; ?>
          <input class="input" type="file" name="resume" accept=".pdf,.doc,.docx">
          <p class="hint">PDF, DOC or DOCX, max 3 MB.</p>
        </div>

        <div style="display:flex;gap:10px">
          <button class="btn btn-primary" type="submit">Submit application</button>
          <a class="btn btn-ghost" href="<?= url('jobs/view.php?id=' . $id) ?>">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
