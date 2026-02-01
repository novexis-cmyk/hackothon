<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['EMPLOYEE']);
$u = current_user();
$userId = (int)$u['id'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);

    $type = strtoupper(trim((string)($_POST['leave_type'] ?? '')));
    $start = (string)($_POST['start_date'] ?? '');
    $end = (string)($_POST['end_date'] ?? '');
    $remarks = trim((string)($_POST['remarks'] ?? ''));

    if (!in_array($type, ['PAID', 'SICK', 'UNPAID'], true)) $errors[] = 'Invalid leave type.';
    $startD = DateTimeImmutable::createFromFormat('Y-m-d', $start) ?: null;
    $endD = DateTimeImmutable::createFromFormat('Y-m-d', $end) ?: null;
    if (!$startD) $errors[] = 'Start date is required.';
    if (!$endD) $errors[] = 'End date is required.';
    if ($startD && $endD && $endD < $startD) $errors[] = 'End date must be after start date.';

    if (!$errors) {
        db()->prepare('
          INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, remarks, status, created_at)
          VALUES (?, ?, ?, ?, ?, "PENDING", NOW())
        ')->execute([$userId, $type, $start, $end, $remarks]);

        flash_set('success', 'Leave request submitted.');
        redirect('employee/leave.php');
    }
}

$stmt = db()->prepare('SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0 surface-heading">Leave Requests</h3>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('dashboard.php')) ?>">Back</a>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card surface-card">
      <div class="card-body">
        <h5 class="card-title mb-3 surface-heading">Apply for leave</h5>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" class="vstack gap-3">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div>
            <label class="form-label">Leave type</label>
            <select class="form-select" name="leave_type" required>
              <option value="PAID">Paid</option>
              <option value="SICK">Sick</option>
              <option value="UNPAID">Unpaid</option>
            </select>
          </div>

          <div class="row g-2">
            <div class="col">
              <label class="form-label">Start</label>
              <input class="form-control" type="date" name="start_date" id="start_date" required>
            </div>
            <div class="col">
              <label class="form-label">End</label>
              <input class="form-control" type="date" name="end_date" id="end_date" required>
            </div>
          </div>

          <div>
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" rows="3" placeholder="Optional"></textarea>
          </div>

          <button class="btn btn-dark">Submit</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card surface-card-light">
      <div class="card-body">
        <h5 class="card-title mb-3 surface-heading">My requests</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle table-surface">
            <thead>
              <tr>
                <th>Type</th>
                <th>Dates</th>
                <th>Status</th>
                <th>Comment</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$requests): ?>
                <tr><td colspan="4" class="text-muted">No requests yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($requests as $r): ?>
                <?php
                  $status = (string)$r['status'];
                  $badge = $status === 'APPROVED' ? 'success' : ($status === 'REJECTED' ? 'danger' : 'warning');
                ?>
                <tr>
                  <td><?= e((string)$r['leave_type']) ?></td>
                  <td><?= e((string)$r['start_date']) ?> → <?= e((string)$r['end_date']) ?></td>
                  <td><span class="badge text-bg-<?= e($badge) ?>"><?= e($status) ?></span></td>
                  <td class="text-muted"><?= e((string)($r['manager_comment'] ?? '')) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    startDate.min = today;
    endDate.min = today;
    
    // Update end date minimum when start date changes
    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        // Clear end date if it's before the new start date
        if (endDate.value && endDate.value < this.value) {
            endDate.value = '';
        }
    });
    
    // Validate end date on change
    endDate.addEventListener('change', function() {
        if (startDate.value && this.value && this.value < startDate.value) {
            alert('End date cannot be before start date.');
            this.value = '';
        }
    });
});
</script>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

