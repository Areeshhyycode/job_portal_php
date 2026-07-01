<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('company');
$u = current_user();
$id = (int)($_GET['id'] ?? 0);

$jstmt = db()->prepare('SELECT * FROM jobs WHERE id=? AND company_id=?');
$jstmt->execute([$id, $u['id']]);
$job = $jstmt->fetch();
if (!$job) { flash('error', 'Job not found.'); redirect('company/dashboard.php'); }
$pageTitle = 'Applicants · ' . $job['title'];

// Update applicant status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $appId  = (int)($_POST['app_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['applied', 'shortlisted', 'rejected'], true)) {
        // Ensure this application belongs to this company's job
        $chk = db()->prepare('SELECT a.id FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.id=? AND j.company_id=?');
        $chk->execute([$appId, $u['id']]);
        if ($chk->fetchColumn()) {
            db()->prepare('UPDATE applications SET status=? WHERE id=?')->execute([$status, $appId]);
            flash('success', 'Applicant status updated.');
        }
    }
    redirect('company/applicants.php?id=' . $id);
}

$stmt = db()->prepare(
    "SELECT a.*, u.name, u.email, u.phone, u.headline, u.location
     FROM applications a JOIN users u ON u.id=a.seeker_id
     WHERE a.job_id=? ORDER BY a.created_at DESC"
);
$stmt->execute([$id]);
$apps = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <a class="muted" href="<?= url('company/dashboard.php') ?>">← Dashboard</a>
    <div class="section-head" style="margin-top:10px">
      <h2 class="mb-0">Applicants — <?= e($job['title']) ?></h2>
      <span class="muted"><?= count($apps) ?> total</span>
    </div>

    <?php if (!$apps): ?>
      <div class="empty"><div class="ico">👥</div>No applicants yet.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Candidate</th><th>Contact</th><th>Resume</th><th>Applied</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
          <?php foreach ($apps as $a): ?>
            <tr>
              <td><strong><?= e($a['name']) ?></strong><br><span class="muted"><?= e($a['headline'] ?: $a['location']) ?></span></td>
              <td><?= e($a['email']) ?><br><span class="muted"><?= e($a['phone']) ?></span></td>
              <td><?php if ($a['resume_path']): ?><a class="btn btn-ghost btn-sm" href="<?= url($a['resume_path']) ?>" target="_blank">📄 View</a><?php else: ?><span class="muted">—</span><?php endif; ?></td>
              <td><?= e(time_ago($a['created_at'])) ?></td>
              <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
              <td>
                <form method="post" style="display:flex;gap:6px">
                  <?= csrf_field() ?>
                  <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                  <select class="input btn-sm" name="status" style="width:auto;padding:6px 8px">
                    <?php foreach (['applied','shortlisted','rejected'] as $s): ?>
                      <option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-primary btn-sm" type="submit">Set</button>
                </form>
              </td>
            </tr>
            <?php if ($a['cover_letter']): ?>
              <tr><td colspan="6" class="muted" style="background:#fafbff"><strong>Cover letter:</strong> <?= e($a['cover_letter']) ?></td></tr>
            <?php endif; ?>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
