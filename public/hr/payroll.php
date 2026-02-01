<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['HR']);

$u = current_user();
$adminId = (int)$u['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $userId = (int)($_POST['user_id'] ?? 0);
    $basic = (float)($_POST['basic_salary'] ?? 0);
    $allow = (float)($_POST['allowances'] ?? 0);
    $ded = (float)($_POST['deductions'] ?? 0);
    $cur = trim((string)($_POST['currency'] ?? 'INR'));
    if ($cur === '') $cur = 'INR';

    if ($userId > 0) {
        payroll_ensure_row($userId);
        db()->prepare('
          UPDATE payroll
          SET basic_salary = ?, allowances = ?, deductions = ?, currency = ?, updated_by_user_id = ?, updated_at = NOW()
          WHERE user_id = ?
        ')->execute([$basic, $allow, $ded, $cur, $adminId, $userId]);

        flash_set('success', 'Payroll updated.');
    }
    redirect('hr/payroll.php');
}

$stmt = db()->query('
  SELECT u.id AS user_id, u.employee_id, u.email, p.full_name,
         pr.basic_salary, pr.allowances, pr.deductions, pr.currency
  FROM users u
  LEFT JOIN employee_profiles p ON p.user_id = u.id
  LEFT JOIN payroll pr ON pr.user_id = u.id
  ORDER BY u.employee_id ASC
  LIMIT 300
');
$rows = $stmt->fetchAll();

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0 surface-heading">Payroll Control</h3>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('dashboard.php')) ?>">Back</a>
</div>

<div class="card surface-card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle table-surface">
        <thead>
          <tr>
            <th>Employee</th>
            <th style="width:120px;">Currency</th>
            <th style="width:140px;">Basic</th>
            <th style="width:140px;">Allowances</th>
            <th style="width:140px;">Deductions</th>
            <th style="width:110px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <form method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="user_id" value="<?= (int)$r['user_id'] ?>">
                <td>
                  <div class="fw-semibold"><?= e((string)($r['full_name'] ?? '')) ?></div>
                  <div class="text-muted small"><?= e((string)$r['employee_id']) ?> · <?= e((string)$r['email']) ?></div>
                </td>
                <td><input class="form-control form-control-sm" name="currency" value="<?= e((string)($r['currency'] ?? 'INR')) ?>"></td>
                <td><input class="form-control form-control-sm" name="basic_salary" value="<?= e((string)($r['basic_salary'] ?? 0)) ?>"></td>
                <td><input class="form-control form-control-sm" name="allowances" value="<?= e((string)($r['allowances'] ?? 0)) ?>"></td>
                <td><input class="form-control form-control-sm" name="deductions" value="<?= e((string)($r['deductions'] ?? 0)) ?>"></td>
                <td class="text-end"><button class="btn btn-sm btn-dark">Save</button></td>
              </form>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

