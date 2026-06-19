<?php

declare(strict_types=1);

/**
 * ============================================================
 * EDULYNTRIX CORE X
 * STUDENT AUTHENTICATION ENGINE
 * STABLE VERSION
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

require_once __DIR__ . '/db_connect.php';

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);

/*
|--------------------------------------------------------------------------
| POST ONLY
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: ../public/frontend/student/student_login.php'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| RATE LIMITING
|--------------------------------------------------------------------------
*/

$_SESSION['login_attempts'] =
    $_SESSION['login_attempts'] ?? 0;

$_SESSION['last_attempt'] =
    $_SESSION['last_attempt'] ?? 0;

if (
    $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS
    &&
    (time() - $_SESSION['last_attempt']) < LOCKOUT_TIME
) {

    header(
        'Location: ../public/frontend/student/student_login.php?error=locked'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| INPUTS
|--------------------------------------------------------------------------
*/

$student_id =
    trim($_POST['student_id'] ?? '');

$password =
    trim($_POST['password'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (
    empty($student_id)
    ||
    empty($password)
) {

    header(
        'Location: ../public/frontend/student/student_login.php?error=empty'
    );

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | FETCH STUDENT
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM students
        WHERE student_id = ?
        LIMIT 1
    ");

    $stmt->execute([$student_id]);

    $student =
        $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | USER NOT FOUND
    |--------------------------------------------------------------------------
    */

    if (!$student) {

        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();

        header(
            'Location: ../public/frontend/student/student_login.php?error=invalid'
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD VALIDATION
    |--------------------------------------------------------------------------
    */

    $passwordValid = false;

    if (
        !empty($student['password'])
        &&
        password_verify(
            $password,
            $student['password']
        )
    ) {

        $passwordValid = true;
    }
    elseif (
        $password === $student['password']
    ) {

        $passwordValid = true;

        /*
        ----------------------------------------------------------
        AUTO HASH UPGRADE
        ----------------------------------------------------------
        */

        $newHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $upgradeStmt = $pdo->prepare("
            UPDATE students
            SET password = ?
            WHERE student_id = ?
        ");

        $upgradeStmt->execute([
            $newHash,
            $student['student_id']
        ]);
    }

    if (!$passwordValid) {

        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();

        header(
            'Location: ../public/frontend/student/student_login.php?error=invalid'
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD REHASH
    |--------------------------------------------------------------------------
    */

    if (
        !empty($student['password'])
        &&
        password_get_info(
            $student['password']
        )['algo']
        &&
        password_needs_rehash(
            $student['password'],
            PASSWORD_DEFAULT
        )
    ) {

        $newHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $updateStmt = $pdo->prepare("
            UPDATE students
            SET password = ?
            WHERE student_id = ?
        ");

        $updateStmt->execute([
            $newHash,
            $student['student_id']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS CHECK
    |--------------------------------------------------------------------------
    */

    $status =
        strtolower(
            trim(
                (string)($student['status'] ?? 'active')
            )
        );

    if ($status !== 'active') {

        header(
            'Location: ../public/frontend/student/student_login.php?error=inactive'
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | RESET ATTEMPTS
    |--------------------------------------------------------------------------
    */

    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt']   = 0;

    /*
    |--------------------------------------------------------------------------
    | SESSION SECURITY
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    /*
    |--------------------------------------------------------------------------
    | CORE SESSION
    |--------------------------------------------------------------------------
    */

    $_SESSION['logged_in'] = true;
    $_SESSION['role']      = 'student';
    $_SESSION['user_role'] = 'student';

    $_SESSION['student_id'] =
        $student['student_id'];

    $_SESSION['full_name'] =
        $student['full_name'] ?? '';

    $_SESSION['dept_id'] =
        $student['dept_id'] ?? 0;

    $_SESSION['semester'] =
        $student['current_semester']
        ?? 1;

    $_SESSION['profile_pic'] =
        !empty($student['profile_pic'])
            ? $student['profile_pic']
            : 'default.png';

    $_SESSION['email'] =
        $student['email']
        ?? '';

    /*
    |--------------------------------------------------------------------------
    | LOGIN LOG
    |--------------------------------------------------------------------------
    */

    error_log(
        'STUDENT_LOGIN_SUCCESS: '
        . $student['student_id']
    );

    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header(
        'Location: ../public/frontend/student/dashboard.php'
    );

    exit;

}
catch (PDOException $e) {

    error_log(
        'STUDENT_AUTH_ERROR: '
        . $e->getMessage()
    );

    header(
        'Location: ../public/frontend/student/student_login.php?error=system'
    );

    exit;
}