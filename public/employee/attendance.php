<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['EMPLOYEE']);
$u = current_user();
$userId = (int)$u['id'];

$today = new DateTimeImmutable('today');
$weekStart = date_week_start($today);
$weekDays = [];
for ($i = 0; $i < 7; $i++) {
    $weekDays[] = $weekStart->modify("+$i days");
}

// Ensure today's attendance row exists once user interacts (lazy)
$stmtToday = db()->prepare('SELECT * FROM attendance WHERE user_id = ? AND att_date = ? LIMIT 1');
$stmtToday->execute([$userId, $today->format('Y-m-d')]);
$todayRow = $stmtToday->fetch() ?: null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $action = (string)($_POST['action'] ?? '');

    // upsert row for today
    db()->prepare('
        INSERT INTO attendance (user_id, att_date, created_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ')->execute([$userId, $today->format('Y-m-d')]);

    if ($action === 'checkin') {
        db()->prepare('
            UPDATE attendance
            SET check_in = COALESCE(check_in, NOW()),
                status = CASE WHEN status = "LEAVE" THEN "LEAVE" ELSE "PRESENT" END,
                updated_at = NOW()
            WHERE user_id = ? AND att_date = ?
        ')->execute([$userId, $today->format('Y-m-d')]);
        flash_set('success', 'Checked in.');
    } elseif ($action === 'checkout') {
        db()->prepare('
            UPDATE attendance
            SET check_out = NOW(),
                status = CASE WHEN status = "LEAVE" THEN "LEAVE" ELSE status END,
                updated_at = NOW()
            WHERE user_id = ? AND att_date = ?
        ')->execute([$userId, $today->format('Y-m-d')]);
        flash_set('success', 'Checked out.');
    }

    redirect('employee/attendance.php');
}

// Weekly rows
$stmt = db()->prepare('
  SELECT * FROM attendance
  WHERE user_id = ? AND att_date BETWEEN ? AND ?
  ORDER BY att_date ASC
');
$stmt->execute([$userId, $weekStart->format('Y-m-d'), $weekStart->modify('+6 days')->format('Y-m-d')]);
$rows = $stmt->fetchAll();
$byDate = [];
foreach ($rows as $r) {
    $byDate[(string)$r['att_date']] = $r;
}

// refresh todayRow
$stmtToday->execute([$userId, $today->format('Y-m-d')]);
$todayRow = $stmtToday->fetch() ?: null;

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0 surface-heading">My Attendance</h3>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('dashboard.php')) ?>">Back</a>
</div>

<div class="card surface-card-light mb-3">
  <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
      <div class="fw-semibold">Today: <?= e($today->format('D, d M Y')) ?></div>
      <div class="surface-subtle small">
        Check-in: <?= e($todayRow && $todayRow['check_in'] ? (new DateTimeImmutable($todayRow['check_in']))->format('H:i') : '-') ?>
        ·
        Check-out: <?= e($todayRow && $todayRow['check_out'] ? (new DateTimeImmutable($todayRow['check_out']))->format('H:i') : '-') ?>
      </div>
    </div>
    <form method="post" class="d-flex gap-2">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <button class="btn btn-success" name="action" value="checkin" <?= $todayRow && $todayRow['check_in'] ? 'disabled' : '' ?>>Check In</button>
      <button class="btn btn-primary" name="action" value="checkout" <?= !$todayRow || !$todayRow['check_in'] || ($todayRow && $todayRow['check_out']) ? 'disabled' : '' ?>>Check Out</button>
    </form>
  </div>
</div>

<div class="card surface-card">
  <div class="card-body">
    <h5 class="card-title mb-3 surface-heading">This Week</h5>
    <div class="table-responsive">
      <table class="table table-sm align-middle table-surface">
        <thead>
          <tr>
            <th>Date</th>
            <th>Status</th>
            <th>Check-in</th>
            <th>Check-out</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($weekDays as $d): ?>
            <?php $r = $byDate[$d->format('Y-m-d')] ?? null; ?>
            <tr>
              <td><?= e($d->format('D, d M')) ?></td>
              <td>
                <?php $status = $r ? (string)$r['status'] : '—'; ?>
                <span class="badge text-bg-<?= $status === 'PRESENT' ? 'success' : ($status === 'LEAVE' ? 'info' : ($status === 'HALF_DAY' ? 'warning' : ($status === 'ABSENT' ? 'danger' : 'secondary'))) ?>">
                  <?= e($status) ?>
                </span>
              </td>
              <td><?= e($r && $r['check_in'] ? (new DateTimeImmutable($r['check_in']))->format('H:i') : '-') ?></td>
              <td><?= e($r && $r['check_out'] ? (new DateTimeImmutable($r['check_out']))->format('H:i') : '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

