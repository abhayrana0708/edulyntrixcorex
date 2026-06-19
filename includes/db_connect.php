<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
*/

$host     = 'localhost';
$dbname   = 'edulyntrix_db';
$username = 'root';
$password = '';

try {

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $host,
        $dbname
    );

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

} catch (PDOException $e) {

    error_log(
        'DATABASE_CONNECTION_ERROR: '
        . $e->getMessage()
    );

    http_response_code(500);

    exit(
        'Database connection unavailable.'
    );
}