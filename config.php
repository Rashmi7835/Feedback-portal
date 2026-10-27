<?php
declare(strict_types=1);

/**
 * Copy to config.php and set your database credentials.
 * Never commit config.php to a public repository.
 */
return [
    'app_name' => 'Cloudsoftera Feedback',
    'base_url' => '', // e.g. https://intranet.example.com/feedback ; keep blank for auto-detection
    'timezone' => 'Asia/Kolkata',
    'session_name' => 'CSFEEDBACK',
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'cloudsoftera_feedback',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
