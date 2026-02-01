<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['HR']);

$date = (string)($_GET['date'] ?? (new DateTimeImmutable('today'))->format('Y-m-d'));
$dObj = DateTimeImmutable::createFromFormat('Y-m-d', $date) ?: new DateTimeImmutable('today');
$date = $dObj->format('Y-m-d');

$stmt = db()->prepare('
  SELECT u.employee_id, u.email, p.full_name, a.att_date, a.status, a.check_in, a.check_out
  FROM users u
  LEFT JOIN employee_profiles p ON p.user_id = u.id
  LEFT JOIN attendance a ON a.user_id = u.id AND a.att_date = ?
  ORDER BY u.employee_id ASC
  LIMIT 500
');
$stmt->execute([$date]);
$rows = $stmt->fetchAll();

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0 surface-heading">Attendance Records</h3>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('dashboard.php')) ?>">Back</a>
</div>

<div class="card surface-card-light mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Date</label>
        <input class="form-control" type="date" name="date" value="<?= e($date) ?>">
      </div>
      <div class="col-auto">
        <button class="btn btn-dark">View</button>
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
            <th>Employee</th>
            <th>Status</th>
            <th>Check-in</th>
            <th>Check-out</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <?php
              $status = $r['status'] ? (string)$r['status'] : '—';
              $badge = $status === 'PRESENT' ? 'success' : ($status === 'LEAVE' ? 'info' : ($status === 'HALF_DAY' ? 'warning' : ($status === 'ABSENT' ? 'danger' : 'secondary')));
            ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= e((string)($r['full_name'] ?? '')) ?></div>
                <div class="text-muted small"><?= e((string)$r['employee_id']) ?> · <?= e((string)$r['email']) ?></div>
              </td>
              <td><span class="badge text-bg-<?= e($badge) ?>"><?= e($status) ?></span></td>
              <td><?= e($r['check_in'] ? (new DateTimeImmutable((string)$r['check_in']))->format('H:i') : '-') ?></td>
              <td><?= e($r['check_out'] ? (new DateTimeImmutable((string)$r['check_out']))->format('H:i') : '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

