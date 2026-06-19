<?php

/**
 * ============================================================
 * EDULYNTRIX CORE X
 * UNIFIED SESSION TERMINATION ENGINE
 * FINAL FIXED VERSION
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| DETECT USER TYPE BEFORE DESTROY
|--------------------------------------------------------------------------
*/

$isStudent =
    isset($_SESSION['student_id']);

$isStaff =
    isset($_SESSION['staff_id']);

$role =
    strtolower(
        trim(
            $_SESSION['role']
            ??
            $_SESSION['user_role']
            ??
            ''
        )
    );

/*
|--------------------------------------------------------------------------
| DESTROY SESSION
|--------------------------------------------------------------------------
*/

$_SESSION = [];

if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
*/

$base_url = '/EdulyntrixCoreX';

/*
|--------------------------------------------------------------------------
| REDIRECT STUDENT
|--------------------------------------------------------------------------
*/

if ($isStudent) {

    header(
        "Location: {$base_url}/public/frontend/student/student_login.php?logout=success"
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| REDIRECT STAFF
|--------------------------------------------------------------------------
*/

if (
    $isStaff
    ||
    in_array(
        $role,
        ['faculty', 'staff', 'hod', 'admin']
    )
) {

    header(
        "Location: {$base_url}/public/frontend/staff/login.php?logout=success"
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| FALLBACK
|--------------------------------------------------------------------------
*/

header(
    "Location: {$base_url}/index.php"
);

exit;