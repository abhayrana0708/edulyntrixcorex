<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/db_connect.php';

/*
|--------------------------------------------------------------------------
| POST ONLY
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: student_login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| INPUTS
|--------------------------------------------------------------------------
*/

$token =
    trim($_POST['token'] ?? '');

$password =
    $_POST['password'] ?? '';

if (
    empty($token)
    ||
    empty($password)
) {

    die('Invalid Request.');
}

if (strlen($password) < 8) {

    die('Password must be at least 8 characters.');
}

/*
|--------------------------------------------------------------------------
| SESSION TOKEN CHECK
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['reset_token'])
    ||
    !isset($_SESSION['reset_student'])
) {

    die('Reset Session Expired.');
}

if (
    $token !== $_SESSION['reset_token']
) {

    die('Invalid Reset Token.');
}

try {

    /*
    |--------------------------------------------------------------------------
    | FIND TOKEN RECORD
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM password_resets
        WHERE student_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute([
        $_SESSION['reset_student']
    ]);

    $reset =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {

        die('Token Record Missing.');
    }

    /*
    |--------------------------------------------------------------------------
    | EXPIRATION CHECK
    |--------------------------------------------------------------------------
    */

    if (
        strtotime($reset['expires_at'])
        < time()
    ) {

        die('Reset Token Expired.');
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN VERIFY
    |--------------------------------------------------------------------------
    */

    if (
        !password_verify(
            $token,
            $reset['reset_token']
        )
    ) {

        die('Token Verification Failed.');
    }

    /*
    |--------------------------------------------------------------------------
    | HASH NEW PASSWORD
    |--------------------------------------------------------------------------
    */

    $passwordHash =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );

    /*
    |--------------------------------------------------------------------------
    | UPDATE STUDENT PASSWORD
    |--------------------------------------------------------------------------
    */

    $updateStmt =
        $pdo->prepare("
            UPDATE students
            SET password = ?
            WHERE student_id = ?
        ");

    $updateStmt->execute([

        $passwordHash,

        $_SESSION['reset_student']

    ]);

    /*
    |--------------------------------------------------------------------------
    | DELETE USED TOKEN
    |--------------------------------------------------------------------------
    */

    $deleteStmt =
        $pdo->prepare("
            DELETE FROM password_resets
            WHERE student_id = ?
        ");

    $deleteStmt->execute([

        $_SESSION['reset_student']

    ]);

    /*
    |--------------------------------------------------------------------------
    | DESTROY RESET SESSION
    |--------------------------------------------------------------------------
    */

    unset($_SESSION['reset_token']);
    unset($_SESSION['reset_student']);

    header(
        'Location: student_login.php?password_reset=success'
    );

    exit;

} catch (PDOException $e) {

    error_log(
        'PASSWORD_RESET_PROCESS_ERROR: '
        . $e->getMessage()
    );

    die('System Error.');
}