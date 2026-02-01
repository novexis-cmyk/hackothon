<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

function profile_get_by_user_id(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM employee_profiles WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function payroll_ensure_row(int $userId): void
{
    $stmt = db()->prepare('SELECT id FROM payroll WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    if ($stmt->fetch()) return;

    $stmt2 = db()->prepare('INSERT INTO payroll (user_id, created_at) VALUES (?, NOW())');
    $stmt2->execute([$userId]);
}

function date_week_start(DateTimeImmutable $d): DateTimeImmutable
{
    // ISO week starts Monday
    $dow = (int)$d->format('N'); // 1..7
    return $d->modify('-' . ($dow - 1) . ' days')->setTime(0, 0, 0);
}

