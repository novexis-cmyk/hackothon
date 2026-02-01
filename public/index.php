<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

$user = current_user();
if ($user && $user['role'] === 'EMPLOYEE') {
    redirect('dashboard.php');
}

$totalEmployees = 0;
$onLeaveToday = 0;
$pendingLeaves = 0;
$isAdmin = $user && $user['role'] === 'HR';

if ($isAdmin) {
    try {
        $today = new DateTimeImmutable('today');
        $stmtEmp = db()->query("SELECT COUNT(*) AS c FROM users WHERE role = 'EMPLOYEE'");
        $rowEmp = $stmtEmp->fetch();
        $totalEmployees = $rowEmp ? (int)$rowEmp['c'] : 0;
        $stmtLeave = db()->prepare("
            SELECT COUNT(DISTINCT user_id) AS c FROM leave_requests
            WHERE status = 'APPROVED' AND start_date <= ? AND end_date >= ?
        ");
        $stmtLeave->execute([$today->format('Y-m-d'), $today->format('Y-m-d')]);
        $rowLeave = $stmtLeave->fetch();
        $onLeaveToday = $rowLeave ? (int)$rowLeave['c'] : 0;
        $stmtPending = db()->query("SELECT COUNT(*) AS c FROM leave_requests WHERE status = 'PENDING'");
        $rowPending = $stmtPending->fetch();
        $pendingLeaves = $rowPending ? (int)$rowPending['c'] : 0;
    } catch (Throwable $e) { }
}

require __DIR__ . '/_layout_top.php';
?>

<section class="py-4 py-md-5">
  <div class="row align-items-center g-4">
    <div class="<?= $isAdmin ? 'col-lg-7' : 'col-12' ?>">
      <div class="card card-glass border-0">
        <div class="card-body p-4 p-md-5">
          <div class="badge-soft mb-3">Human Resource Management</div>
          <h1 class="display-5 fw-semibold text-white mb-3">
            Modern HRMS for<br>attendance, leave & payroll.
          </h1>
          <p class="lead text-gray-300 mb-4" style="color:#cbd5f5;">
            Digitize employee onboarding, time tracking and approvals in one clean, secure dashboard —
            built for small and medium teams on WAMP / PHP.
          </p>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-light btn-lg px-4" href="<?= e(base_url('auth/login.php')) ?>">Sign in</a>
            <a class="btn btn-outline-light btn-lg px-4" href="<?= e(base_url('auth/register.php')) ?>">Create account</a>
          </div>
          <div class="mt-4 d-flex flex-wrap gap-3 small text-secondary">
            <span>✓ Role-based access (Employee / HR)</span>
            <span>✓ Attendance with daily & weekly view</span>
            <span>✓ Leave approvals & payroll visibility</span>
          </div>
        </div>
      </div>
    </div>
    <?php if ($isAdmin): ?>
    <div class="col-lg-5">
      <div class="row g-3">
        <div class="col-12">
          <div class="card card-glass-light h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0 text-white">Snapshot</h5>
                <span class="badge bg-success-subtle border border-success text-success">Live demo</span>
              </div>
              <p class="text-secondary small mb-3">
                See a high-level summary of your people: active employees, pending leave requests and payroll status.
              </p>
              <div class="row g-3 small">
                <div class="col-4">
                  <div class="border rounded-3 p-2 text-center bg-dark bg-opacity-25">
                    <div class="text-secondary">Employees</div>
                    <div class="fs-5 fw-semibold text-white"><?= e((string)number_format($totalEmployees)) ?></div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="border rounded-3 p-2 text-center bg-dark bg-opacity-25">
                    <div class="text-secondary">On leave</div>
                    <div class="fs-5 fw-semibold text-white"><?= e((string)number_format($onLeaveToday)) ?></div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="border rounded-3 p-2 text-center bg-dark bg-opacity-25">
                    <div class="text-secondary">Pending</div>
                    <div class="fs-5 fw-semibold text-white"><?= e((string)number_format($pendingLeaves)) ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card card-glass-light h-100">
            <div class="card-body">
              <h6 class="text-white mb-2">Core modules</h6>
              <div class="d-flex flex-wrap gap-2 small">
                <span class="badge bg-primary">Employee profiles</span>
                <span class="badge bg-info">Attendance</span>
                <span class="badge bg-warning text-dark">Leave & time-off</span>
                <span class="badge bg-success">Payroll view</span>
                <span class="badge bg-secondary">Approvals</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/_layout_bottom.php'; ?>

