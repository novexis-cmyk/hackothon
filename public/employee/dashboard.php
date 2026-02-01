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
        redirect('employee/dashboard.php');
    }
}

$stmt = db()->prepare('SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();

require __DIR__ . '/../_layout_top.php';
?>

<style>
body {
    background-color: #f5f5f5;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 280px;
    background: #1a1a1a;
    color: white;
    padding: 2rem 1rem;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
    margin-bottom: 3rem;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.sidebar-subtitle {
    font-size: 0.875rem;
    opacity: 0.7;
}

.sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav li {
    margin-bottom: 0.5rem;
}

.sidebar-nav a {
    color: #b0b0b0;
    text-decoration: none;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
}

.sidebar-nav a:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.sidebar-nav a.active {
    background: rgba(255, 255, 255, 0.15);
    color: white;
}

.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    background-color: #f5f5f5;
}

.content-header {
    margin-bottom: 2rem;
}

.content-title {
    font-size: 2rem;
    font-weight: bold;
    color: #2c2c2c;
    margin-bottom: 0.5rem;
}

.content-subtitle {
    color: #666;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e0e0e0;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2c2c2c;
}

.card-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    background: #f8f8f8;
}

.employee-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.employee-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #2c2c2c;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.25rem;
}

.employee-details h4 {
    margin: 0 0 0.25rem 0;
    color: #2c2c2c;
}

.employee-details p {
    margin: 0;
    color: #666;
    font-size: 0.875rem;
}

.working-format {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f8f8f8;
    border-radius: 0.5rem;
    margin-top: 0.5rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #e8f5e8;
    color: #2d5a2d;
}

.onboarding-tasks {
    list-style: none;
    padding: 0;
    margin: 0;
}

.onboarding-tasks li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e0e0e0;
}

.onboarding-tasks li:last-child {
    border-bottom: none;
}

.task-checkbox {
    width: 20px;
    height: 20px;
    border: 2px solid #ccc;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.task-checkbox.completed {
    background: #4caf50;
    border-color: #4caf50;
    color: white;
}

.calendar-widget {
    background: #f8f8f8;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    cursor: pointer;
    color: #666;
}

.calendar-day:hover {
    background: #e0e0e0;
}

.calendar-day.today {
    background: #2c2c2c;
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.25rem;
    border: 1px solid #e0e0e0;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #2c2c2c;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #666;
    font-size: 0.875rem;
}

.leave-form {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e0e0e0;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #2c2c2c;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e0e0e0;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #2c2c2c;
    box-shadow: 0 0 0 3px rgba(44, 44, 44, 0.1);
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #2c2c2c;
    color: white;
}

.btn-primary:hover {
    background: #1a1a1a;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.alert-danger {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.table th {
    font-weight: 600;
    color: #2c2c2c;
    background: #f8f8f8;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}
</style>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <span>🏢</span>
                <span>HRMS</span>
            </div>
            <div class="sidebar-subtitle">Human Resource Management System</div>
        </div>
        
        <nav>
            <ul class="sidebar-nav">
                <li><a href="#" class="active">📊 Dashboard</a></li>
                <li><a href="#">👤 Profile</a></li>
                <li><a href="#">📅 Leave Request</a></li>
                <li><a href="#">💰 Payroll</a></li>
                <li><a href="#">📈 Performance</a></li>
                <li><a href="#">📋 Documents</a></li>
                <li><a href="#">⚙️ Settings</a></li>
                <li><a href="#">🚪 Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h1 class="content-title">Welcome back, <?= e($u['first_name'] ?? 'Employee') ?>! 👋</h1>
            <p class="content-subtitle">Here's what's happening with your leave requests today.</p>
        </div>

        <div class="cards-grid">
            <!-- Employee Info Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employee Information</h3>
                    <div class="card-icon" style="background: #edf2f7;">👤</div>
                </div>
                <div class="employee-info">
                    <div class="employee-avatar">
                        <?= strtoupper(substr($u['first_name'] ?? 'E', 0, 1) . substr($u['last_name'] ?? '', 0, 1)) ?>
                    </div>
                    <div class="employee-details">
                        <h4><?= e(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?></h4>
                        <p><?= e($u['email'] ?? '') ?></p>
                        <div class="working-format">
                            <span>💻</span>
                            <span>Remote Work</span>
                            <span class="status-badge status-active">Active</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onboarding Tasks Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Onboarding Tasks</h3>
                    <div class="card-icon" style="background: #edf2f7;">✅</div>
                </div>
                <ul class="onboarding-tasks">
                    <li>
                        <div class="task-checkbox completed">✓</div>
                        <span>Complete profile information</span>
                    </li>
                    <li>
                        <div class="task-checkbox completed">✓</div>
                        <span>Sign employment contract</span>
                    </li>
                    <li>
                        <div class="task-checkbox"></div>
                        <span>Attend orientation session</span>
                    </li>
                    <li>
                        <div class="task-checkbox"></div>
                        <span>Setup benefits enrollment</span>
                    </li>
                </ul>
            </div>

            <!-- Calendar Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Calendar</h3>
                    <div class="card-icon" style="background: #edf2f7;">📅</div>
                </div>
                <div class="calendar-widget">
                    <div class="calendar-header">
                        <span>◀</span>
                        <span><?= date('F Y') ?></span>
                        <span>▶</span>
                    </div>
                    <div class="calendar-grid">
                        <div class="calendar-day">S</div>
                        <div class="calendar-day">M</div>
                        <div class="calendar-day">T</div>
                        <div class="calendar-day">W</div>
                        <div class="calendar-day">T</div>
                        <div class="calendar-day">F</div>
                        <div class="calendar-day">S</div>
                        <?php for($i = 1; $i <= 31; $i++): ?>
                            <div class="calendar-day <?= $i == date('d') ? 'today' : '' ?>"><?= $i ?></div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Request Form -->
        <div class="leave-form">
            <h3 class="card-title mb-3">Apply for Leave</h3>
            
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Leave Type</label>
                            <select class="form-control" name="leave_type" required>
                                <option value="PAID">Paid Leave</option>
                                <option value="SICK">Sick Leave</option>
                                <option value="UNPAID">Unpaid Leave</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input class="form-control" type="date" name="start_date" id="start_date" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input class="form-control" type="date" name="end_date" id="end_date" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="remarks" rows="3" placeholder="Optional comments..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Leave Request</button>
            </form>
        </div>

        <!-- Recent Leave Requests -->
        <div class="card mt-4">
            <h3 class="card-title mb-3">Recent Leave Requests</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$requests): ?>
                            <tr><td colspan="5" class="text-center text-muted">No leave requests yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($requests as $r): ?>
                            <?php
                                $status = (string)$r['status'];
                                $badgeClass = $status === 'APPROVED' ? 'badge-success' : ($status === 'REJECTED' ? 'badge-danger' : 'badge-warning');
                            ?>
                            <tr>
                                <td><?= e((string)$r['leave_type']) ?></td>
                                <td><?= e((string)$r['start_date']) ?></td>
                                <td><?= e((string)$r['end_date']) ?></td>
                                <td><span class="badge <?= e($badgeClass) ?>"><?= e($status) ?></span></td>
                                <td><?= e((string)($r['remarks'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid mt-4">
            <div class="stat-card">
                <div class="stat-value">12</div>
                <div class="stat-label">Total Leave Days</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">8</div>
                <div class="stat-label">Remaining Days</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">2</div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">95%</div>
                <div class="stat-label">Attendance Rate</div>
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
