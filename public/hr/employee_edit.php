<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['HR']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'Missing employee id.');
    redirect('hr/employees.php');
}

$stmt = db()->prepare('
  SELECT u.id, u.employee_id, u.email, u.role, u.email_verified,
         p.full_name, p.phone, p.address, p.job_title, p.department
  FROM users u
  LEFT JOIN employee_profiles p ON p.user_id = u.id
  WHERE u.id = ?
  LIMIT 1
');
$stmt->execute([$id]);
$emp = $stmt->fetch();
if (!$emp) {
    flash_set('error', 'Employee not found.');
    redirect('hr/employees.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $jobTitle = trim((string)($_POST['job_title'] ?? ''));
    $department = trim((string)($_POST['department'] ?? ''));
    $role = strtoupper(trim((string)($_POST['role'] ?? $emp['role'])));

    if (!in_array($role, ['EMPLOYEE', 'HR'], true)) $errors[] = 'Invalid role.';

    if (!$errors) {
        db()->beginTransaction();
        try {
            db()->prepare('UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?')->execute([$role, $id]);
            db()->prepare('
              UPDATE employee_profiles
              SET full_name = ?, phone = ?, address = ?, job_title = ?, department = ?, updated_at = NOW()
              WHERE user_id = ?
            ')->execute([$fullName, $phone, $address, $jobTitle, $department, $id]);

            db()->commit();
        } catch (Throwable $e) {
            db()->rollBack();
            $errors[] = 'Save failed: ' . $e->getMessage();
        }
    }

    if (!$errors) {
        flash_set('success', 'Employee updated.');
        redirect('hr/employee_edit.php?id=' . $id);
    }
}

// reload
$stmt->execute([$id]);
$emp = $stmt->fetch();
$page_back_url = base_url('hr/employees.php');
$page_back_text = 'Back to list of employees';

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h3 class="mb-0 surface-heading">Edit Employee</h3>
    <div class="surface-subtle small"><?= e((string)$emp['employee_id']) ?> · <?= e((string)$emp['email']) ?></div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('hr/employees.php')) ?>">Back</a>
  </div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card surface-card">
  <div class="card-body">
    <form method="post" class="row g-3">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input class="form-control" name="full_name" value="<?= e((string)($emp['full_name'] ?? '')) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Role</label>
        <select class="form-select" name="role">
          <option value="EMPLOYEE" <?= $emp['role'] === 'EMPLOYEE' ? 'selected' : '' ?>>Employee</option>
          <option value="HR" <?= $emp['role'] === 'HR' ? 'selected' : '' ?>>Admin / HR</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Email Verified</label>
        <input class="form-control" value="<?= (int)$emp['email_verified'] === 1 ? 'Yes' : 'No' ?>" disabled>
      </div>

      <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input class="form-control" name="phone" value="<?= e((string)($emp['phone'] ?? '')) ?>">
      </div>

      <div class="col-md-8">
        <label class="form-label">Address</label>
        <input class="form-control" name="address" value="<?= e((string)($emp['address'] ?? '')) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Job Title</label>
        <input class="form-control" name="job_title" value="<?= e((string)($emp['job_title'] ?? '')) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Department</label>
        <input class="form-control" name="department" value="<?= e((string)($emp['department'] ?? '')) ?>">
      </div>

      <div class="col-12">
        <button class="btn btn-dark">Save</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

