<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

require_auth();
require_role(['EMPLOYEE']);

$u = current_user();
$profile = profile_get_by_user_id((int)$u['id']);
if (!$profile) {
    flash_set('error', 'Profile not found.');
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));

    $stmt = db()->prepare('UPDATE employee_profiles SET full_name = ?, phone = ?, address = ?, updated_at = NOW() WHERE user_id = ?');
    $stmt->execute([$fullName, $phone, $address, (int)$u['id']]);

    flash_set('success', 'Profile updated.');
    redirect('employee/profile.php');
}

$profile = profile_get_by_user_id((int)$u['id']);
$page_back_url = base_url('dashboard.php');
$page_back_text = 'Back to dashboard';

require __DIR__ . '/../_layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0 surface-heading">My Profile</h3>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('dashboard.php')) ?>">Back</a>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card surface-card-light">
      <div class="card-body">
        <h5 class="card-title mb-3 surface-heading">Overview</h5>
        <dl class="row mb-0">
          <dt class="col-5">Employee ID</dt><dd class="col-7"><?= e($u['employee_id']) ?></dd>
          <dt class="col-5">Email</dt><dd class="col-7"><?= e($u['email']) ?></dd>
          <dt class="col-5">Full Name</dt><dd class="col-7"><?= e((string)$profile['full_name']) ?></dd>
          <dt class="col-5">Job Title</dt><dd class="col-7"><?= e((string)$profile['job_title']) ?></dd>
          <dt class="col-5">Department</dt><dd class="col-7"><?= e((string)$profile['department']) ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card surface-card">
      <div class="card-body">
        <h5 class="card-title mb-3 surface-heading">Edit (limited)</h5>

        <?php if ($errors): ?>
          <div class="alert alert-danger surface-card-light">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" class="vstack gap-3">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div>
            <label class="form-label">Full Name</label>
            <input class="form-control" name="full_name" value="<?= e((string)$profile['full_name']) ?>">
          </div>

          <div>
            <label class="form-label">Phone</label>
            <input class="form-control" name="phone" value="<?= e((string)$profile['phone']) ?>">
          </div>

          <div>
            <label class="form-label">Address</label>
            <textarea class="form-control" name="address" rows="4"><?= e((string)($profile['address'] ?? '')) ?></textarea>
          </div>

          <button class="btn btn-dark">Save</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_layout_bottom.php'; ?>

