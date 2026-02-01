<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

session_destroy();
session_start();
flash_set('success', 'Signed out.');
redirect('index.php');

