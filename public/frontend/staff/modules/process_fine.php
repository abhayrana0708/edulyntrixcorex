<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * DISCIPLINARY LEDGER PROCESSOR
 * FINAL SECURE STABLE VERSION
 * ============================================================
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
ERROR CONTROL
============================================================ */

ini_set('display_errors', 0);

error_reporting(E_ALL);

/* ============================================================
DATABASE
============================================================ */

$db_path =
    $_SERVER['DOCUMENT_ROOT']
    . '/EdulyntrixCoreX/includes/db_connect.php';

if (!file_exists($db_path)) {

    $db_path =
        'C:/xampp/htdocs/EdulyntrixCoreX/includes/db_connect.php';
}

if (!file_exists($db_path)) {

    echo json_encode([

        'success' => false,

        'message' => 'DB_CONNECTION_FAILURE'

    ]);

    exit;
}

require_once $db_path;

/* ============================================================
REQUEST METHOD
============================================================ */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([

        'success' => false,

        'message' => 'INVALID_REQUEST_METHOD'

    ]);

    exit;
}

/* ============================================================
SESSION VALIDATION
============================================================ */

$session_staff =

    $_SESSION['staff_id']

    ?? $_SESSION['login_id']

    ?? '';

if (empty($session_staff)) {

    echo json_encode([

        'success' => false,

        'message' => 'SESSION_EXPIRED'

    ]);

    exit;
}

/* ============================================================
INPUTS
============================================================ */

$student_id =
    trim($_POST['student_id'] ?? '');

$amount =
    trim($_POST['amount'] ?? '');

$reason =
    trim($_POST['reason'] ?? '');

$fine_type =
    trim($_POST['fine_type'] ?? 'general');

/* ============================================================
VALIDATION
============================================================ */

if (

    empty($student_id)

    ||

    empty($amount)

    ||

    empty($reason)

) {

    echo json_encode([

        'success' => false,

        'message' => 'MISSING_REQUIRED_FIELDS'

    ]);

    exit;
}

/* ============================================================
AMOUNT VALIDATION
============================================================ */

$amount = floatval($amount);

if (

    $amount <= 0

    ||

    $amount > 50000

) {

    echo json_encode([

        'success' => false,

        'message' => 'INVALID_FINE_AMOUNT'

    ]);

    exit;
}

/* ============================================================
ALLOWED TYPES
============================================================ */

$allowed_types = [

    'discipline',
    'attendance',
    'damage',
    'library',
    'misconduct',
    'general'
];

if (

    !in_array(

        strtolower($fine_type),

        $allowed_types

    )

) {

    $fine_type = 'general';
}

/* ============================================================
MAIN ENGINE
============================================================ */

try {

    /*
    =========================================================
    STAFF VALIDATION
    =========================================================
    */

    $staff_stmt = $pdo->prepare("

        SELECT

            staff_id,
            dept_id,
            role,
            status

        FROM staff

        WHERE

            (
                staff_id = ?
                OR login_id = ?
            )

        LIMIT 1

    ");

    $staff_stmt->execute([

        $session_staff,

        $session_staff

    ]);

    $staff =
        $staff_stmt->fetch(PDO::FETCH_ASSOC);

    /*
    =========================================================
    INVALID STAFF
    =========================================================
    */

    if (
        !$staff
    ) {

        throw new Exception(
            'STAFF_IDENTITY_FAILURE'
        );
    }

    /*
    =========================================================
    STAFF STATUS CHECK
    =========================================================
    */

    if (

        strtolower(
            trim($staff['status'])
        ) !== 'active'

    ) {

        throw new Exception(
            'STAFF_ACCOUNT_DISABLED'
        );
    }

    /*
    =========================================================
    VARIABLES
    =========================================================
    */

    $staff_id =
        trim($staff['staff_id']);

    $dept_id =
        $staff['dept_id'];

    /*
    =========================================================
    VERIFY STUDENT BELONGS TO SAME DEPARTMENT
    =========================================================
    */

    $student_stmt = $pdo->prepare("

        SELECT

            student_id,
            full_name,
            status

        FROM students

        WHERE

            student_id = ?
            AND dept_id = ?

        LIMIT 1

    ");

    $student_stmt->execute([

        $student_id,

        $dept_id

    ]);

    $student =
        $student_stmt->fetch(PDO::FETCH_ASSOC);

    /*
    =========================================================
    INVALID STUDENT
    =========================================================
    */

    if (
        !$student
    ) {

        throw new Exception(
            'UNAUTHORIZED_STUDENT_ACCESS'
        );
    }

    /*
    =========================================================
    STUDENT STATUS
    =========================================================
    */

    if (

        strtolower(
            trim($student['status'])
        ) !== 'active'

    ) {

        throw new Exception(
            'STUDENT_ACCOUNT_INACTIVE'
        );
    }

    /*
    =========================================================
    DUPLICATE PROTECTION
    =========================================================
    */

    $dup_stmt = $pdo->prepare("

        SELECT fine_id

        FROM disciplinary_fines

        WHERE

            student_id = ?
            AND description = ?
            AND DATE(created_at) = CURDATE()

        LIMIT 1

    ");

    $dup_stmt->execute([

        $student_id,

        $reason

    ]);

    /*
    =========================================================
    DUPLICATE FOUND
    =========================================================
    */

    if ($dup_stmt->fetch()) {

        throw new Exception(
            'DUPLICATE_FINE_DETECTED'
        );
    }

    /*
    =========================================================
    INSERT LEDGER ENTRY
    =========================================================
    */

    $insert = $pdo->prepare("

        INSERT INTO disciplinary_fines (

            student_id,
            staff_id,
            total_amount,
            description,
            fine_type,
            status,
            created_at

        )

        VALUES (

            ?, ?, ?, ?, ?, 'Unpaid', NOW()

        )

    ");

    $insert->execute([

        $student_id,

        $staff_id,

        $amount,

        $reason,

        strtolower($fine_type)

    ]);

    /*
    =========================================================
    SUCCESS RESPONSE
    =========================================================
    */

    echo json_encode([

        'success' => true,

        'message' => 'LEDGER_SYNC_COMPLETE'

    ]);

}
catch(Exception $e){

    /*
    =========================================================
    ERROR RESPONSE
    =========================================================
    */

    echo json_encode([

        'success' => false,

        'message' => $e->getMessage()

    ]);
}
?>