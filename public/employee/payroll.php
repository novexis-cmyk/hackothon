<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['EMPLOYEE']);
$u = current_user();
$userId = (int)$u['id'];

payroll_ensure_row($userId);
$stmt = db()->prepare('SELECT * FROM payroll WHERE user_id = ? LIMIT 1');
$stmt->execute([$userId]);
$pay = $stmt->fetch();

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0 surface-heading">My Payroll</h3>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('dashboard.php')) ?>">Back</a>
</div>

<div class="card surface-card">
  <div class="card-body">
    <h5 class="card-title mb-3 surface-heading">Salary (read-only)</h5>
    <div class="row g-3">
      <div class="col-md-4">
        <div class="border rounded p-3 bg-dark bg-opacity-25">
          <div class="surface-subtle small">Basic</div>
          <div class="fs-5 fw-semibold"><?= e((string)$pay['currency']) ?> <?= e(number_format((float)$pay['basic_salary'], 2)) ?></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="border rounded p-3 bg-dark bg-opacity-25">
          <div class="surface-subtle small">Allowances</div>
          <div class="fs-5 fw-semibold"><?= e((string)$pay['currency']) ?> <?= e(number_format((float)$pay['allowances'], 2)) ?></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="border rounded p-3 bg-dark bg-opacity-25">
          <div class="surface-subtle small">Deductions</div>
          <div class="fs-5 fw-semibold"><?= e((string)$pay['currency']) ?> <?= e(number_format((float)$pay['deductions'], 2)) ?></div>
        </div>
      </div>
    </div>

    <?php
      $net = (float)$pay['basic_salary'] + (float)$pay['allowances'] - (float)$pay['deductions'];
    ?>
    <hr>
    <div class="d-flex justify-content-between">
      <div class="surface-subtle">Net Pay (approx.)</div>
      <div class="fw-bold"><?= e((string)$pay['currency']) ?> <?= e(number_format($net, 2)) ?></div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

