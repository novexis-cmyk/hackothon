<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(config()['app']['name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background: radial-gradient(circle at top left, #1f2937, #020617) fixed;
      color: #0f172a;
    }
    .app-shell {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .app-main {
      flex: 1;
    }
    .navbar-blur {
      backdrop-filter: blur(12px);
      background: rgba(15, 23, 42, 0.92);
      border-bottom: 1px solid rgba(148, 163, 184, 0.2);
    }
    .card-glass {
      background: rgba(15, 23, 42, 0.7);
      border-radius: 1rem;
      border: 1px solid rgba(148, 163, 184, 0.4);
      box-shadow: 0 24px 80px rgba(15, 23, 42, 0.9);
      color: #e5e7eb;
    }
    .card-glass-light {
      background: rgba(15, 23, 42, 0.6);
      border-radius: 0.9rem;
      border: 1px solid rgba(148, 163, 184, 0.35);
      color: #e5e7eb;
    }
    .badge-soft {
      background: rgba(96, 165, 250, 0.16);
      color: #e5e7eb;
      border-radius: 999px;
      padding: 0.3rem 0.8rem;
      font-size: 0.72rem;
      letter-spacing: .04em;
      text-transform: uppercase;
    }
    .quick-link-row {
      border-radius: 999px;
      padding: 0.9rem 1.3rem;
      border: 1px solid rgba(148, 163, 184, 0.35);
      background: radial-gradient(circle at top left, rgba(15,23,42,0.9), rgba(15,23,42,0.7));
      color: #e5e7eb;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1.25rem;
      text-decoration: none;
      box-shadow: 0 18px 40px rgba(15,23,42,0.85);
      transform: translateY(0);
      opacity: 0;
      animation: fadeUp 0.5s ease forwards;
    }
    .quick-link-row + .quick-link-row {
      margin-top: 0.75rem;
    }
    .quick-link-row:hover {
      transform: translateY(-2px);
      border-color: rgba(96, 165, 250, 0.8);
      box-shadow: 0 24px 60px rgba(15,23,42,0.95);
    }
    .quick-link-main {
      display: flex;
      flex-direction: column;
      gap: 0.15rem;
    }
    .quick-link-title {
      font-weight: 600;
      color: #f9fafb;
    }
    .quick-link-sub {
      font-size: 0.75rem;
      color: #9ca3af;
    }
    .quick-link-action {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.45rem 0.85rem;
      border-radius: 999px;
      font-size: 0.75rem;
      background: rgba(15,23,42,0.9);
      border: 1px solid rgba(148,163,184,0.55);
      color: #e5e7eb;
      white-space: nowrap;
    }
    .quick-link-icon {
      display: inline-flex;
      width: 1.3rem;
      height: 1.3rem;
      border-radius: 999px;
      align-items: center;
      justify-content: center;
      background: rgba(37,99,235,0.85);
      color: #e5e7eb;
      font-size: 0.8rem;
    }
    .quick-link-row:nth-child(1) { animation-delay: 0.05s; }
    .quick-link-row:nth-child(2) { animation-delay: 0.12s; }
    .quick-link-row:nth-child(3) { animation-delay: 0.19s; }
    .quick-link-row:nth-child(4) { animation-delay: 0.26s; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .surface-card {
      background: radial-gradient(circle at top left, rgba(15,23,42,0.95), rgba(15,23,42,0.8));
      border-radius: 1rem;
      border: 1px solid rgba(148, 163, 184, 0.5);
      box-shadow: 0 20px 60px rgba(15, 23, 42, 0.9);
      color: #e5e7eb;
      animation: fadeUp 0.45s ease forwards;
      opacity: 0;
    }
    .surface-card-light {
      background: rgba(15,23,42,0.85);
      border-radius: 0.85rem;
      border: 1px solid rgba(148, 163, 184, 0.45);
      box-shadow: 0 16px 40px rgba(15, 23, 42, 0.85);
      color: #e5e7eb;
      animation: fadeUp 0.45s ease forwards;
      opacity: 0;
    }
    .surface-card + .surface-card,
    .surface-card-light + .surface-card-light {
      margin-top: 1rem;
    }
    .surface-heading {
      color: #e5e7eb;
    }
    .surface-subtle {
      color: #9ca3af;
    }
    .table-surface > :not(caption) > * > * {
      background-color: transparent;
      color: #e5e7eb;
      border-bottom-color: rgba(55, 65, 81, 0.8);
    }
    .table-surface thead th {
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #9ca3af;
    }
    .btn {
      border-radius: 999px;
      padding-inline: 1.2rem;
      padding-block: 0.5rem;
      transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background-color 0.15s ease;
    }
    .btn-dark,
    .btn-primary,
    .btn-success,
    .btn-danger {
      border-radius: 999px;
      padding-inline: 1.4rem;
      padding-block: 0.55rem;
      box-shadow: 0 16px 40px rgba(30,64,175,0.65);
      border: none;
    }
    .btn-dark:hover,
    .btn-primary:hover,
    .btn-success:hover,
    .btn-danger:hover {
      transform: translateY(-1px);
      box-shadow: 0 24px 50px rgba(30,64,175,0.85);
    }
    .btn-outline-light,
    .btn-outline-secondary,
    .btn-outline-dark {
      border-radius: 999px;
      padding-inline: 1.2rem;
      padding-block: 0.5rem;
      box-shadow: 0 10px 28px rgba(15,23,42,0.8);
      border-width: 1px;
    }
    .btn-outline-light:hover,
    .btn-outline-secondary:hover,
    .btn-outline-dark:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 40px rgba(15,23,42,0.95);
      border-color: rgba(148,163,184,0.9);
      background-color: rgba(15,23,42,0.9);
      color: #e5e7eb;
    }
    .text-muted,
    .text-secondary {
      color: #f9fafb !important;
      opacity: 0.9;
    }
    input[type="date"] {
      color-scheme: dark;
      color: #f9fafb;
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
      filter: invert(1);
      opacity: 0.9;
    }
    .form-control,
    .form-select {
      border-radius: 0.7rem;
      border-color: rgba(148,163,184,0.6);
      background-color: rgba(15,23,42,0.9);
      color: #e5e7eb;
    }
    .form-control:focus,
    .form-select:focus {
      border-color: rgba(96,165,250,0.9);
      box-shadow: 0 0 0 1px rgba(96,165,250,0.5);
    }
    .form-label {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #9ca3af;
    }
    .app-footer {
      border-top: 1px solid rgba(148, 163, 184, 0.3);
      color: #9ca3af;
      font-size: 0.8rem;
    }
    .app-with-sidebar { display: flex; min-height: 100vh; }
    .sidebar {
      width: 260px;
      min-width: 260px;
      background: #1e293b;
      border-right: 1px solid rgba(148, 163, 184, 0.2);
      display: flex;
      flex-direction: column;
      padding: 1.5rem 0;
    }
    .sidebar-logo { font-size: 1.25rem; font-weight: 700; color: #f8fafc; padding: 0 1.5rem 1.5rem; border-bottom: 1px solid rgba(148, 163, 184, 0.2); margin-bottom: 1rem; }
    .sidebar-nav { flex: 1; }
    .sidebar-nav a {
      display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem;
      color: #94a3b8; text-decoration: none; font-size: 0.8rem; letter-spacing: .04em; text-transform: uppercase;
      transition: background .15s, color .15s;
    }
    .sidebar-nav a:hover { color: #f8fafc; background: rgba(148, 163, 184, 0.1); }
    .sidebar-nav a.active { color: #f8fafc; background: rgba(34, 197, 94, 0.25); border-left: 3px solid #22c55e; }
    .sidebar-nav .nav-icon { width: 1.25rem; text-align: center; opacity: .9; }
    .sidebar-cta { margin: 1rem 1rem 0; padding: 1rem; background: rgba(15, 23, 42, 0.8); border-radius: 0.75rem; border: 1px solid rgba(148, 163, 184, 0.25); }
    .sidebar-cta .btn { font-size: 0.75rem; padding: 0.4rem 0.75rem; }
    .sidebar-user { padding: 1rem 1.5rem; border-top: 1px solid rgba(148, 163, 184, 0.2); display: flex; align-items: center; gap: 0.75rem; }
    .sidebar-user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 0.9rem; }
    .sidebar-user-info { flex: 1; min-width: 0; }
    .sidebar-user-name { font-size: 0.85rem; font-weight: 600; color: #f8fafc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar-user-role { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }
    .main-wrap { flex: 1; display: flex; flex-direction: column; min-width: 0; background: radial-gradient(circle at top left, #1f2937, #0f172a); }
    .main-topbar { padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; }
    .main-topbar h1 { font-size: 1.5rem; font-weight: 700; color: #f8fafc; margin: 0; }
    .main-topbar-actions { display: flex; align-items: center; gap: 0.5rem; }
    .main-topbar-actions a { color: #94a3b8; padding: 0.5rem; border-radius: 0.5rem; }
    .main-topbar-actions a:hover { color: #f8fafc; background: rgba(148, 163, 184, 0.15); }
    .main-content { flex: 1; padding: 0 1.5rem 1.5rem; overflow: auto; }
    .dashboard-card { background: rgba(30, 41, 59, 0.8); border-radius: 1rem; border: 1px solid rgba(148, 163, 184, 0.2); overflow: hidden; color: #e2e8f0; }
    .dashboard-card .card-body { padding: 1.25rem; }
    .stat-number { font-size: 1.75rem; font-weight: 700; color: #f8fafc; }
    .stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-top: 0.25rem; }
    .profile-card-cover { height: 100px; background: linear-gradient(135deg, #334155, #1e293b); position: relative; }
    .profile-card-avatar { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); border: 3px solid #1e293b; position: absolute; bottom: -32px; left: 1.25rem; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; font-weight: 700; }
    .profile-card-body { padding: 2.5rem 1.25rem 1.25rem; }
    .profile-card-name { font-size: 1.1rem; font-weight: 600; color: #f8fafc; }
    .profile-card-title { font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; }
  </style>
</head>
<body>
<?php
$__current_path = $_SERVER['PHP_SELF'] ?? '';
$__logged_in = (bool)current_user();
if ($__logged_in):
  $__cu = current_user();
  $__initial = mb_strtoupper(mb_substr($__cu['employee_id'], 0, 1));
  $__display_name = $__cu['employee_id'];
  try {
    $__pro = profile_get_by_user_id((int)$__cu['id']);
    if ($__pro && !empty(trim((string)($__pro['full_name'] ?? '')))) $__display_name = trim((string)$__pro['full_name']);
  } catch (Throwable $e) { }
?>
<div class="app-with-sidebar">
  <aside class="sidebar">
    <div class="sidebar-logo"><?= e(config()['app']['name']) ?></div>
    <nav class="sidebar-nav">
      <a href="<?= e(base_url('dashboard.php')) ?>" class="<?= (strpos($__current_path, 'dashboard') !== false) ? 'active' : '' ?>"><span class="nav-icon">&#9635;</span> Dashboard</a>
      <?php if ($__cu['role'] === 'HR'): ?>
        <a href="<?= e(base_url('hr/employees.php')) ?>" class="<?= (strpos($__current_path, 'hr/employees') !== false) ? 'active' : '' ?>"><span class="nav-icon">&#128100;</span> Employees</a>
        <a href="<?= e(base_url('hr/attendance.php')) ?>" class="<?= (strpos($__current_path, 'hr/attendance') !== false) ? 'active' : '' ?>"><span class="nav-icon">&#128197;</span> Attendance</a>
        <a href="<?= e(base_url('hr/leave_approvals.php')) ?>" class="<?= (strpos($__current_path, 'hr/leave_approvals') !== false) ? 'active' : '' ?>"><span class="nav-icon">&#128203;</span> Leave</a>
      <?php else: ?>
        <a href="<?= e(base_url('employee/profile.php')) ?>" class="<?= (strpos($__current_path, 'employee/profile') !== false) ? 'active' : '' ?>"><span class="nav-icon">&#128100;</span> Profile</a>
        <a href="<?= e(base_url('employee/attendance.php')) ?>" class="<?= (strpos($__current_path, 'employee/attendance') !== false) ? 'active' : '' ?>"><span class="nav-icon">&#128197;</span> Attendance</a>
        <a href="<?= e(base_url('employee/leave.php')) ?>" class="<?= (strpos($__current_path, 'employee/leave') !== false) ? 'active' : '' ?>"><span class="nav-icon">&#128203;</span> Leave</a>
      <?php endif; ?>
      <a href="<?= e($__cu['role'] === 'HR' ? base_url('hr/payroll.php') : base_url('employee/payroll.php')) ?>" class="<?= (strpos($__current_path, 'payroll') !== false) ? 'active' : '' ?>"><span class="nav-icon">&#128176;</span> Payroll</a>
    </nav>
    <div class="sidebar-cta">
      <div class="small text-secondary mb-2">Reports &amp; analytics</div>
      <a href="<?= e(base_url('dashboard.php')) ?>" class="btn btn-success btn-sm w-100">Try now &rarr;</a>
    </div>
    <div class="sidebar-user">
      <div class="sidebar-user-avatar"><?= e($__initial) ?></div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= e($__display_name) ?></div>
        <div class="sidebar-user-role"><?= e($__cu['role']) ?></div>
      </div>
    </div>
  </aside>
  <div class="main-wrap">
    <header class="main-topbar">
      <div>
        <h1>Hello <?= e($__display_name) ?></h1>
        <?php if (!empty($page_back_url)): ?>
          <a href="<?= e($page_back_url) ?>" class="small text-secondary text-decoration-none"><?= e($page_back_text ?? 'Go back') ?> &rarr;</a>
        <?php endif; ?>
      </div>
      <div class="main-topbar-actions">
        <a href="<?= e(base_url('dashboard.php')) ?>" title="Dashboard">&#8962;</a>
        <a href="<?= e(base_url('auth/logout.php')) ?>" title="Logout">Logout</a>
      </div>
    </header>
    <main class="main-content">
      <?php if ($msg = flash_get('error')): ?>
        <div class="alert alert-danger shadow-sm mb-3"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash_get('success')): ?>
        <div class="alert alert-success shadow-sm mb-3"><?= e($msg) ?></div>
      <?php endif; ?>
<?php else: ?>
<div class="app-shell">
  <nav class="navbar navbar-expand-lg navbar-dark navbar-blur sticky-top">
    <div class="container py-1">
      <a class="navbar-brand fw-semibold" href="<?= e(base_url('index.php')) ?>"><?= e(config()['app']['name']) ?></a>
      <div class="collapse navbar-collapse justify-content-end" id="mainNav">
        <div class="d-flex gap-2">
          <a class="btn btn-outline-light btn-sm px-3" href="<?= e(base_url('auth/login.php')) ?>">Sign In</a>
          <a class="btn btn-warning btn-sm px-3" href="<?= e(base_url('auth/register.php')) ?>">Sign Up</a>
        </div>
      </div>
    </div>
  </nav>
  <main class="app-main">
    <div class="container py-4 py-md-5">
      <?php if ($msg = flash_get('error')): ?>
        <div class="alert alert-danger shadow-sm mb-3"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash_get('success')): ?>
        <div class="alert alert-success shadow-sm mb-3"><?= e($msg) ?></div>
      <?php endif; ?>
<?php endif; ?>
