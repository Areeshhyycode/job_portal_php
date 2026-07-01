<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_logged_in()) redirect('index.php');
$pageTitle = 'Sign up';

$errors = [];
$old = ['name' => '', 'email' => '', 'role' => 'seeker', 'company_name' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old['name']         = trim($_POST['name'] ?? '');
    $old['email']        = trim($_POST['email'] ?? '');
    $old['role']         = $_POST['role'] ?? 'seeker';
    $old['company_name'] = trim($_POST['company_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    if (!in_array($old['role'], ['seeker', 'company'], true)) $old['role'] = 'seeker';
    if ($old['name'] === '')                       $errors[] = 'Name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6)                     $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)                    $errors[] = 'Passwords do not match.';
    if ($old['role'] === 'company' && $old['company_name'] === '') $errors[] = 'Company name is required.';

    if (!$errors) {
        $exists = db()->prepare('SELECT id FROM users WHERE email = ?');
        $exists->execute([$old['email']]);
        if ($exists->fetch()) {
            $errors[] = 'That email is already registered.';
        } else {
            $stmt = db()->prepare(
                'INSERT INTO users (name, email, password, role, company_name)
                 VALUES (?,?,?,?,?)'
            );
            $stmt->execute([
                $old['name'],
                $old['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $old['role'],
                $old['role'] === 'company' ? $old['company_name'] : null,
            ]);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            flash('success', 'Welcome to ' . APP_NAME . ', ' . $old['name'] . '!');
            redirect($old['role'] === 'company' ? 'company/dashboard.php' : 'index.php');
        }
    }
}
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <div class="form-wrap">
    <div class="card card-pad">
      <h2 class="mt-0">Create your account</h2>
      <p class="muted" style="margin-top:-6px">Join <?= APP_NAME ?> as a job seeker or a company.</p>

      <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field-group">
          <label>I am a…</label>
          <select class="input" name="role" id="role" onchange="document.getElementById('cn').style.display=this.value==='company'?'block':'none'">
            <option value="seeker"  <?= $old['role']==='seeker'?'selected':'' ?>>Job Seeker — looking for work</option>
            <option value="company" <?= $old['role']==='company'?'selected':'' ?>>Company — hiring talent</option>
          </select>
        </div>
        <div class="field-group" id="cn" style="display:<?= $old['role']==='company'?'block':'none' ?>">
          <label>Company name</label>
          <input class="input" name="company_name" value="<?= e($old['company_name']) ?>" placeholder="Acme Corp">
        </div>
        <div class="field-group">
          <label>Full name</label>
          <input class="input" name="name" value="<?= e($old['name']) ?>" placeholder="Your name" required>
        </div>
        <div class="field-group">
          <label>Email</label>
          <input class="input" type="email" name="email" value="<?= e($old['email']) ?>" placeholder="you@example.com" required>
        </div>
        <div class="row">
          <div class="field-group">
            <label>Password</label>
            <input class="input" type="password" name="password" placeholder="min 6 chars" required>
          </div>
          <div class="field-group">
            <label>Confirm</label>
            <input class="input" type="password" name="confirm" placeholder="repeat" required>
          </div>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Create account</button>
      </form>
      <p class="text-center muted" style="margin-bottom:0">Already have an account? <a href="<?= url('auth/login.php') ?>">Log in</a></p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
