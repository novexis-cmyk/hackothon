<?php
declare(strict_types=1);

// Basic app configuration (edit for your environment)
return [
    'app' => [
        'name' => 'HRMS',
        // IMPORTANT: Change this in production
        'secret_key' => 'change-this-secret-key',
        // Base URL for generating links (adjust if needed)
        'base_url' => 'http://localhost/php program/hproject/public',
    ],
    'db' => [
        'host' => 'localhost',
        'name' => 'hrms',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
];

