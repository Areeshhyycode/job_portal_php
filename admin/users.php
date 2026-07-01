<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$pageTitle = 'Manage users';
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $userId = (int)($_POST['user_id'] ?? 0);
    if (($_POST['do'] ?? '') === 'delete') {
        if ($userId === (int)$me['id']) {
            flash('error', 'You cannot delete your own admin account.');
        } else {
            db()->prepare('DELETE FROM users WHERE id=?')->execute([$userId]);
            flash('success', 'User deleted (their jobs & applications were removed too).');
        }
    }
    redirect('admin/users.php' . (!empty($_POST['filter']) ? '?filter=' . urlencode($_POST['filter']) : ''));
}

$filter = $_GET['filter'] ?? 'all';
$where = in_array($filter, ['seeker','company','admin'], true) ? "WHERE role='$filter'" : '';
$users = db()->query(
    "SELECT u.*,
            (SELECT COUNT(*) FROM jobs j WHERE j.company_id=u.id) AS jobs_count,
            (SELECT COUNT(*) FROM applications a WHERE a.seeker_id=u.id) AS apps_count
     FROM users u $where ORDER BY u.created_at DESC"
)->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-head"><h2>Manage users</h2></div>
    <div class="pill-tabs">
      <?php foreach (['all','seeker','company','admin'] as $t): ?>
        <a class="<?= $filter===$t?'active':'' ?>" href="<?= url('admin/users.php?filter=' . $t) ?>"><?= ucfirst($t) ?><?= $t==='all'?'':'s' ?></a>
      <?php endforeach; ?>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Activity</th><th>Joined</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($users as $usr): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <span class="avatar" style="width:34px;height:34px;font-size:.8rem;background:<?= avatar_color($usr['email']) ?>"><?= e(initials($usr['company_name'] ?: $usr['name'])) ?></span>
                <strong><?= e($usr['company_name'] ?: $usr['name']) ?></strong>
              </div>
            </td>
            <td><?= e($usr['email']) ?></td>
            <td><span class="tag gray"><?= e($usr['role']) ?></span></td>
            <td class="muted">
              <?php if ($usr['role']==='company'): ?><?= (int)$usr['jobs_count'] ?> jobs
              <?php elseif ($usr['role']==='seeker'): ?><?= (int)$usr['apps_count'] ?> applications
              <?php else: ?>—<?php endif; ?>
            </td>
            <td><?= e(date('M j, Y', strtotime($usr['created_at']))) ?></td>
            <td>
              <?php if ((int)$usr['id'] === (int)$me['id']): ?>
                <span class="muted">You</span>
              <?php else: ?>
                <form method="post" onsubmit="return confirm('Delete <?= e(addslashes($usr['name'])) ?>? This removes all their data.')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="filter" value="<?= e($filter) ?>">
                  <input type="hidden" name="user_id" value="<?= $usr['id'] ?>">
                  <input type="hidden" name="do" value="delete">
                  <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
