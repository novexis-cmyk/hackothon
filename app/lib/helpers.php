<?php
declare(strict_types=1);

function config(): array
{
    static $cfg = null;
    if (is_array($cfg)) return $cfg;
    $cfg = require __DIR__ . '/../../config/config.php';
    return $cfg;
}

function base_url(string $path = ''): string
{
    $base = rtrim((string)config()['app']['base_url'], '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base : $base . '/' . $path;
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) return null;
    $msg = (string)$_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf'];
}

function csrf_check(?string $token): void
{
    if (!$token || empty($_SESSION['csrf']) || !hash_equals((string)$_SESSION['csrf'], (string)$token)) {
        http_response_code(400);
        echo "Bad Request (CSRF).";
        exit;
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_auth(): void
{
    if (!current_user()) {
        flash_set('error', 'Please sign in.');
        redirect('auth/login.php');
    }
}

function require_role(array $roles): void
{
    $u = current_user();
    if (!$u || !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        echo "Forbidden.";
        exit;
    }
}

