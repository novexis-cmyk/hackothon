<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

function auth_find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function auth_find_user_by_employee_id(string $employeeId): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE employee_id = ? LIMIT 1');
    $stmt->execute([$employeeId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function auth_password_valid(string $password): bool
{
    // Basic policy: >= 8 chars, at least 1 upper, 1 lower, 1 digit
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}

function auth_create_user(string $employeeId, string $email, string $password, string $role): array
{
    $role = strtoupper($role);
    if (!in_array($role, ['EMPLOYEE', 'HR'], true)) {
        throw new RuntimeException('Invalid role.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $verifyToken = bin2hex(random_bytes(32));

    db()->beginTransaction();
    try {
        $stmt = db()->prepare('
            INSERT INTO users (employee_id, email, password_hash, role, email_verified, email_verify_token, created_at)
            VALUES (?, ?, ?, ?, 0, ?, NOW())
        ');
        $stmt->execute([$employeeId, $email, $hash, $role, $verifyToken]);
        $userId = (int)db()->lastInsertId();

        $stmt2 = db()->prepare('
            INSERT INTO employee_profiles (user_id, full_name, phone, address, job_title, department, created_at)
            VALUES (?, "", "", "", "", "", NOW())
        ');
        $stmt2->execute([$userId]);

        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }

    $u = auth_find_user_by_email($email);
    if (!$u) throw new RuntimeException('User creation failed.');
    return $u;
}

function auth_verify_email(string $token): bool
{
    $stmt = db()->prepare('SELECT id FROM users WHERE email_verify_token = ? AND email_verified = 0 LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if (!$row) return false;

    $stmt2 = db()->prepare('UPDATE users SET email_verified = 1, email_verify_token = NULL WHERE id = ?');
    $stmt2->execute([(int)$row['id']]);
    return true;
}

