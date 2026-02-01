<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['HR']);

$u = current_user();
$deciderId = (int)$u['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $id = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    $comment = trim((string)($_POST['comment'] ?? ''));

    if ($id > 0 && in_array($action, ['approve', 'reject'], true)) {
        $status = $action === 'approve' ? 'APPROVED' : 'REJECTED';

        // Load request
        $stmt = db()->prepare('SELECT * FROM leave_requests WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $req = $stmt->fetch();

        if ($req && (string)$req['status'] === 'PENDING') {
            db()->beginTransaction();
            try {
                db()->prepare('
                    UPDATE leave_requests
                    SET status = ?, manager_comment = ?, decided_by_user_id = ?, decided_at = NOW(), updated_at = NOW()
                    WHERE id = ?
                ')->execute([$status, $comment, $deciderId, $id]);

                // If approved, mark attendance as LEAVE for date range (create rows if missing)
                if ($status === 'APPROVED') {
                    $start = new DateTimeImmutable((string)$req['start_date']);
                    $end = new DateTimeImmutable((string)$req['end_date']);
                    $userId = (int)$req['user_id'];
                    for ($d = $start; $d <= $end; $d = $d->modify('+1 day')) {
                        db()->prepare('
                            INSERT INTO attendance (user_id, att_date, status, created_at)
                            VALUES (?, ?, "LEAVE", NOW())
                            ON DUPLICATE KEY UPDATE status = "LEAVE", updated_at = NOW()
                        ')->execute([$userId, $d->format('Y-m-d')]);
                    }
                }

                db()->commit();
                flash_set('success', 'Leave request updated.');
            } catch (Throwable $e) {
                db()->rollBack();
                flash_set('error', 'Update failed: ' . $e->getMessage());
            }
        }
    }

    redirect('hr/leave_approvals.php');
}

$stmt = db()->query('
  SELECT lr.*, u.employee_id, u.email, p.full_name
  FROM leave_requests lr
  JOIN users u ON u.id = lr.user_id
  LEFT JOIN employee_profiles p ON p.user_id = u.id
  ORDER BY
    CASE lr.status WHEN "PENDING" THEN 0 WHEN "APPROVED" THEN 1 ELSE 2 END,
    lr.created_at DESC
  LIMIT 200
');
$requests = $stmt->fetchAll();

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0 surface-heading">Leave Approvals</h3>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('dashboard.php')) ?>">Back</a>
</div>

<div class="card surface-card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle table-surface">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>Dates</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$requests): ?>
            <tr><td colspan="6" class="text-muted">No requests.</td></tr>
          <?php endif; ?>
          <?php foreach ($requests as $r): ?>
            <?php
              $status = (string)$r['status'];
              $badge = $status === 'APPROVED' ? 'success' : ($status === 'REJECTED' ? 'danger' : 'warning');
            ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= e((string)($r['full_name'] ?? '')) ?></div>
                <div class="text-muted small"><?= e((string)$r['employee_id']) ?> · <?= e((string)$r['email']) ?></div>
              </td>
              <td><?= e((string)$r['leave_type']) ?></td>
              <td><?= e((string)$r['start_date']) ?> → <?= e((string)$r['end_date']) ?></td>
              <td><span class="badge text-bg-<?= e($badge) ?>"><?= e($status) ?></span></td>
              <td class="text-muted" style="max-width: 260px;"><?= e((string)($r['remarks'] ?? '')) ?></td>
              <td style="min-width: 320px;">
                <?php if ($status === 'PENDING'): ?>
                  <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input class="form-control form-control-sm" name="comment" placeholder="Comment (optional)">
                    <button class="btn btn-sm btn-success" name="action" value="approve">Approve</button>
                    <button class="btn btn-sm btn-danger" name="action" value="reject">Reject</button>
                  </form>
                <?php else: ?>
                  <div class="text-muted small"><?= e((string)($r['manager_comment'] ?? '')) ?></div>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

