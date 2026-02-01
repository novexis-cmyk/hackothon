<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

if (current_user()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);

    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $u = auth_find_user_by_email($email);
    if (!$u || !password_verify($password, (string)$u['password_hash'])) {
        $errors[] = 'Incorrect email or password.';
    } elseif ((int)$u['email_verified'] !== 1) {
        $errors[] = 'Please verify your email before signing in.';
    } else {
        // store minimal session user
        $_SESSION['user'] = [
            'id' => (int)$u['id'],
            'employee_id' => (string)$u['employee_id'],
            'email' => (string)$u['email'],
            'role' => (string)$u['role'],
        ];
        flash_set('success', 'Welcome back!');
        redirect('dashboard.php');
    }
}

require __DIR__ . '/../_layout_top.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card surface-card">
      <div class="card-body">
        <h4 class="mb-3 surface-heading">Sign in</h4>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" class="vstack gap-3">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div>
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required>
          </div>

          <div>
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>

          <button class="btn btn-dark w-100 mt-1">Sign In</button>
          <div class="text-center mt-2">
            <a href="<?= e(base_url('auth/register.php')) ?>">Create account</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

