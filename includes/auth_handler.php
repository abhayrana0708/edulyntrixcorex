<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * STUDENT AUTHENTICATION ENGINE
 * FINAL FIXED VERSION
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_connect.php';

/* ============================================================
METHOD VALIDATION
============================================================ */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: ../public/frontend/student/student_login.php'
    );

    exit;
}

/* ============================================================
INPUTS
============================================================ */

$student_id =
    trim($_POST['student_id'] ?? '');

$password =
    trim($_POST['password'] ?? '');

/* ============================================================
VALIDATION
============================================================ */

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
    =========================================================
    STUDENT FETCH
    =========================================================
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
    =========================================================
    INVALID USER
    =========================================================
    */

    if (!$student) {

        header(
            'Location: ../public/frontend/student/student_login.php?error=invalid'
        );

        exit;
    }

    /*
    =========================================================
    PASSWORD VERIFY
    SUPPORT:
    - plain text
    - hashed passwords
    =========================================================
    */

    $password_valid = false;

    if (
        password_verify(
            $password,
            $student['password']
        )
    ) {

        $password_valid = true;

    } elseif (

        trim($password)
        === trim($student['password'])

    ) {

        $password_valid = true;
    }

    if (!$password_valid) {

        header(
            'Location: ../public/frontend/student/student_login.php?error=invalid'
        );

        exit;
    }

    /*
    =========================================================
    STATUS CHECK
    =========================================================
    */

    if (
        isset($student['status'])
        &&
        strtolower($student['status']) !== 'active'
    ) {

        header(
            'Location: ../public/frontend/student/student_login.php?error=inactive'
        );

        exit;
    }

    /*
    =========================================================
    SESSION SECURITY
    =========================================================
    */

    session_unset();

    session_regenerate_id(true);

    /*
    =========================================================
    CORE SESSION
    =========================================================
    */

    $_SESSION['logged_in'] = true;

    $_SESSION['role'] =
        'student';

    $_SESSION['user_role'] =
        'student';

    /*
    =========================================================
    STUDENT DATA
    =========================================================
    */

    $_SESSION['student_id'] =
        trim($student['student_id']);

    $_SESSION['full_name'] =
        $student['full_name'];

    $_SESSION['dept_id'] =
        $student['dept_id'];

    $_SESSION['semester'] =
        $student['current_semester'];

    $_SESSION['profile_pic'] =
        $student['profile_pic']
        ?: 'default.png';

    $_SESSION['email'] =
        $student['email'] ?? '';

    /*
    =========================================================
    DEBUG
    =========================================================
    */

    $_SESSION['debug_student'] = [

        'student_id' =>
            $_SESSION['student_id'],

        'dept_id' =>
            $_SESSION['dept_id'],

        'semester' =>
            $_SESSION['semester']
    ];

    /*
    =========================================================
    REDIRECT
    =========================================================
    */

    header(
        'Location: ../public/frontend/student/dashboard.php'
    );

    exit;

}

catch(PDOException $e){

    error_log(
        'STUDENT_AUTH_ERROR : '
        . $e->getMessage()
    );

    die("

        <div style='
            padding:25px;
            margin:30px;
            background:#fff1f2;
            border:1px solid #fecdd3;
            border-radius:16px;
            color:#dc2626;
            font-family:Arial;
        '>

            <h3 style='margin-top:0;'>

                STUDENT AUTH FAILURE

            </h3>

            <p>

                Authentication engine crashed.

            </p>

            <hr style='opacity:.2;'>

            <small>

                " . htmlspecialchars($e->getMessage()) . "

            </small>

        </div>

    ");
}
?>