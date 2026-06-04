<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * STAFF REGISTRATION ENGINE
 * FULLY FIXED VERSION
 * FILE:
 * /includes/process_staff.php
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
DATABASE
============================================================ */

require_once __DIR__ . '/db_connect.php';

/* ============================================================
REQUEST VALIDATION
============================================================ */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: ../corex_root/layout.php?page=staff_provision"
    );

    exit();
}

/* ============================================================
INPUTS
============================================================ */

$name =
    trim($_POST['s_name'] ?? '');

$phone =
    trim($_POST['s_phone'] ?? '');

$role =
    strtoupper(
        trim($_POST['s_role'] ?? '')
    );

$dept =
    strtoupper(
        trim($_POST['s_dept'] ?? '')
    );

$password_raw =
    trim($_POST['s_pass'] ?? '');

/* ============================================================
VALIDATION
============================================================ */

if (
    empty($name)
    ||
    empty($phone)
    ||
    empty($role)
    ||
    empty($dept)
    ||
    empty($password_raw)
) {

    die("MISSING_REQUIRED_FIELDS");
}

/* ============================================================
ROLE NORMALIZATION
============================================================ */

if ($role === 'FAC') {

    $role_db = 'faculty';

    $designation = 'FACULTY';

} elseif ($role === 'HOD') {

    $role_db = 'hod';

    $designation = 'HOD';

} else {

    die("INVALID_ROLE_SELECTED");
}

/* ============================================================
PASSWORD HASH
============================================================ */

$password =
    password_hash(
        $password_raw,
        PASSWORD_BCRYPT
    );

/* ============================================================
COUNT EXISTING STAFF
============================================================ */

try {

    $count_stmt = $pdo->prepare("

        SELECT COUNT(*) AS total

        FROM staff

        WHERE
            department = ?
            AND role = ?

    ");

    $count_stmt->execute([

        $dept,
        $role_db

    ]);

    $count =
        $count_stmt->fetch(PDO::FETCH_ASSOC);

    /*
    =========================================================
    GENERATE UNIQUE ID
    =========================================================
    */

    $next_num =
        str_pad(
            ($count['total'] ?? 0) + 1,
            3,
            '0',
            STR_PAD_LEFT
        );

    /*
    =========================================================
    STAFF ID
    =========================================================
    */

    $generated_uid =
        "STF-"
        . $dept
        . "-"
        . strtoupper($role_db)
        . "-"
        . $next_num;

    /*
    =========================================================
    LOGIN ID
    =========================================================
    */

    $login_id =
        strtolower($dept)
        . "_"
        . strtolower($role_db)
        . "_"
        . $next_num;

    /*
    =========================================================
    CHECK DUPLICATES
    =========================================================
    */

    $dup = $pdo->prepare("

        SELECT id

        FROM staff

        WHERE
            mobile = ?
            OR login_id = ?
            OR staff_id = ?

        LIMIT 1

    ");

    $dup->execute([

        $phone,
        $login_id,
        $generated_uid

    ]);

    if ($dup->fetch()) {

        die("STAFF_ALREADY_EXISTS");
    }

    /*
    =========================================================
    INSERT STAFF
    =========================================================
    */

    $stmt = $pdo->prepare("

        INSERT INTO staff (

            staff_id,
            login_id,
            full_name,
            mobile,
            role,
            department,
            password,
            designation,
            status,
            created_at

        )

        VALUES (

            ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW()

        )

    ");

    $stmt->execute([

        $generated_uid,

        $login_id,

        $name,

        $phone,

        $role_db,

        $dept,

        $password,

        $designation

    ]);

    /*
    =========================================================
    SUCCESS REDIRECT
    =========================================================
    */

    header(

        "Location: ../corex_root/layout.php?page=staff_provision"

        . "&status=success"

        . "&id=" . urlencode($generated_uid)

        . "&login=" . urlencode($login_id)

    );

    exit();

}
catch(PDOException $e){

    error_log(
        "STAFF_REGISTRATION_ERROR : "
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

                EDULYNTRIX CORE FAILURE

            </h3>

            <p>

                Staff registration engine crashed.

            </p>

            <hr style='opacity:.2;'>

            <small>

                " . htmlspecialchars($e->getMessage()) . "

            </small>

        </div>

    ");
}
?>