<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_logged_in()) redirect('index.php');
$pageTitle = 'Login';

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        flash('success', 'Welcome back, ' . ($user['company_name'] ?: $user['name']) . '!');
        $dest = match ($user['role']) {
            'admin'   => 'admin/dashboard.php',
            'company' => 'company/dashboard.php',
            default   => 'index.php',
        };
        redirect($dest);
    }
    $error = 'Invalid email or password.';
}
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <div class="form-wrap">
    <div class="card card-pad">
      <h2 class="mt-0">Welcome back</h2>
      <p class="muted" style="margin-top:-6px">Log in to your <?= APP_NAME ?> account.</p>

      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

      <form method="post">
        <?= csrf_field() ?>
        <div class="field-group">
          <label>Email</label>
          <input class="input" type="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" required autofocus>
        </div>
        <div class="field-group">
          <label>Password</label>
          <input class="input" type="password" name="password" placeholder="Your password" required>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Log in</button>
      </form>
      <p class="text-center muted" style="margin-bottom:0">New here? <a href="<?= url('auth/register.php') ?>">Create an account</a></p>
    </div>
    <div class="card card-pad" style="margin-top:16px;font-size:.85rem">
      <strong>Demo accounts</strong> (password: <code>password123</code>)
      <div class="divider" style="margin:10px 0"></div>
      <div class="kv"><span class="k">Admin</span><span>admin@jobportal.test</span></div>
      <div class="kv"><span class="k">Company</span><span>hr@acme.test</span></div>
      <div class="kv"><span class="k">Seeker</span><span>ayesha@seeker.test</span></div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
