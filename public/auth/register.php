<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

if (current_user()) {
    redirect('dashboard.php');
}

$errors = [];
$verificationLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);

    $employeeId = trim((string)($_POST['employee_id'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = (string)($_POST['role'] ?? 'EMPLOYEE');

    if ($employeeId === '') $errors[] = 'Employee ID is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!auth_password_valid($password)) $errors[] = 'Password must be at least 8 chars and include upper, lower, and a number.';

    if (auth_find_user_by_employee_id($employeeId)) $errors[] = 'Employee ID already registered.';
    if (auth_find_user_by_email($email)) $errors[] = 'Email already registered.';

    if (!$errors) {
        try {
            $u = auth_create_user($employeeId, $email, $password, $role);
            $verificationLink = base_url('auth/verify.php?token=' . urlencode((string)$u['email_verify_token']));
            flash_set('success', 'Account created. Please verify your email to sign in.');
        } catch (Throwable $e) {
            $errors[] = 'Could not create account: ' . $e->getMessage();
        }
    }
}

require __DIR__ . '/../_layout_top.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card surface-card">
      <div class="card-body">
        <h4 class="mb-3 surface-heading">Create account</h4>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($verificationLink): ?>
          <div class="alert alert-warning">
            Email sending is not configured yet. Click to verify:
            <div class="mt-2">
              <a href="<?= e($verificationLink) ?>"><?= e($verificationLink) ?></a>
            </div>
          </div>
        <?php endif; ?>

        <form method="post" class="vstack gap-3">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div>
            <label class="form-label">Employee ID</label>
            <input class="form-control" name="employee_id" required>
          </div>

          <div>
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required>
          </div>

          <div>
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
            <div class="form-text">Min 8 chars with upper, lower, number.</div>
          </div>

          <div>
            <label class="form-label">Role</label>
            <select class="form-select" name="role">
              <option value="EMPLOYEE">Employee</option>
              <option value="HR">Admin / HR</option>
            </select>
          </div>

          <button class="btn btn-dark w-100">Sign Up</button>
          <div class="text-center">
            <a href="<?= e(base_url('auth/login.php')) ?>">Already have an account? Sign in</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

