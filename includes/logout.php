<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * UNIFIED SESSION TERMINATION ENGINE
 * FINAL STABLE VERSION
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
ERROR CONTROL
============================================================ */

ini_set('display_errors', 0);

error_reporting(E_ALL);

/* ============================================================
CAPTURE ROLE BEFORE SESSION DESTROY
============================================================ */

$role = strtolower(

    trim(

        $_SESSION['role']

        ?? $_SESSION['user_role']

        ?? 'guest'
    )
);

/* ============================================================
CLEAR SESSION ARRAY
============================================================ */

$_SESSION = [];

/* ============================================================
DESTROY SESSION COOKIE
============================================================ */

if (ini_get('session.use_cookies')) {

    $params =
        session_get_cookie_params();

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

/* ============================================================
DESTROY SESSION
============================================================ */

session_destroy();

/* ============================================================
BASE PROJECT URL
============================================================ */

$base_url = '/EdulyntrixCoreX';

/* ============================================================
REDIRECT ENGINE
============================================================ */

switch ($role) {

    /*
    ========================================================
    STUDENT REDIRECT
    ========================================================
    */

    case 'student':

        header(

            'Location: '

            .

            $base_url

            .

            '/public/frontend/student/student_login.php?logout=success'
        );

        exit;

    /*
    ========================================================
    STAFF / FACULTY / HOD / ADMIN
    ========================================================
    */

    case 'faculty':

    case 'hod':

    case 'staff':

    case 'admin':

        header(

            'Location: '

            .

            $base_url

            .

            '/public/frontend/staff/login.php?logout=success'
        );

        exit;

    /*
    ========================================================
    FALLBACK
    ========================================================
    */

    default:

        header(

            'Location: '

            .

            $base_url

            .

            '/index.php'
        );

        exit;
}
?>