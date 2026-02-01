<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['HR']);

$q = trim((string)($_GET['q'] ?? ''));

if ($q !== '') {
    $stmt = db()->prepare('
      SELECT u.id, u.employee_id, u.email, u.role, u.email_verified, p.full_name, p.job_title, p.department
      FROM users u
      LEFT JOIN employee_profiles p ON p.user_id = u.id
      WHERE u.employee_id LIKE ? OR u.email LIKE ? OR p.full_name LIKE ?
      ORDER BY u.id DESC
      LIMIT 200
    ');
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = db()->query('
      SELECT u.id, u.employee_id, u.email, u.role, u.email_verified, p.full_name, p.job_title, p.department
      FROM users u
      LEFT JOIN employee_profiles p ON p.user_id = u.id
      ORDER BY u.id DESC
      LIMIT 200
    ');
}
$users = $stmt->fetchAll();

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0 surface-heading">Employees</h3>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('dashboard.php')) ?>">Back</a>
</div>

<div class="card surface-card-light mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Search</label>
        <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Employee ID, email, name">
      </div>
      <div class="col-auto">
        <button class="btn btn-dark">Search</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('hr/employees.php')) ?>">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card surface-card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle table-surface">
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Job</th>
            <th>Role</th>
            <th>Verified</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$users): ?>
            <tr><td colspan="7" class="text-muted">No employees found.</td></tr>
          <?php endif; ?>
          <?php foreach ($users as $row): ?>
            <tr>
              <td><?= e((string)$row['employee_id']) ?></td>
              <td><?= e((string)($row['full_name'] ?? '')) ?></td>
              <td><?= e((string)$row['email']) ?></td>
              <td class="text-muted"><?= e((string)($row['job_title'] ?? '')) ?> <?= $row['department'] ? '· ' . e((string)$row['department']) : '' ?></td>
              <td><span class="badge text-bg-<?= $row['role'] === 'HR' ? 'dark' : 'secondary' ?>"><?= e((string)$row['role']) ?></span></td>
              <td><?= (int)$row['email_verified'] === 1 ? 'Yes' : 'No' ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-dark" href="<?= e(base_url('hr/employee_edit.php?id=' . (int)$row['id'])) ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

