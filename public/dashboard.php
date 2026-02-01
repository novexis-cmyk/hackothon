<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

require_auth();
$u = current_user();
$userId = (int)$u['id'];

$profile = profile_get_by_user_id($userId);
$profileName = trim((string)($profile['full_name'] ?? '')) ?: $u['employee_id'];
$profileTitle = trim((string)($profile['job_title'] ?? '')) ?: $u['role'];
$initial = mb_strtoupper(mb_substr($profileName, 0, 1));

$daysPresent = 0;
$leavePending = 0;
$netSalary = 0;
$totalEmployees = 0;
$onLeaveToday = 0;

if ($u['role'] === 'EMPLOYEE') {
  $today = new DateTimeImmutable('today');
  $weekStart = date_week_start($today);
  $stmt = db()->prepare('SELECT COUNT(*) AS c FROM attendance WHERE user_id = ? AND att_date BETWEEN ? AND ? AND status = "PRESENT"');
  $stmt->execute([$userId, $weekStart->format('Y-m-d'), $weekStart->modify('+6 days')->format('Y-m-d')]);
  $row = $stmt->fetch();
  $daysPresent = $row ? (int)$row['c'] : 0;
  $stmt2 = db()->prepare('SELECT COUNT(*) AS c FROM leave_requests WHERE user_id = ? AND status = "PENDING"');
  $stmt2->execute([$userId]);
  $row2 = $stmt2->fetch();
  $leavePending = $row2 ? (int)$row2['c'] : 0;
  payroll_ensure_row($userId);
  $stmt3 = db()->prepare('SELECT basic_salary, allowances, deductions, currency FROM payroll WHERE user_id = ? LIMIT 1');
  $stmt3->execute([$userId]);
  $pay = $stmt3->fetch();
  if ($pay) {
    $netSalary = (float)$pay['basic_salary'] + (float)$pay['allowances'] - (float)$pay['deductions'];
  }
} else {
  $stmt = db()->query("SELECT COUNT(*) AS c FROM users WHERE role = 'EMPLOYEE'");
  $row = $stmt->fetch();
  $totalEmployees = $row ? (int)$row['c'] : 0;
  $today = (new DateTimeImmutable('today'))->format('Y-m-d');
  $stmt2 = db()->prepare("SELECT COUNT(DISTINCT user_id) AS c FROM leave_requests WHERE status = 'APPROVED' AND start_date <= ? AND end_date >= ?");
  $stmt2->execute([$today, $today]);
  $row2 = $stmt2->fetch();
  $onLeaveToday = $row2 ? (int)$row2['c'] : 0;
  $stmt3 = db()->query("SELECT COUNT(*) AS c FROM leave_requests WHERE status = 'PENDING'");
  $row3 = $stmt3->fetch();
  $leavePending = $row3 ? (int)$row3['c'] : 0;
}

require __DIR__ . '/_layout_top.php';
?>

<div class="row g-3 mb-4">
  <?php if ($u['role'] === 'EMPLOYEE'): ?>
  <div class="col-md-4">
    <a href="<?= e(base_url('employee/profile.php')) ?>" class="text-decoration-none">
      <div class="dashboard-card h-100">
        <div class="profile-card-cover"></div>
        <div class="profile-card-avatar"><?= e($initial) ?></div>
        <div class="profile-card-body">
          <div class="profile-card-name"><?= e($profileName) ?></div>
          <div class="profile-card-title"><?= e($profileTitle) ?></div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= e(base_url('employee/attendance.php')) ?>" class="text-decoration-none text-reset">
      <div class="dashboard-card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-secondary text-uppercase">Attendance this week</span>
          </div>
          <div class="donut-wrap d-flex align-items-center justify-content-center">
            <div class="rounded-circle border border-3 border-success d-flex align-items-center justify-content-center" style="width:100px;height:100px;">
              <span class="stat-number"><?= $daysPresent ?>/7</span>
            </div>
          </div>
          <div class="stat-label text-center mt-2">Days present</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= e(base_url('employee/leave.php')) ?>" class="text-decoration-none text-reset">
      <div class="dashboard-card h-100">
        <div class="card-body">
          <div class="small text-secondary text-uppercase mb-2">Leave requests</div>
          <div class="progress mb-2" style="height:8px;">
            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $leavePending > 0 ? '100' : '0' ?>%"></div>
          </div>
          <div class="stat-number"><?= $leavePending ?></div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
    </a>
  </div>
  <?php else: ?>
  <div class="col-md-4">
    <a href="<?= e(base_url('hr/employees.php')) ?>" class="text-decoration-none text-reset">
      <div class="dashboard-card h-100">
        <div class="card-body">
          <div class="stat-number"><?= $totalEmployees ?></div>
          <div class="stat-label">Employees</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= e(base_url('hr/attendance.php')) ?>" class="text-decoration-none text-reset">
      <div class="dashboard-card h-100">
        <div class="card-body">
          <div class="stat-number"><?= $onLeaveToday ?></div>
          <div class="stat-label">On leave today</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= e(base_url('hr/leave_approvals.php')) ?>" class="text-decoration-none text-reset">
      <div class="dashboard-card h-100">
        <div class="card-body">
          <div class="stat-number"><?= $leavePending ?></div>
          <div class="stat-label">Pending approvals</div>
        </div>
      </div>
    </a>
  </div>
  <?php endif; ?>
</div>

<div class="row g-3">
  <?php if ($u['role'] === 'EMPLOYEE'): ?>
  <div class="col-md-4">
    <a href="<?= e(base_url('employee/payroll.php')) ?>" class="text-decoration-none text-reset">
      <div class="dashboard-card h-100">
        <div class="card-body">
          <div class="stat-number"><?= e(number_format($netSalary, 0)) ?></div>
          <div class="stat-label">Net salary (approx.)</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= e(base_url('employee/profile.php')) ?>" class="text-decoration-none text-reset">
      <div class="dashboard-card h-100">
        <div class="card-body">
          <div class="small text-secondary text-uppercase mb-1">Personal data</div>
          <div class="text-truncate">View &amp; edit profile</div>
        </div>
      </div>
    </a>
  </div>
  <?php else: ?>
  <div class="col-md-4">
    <a href="<?= e(base_url('hr/payroll.php')) ?>" class="text-decoration-none text-reset">
      <div class="dashboard-card h-100">
        <div class="card-body">
          <div class="small text-secondary text-uppercase mb-1">Payroll</div>
          <div>Edit salary structures</div>
        </div>
      </div>
    </a>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
