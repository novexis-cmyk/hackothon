<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

$token = (string)($_GET['token'] ?? '');
if ($token === '') {
    flash_set('error', 'Missing verification token.');
    redirect('auth/login.php');
}

if (auth_verify_email($token)) {
    flash_set('success', 'Email verified. You can now sign in.');
} else {
    flash_set('error', 'Verification link is invalid or already used.');
}

redirect('auth/login.php');

