<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * ENROLLMENT STATUS PROCESSOR
 * FINAL FIXED VERSION
 * ============================================================
 */

header('Content-Type: application/json');

/* ============================================================
SESSION
============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
DATABASE
============================================================ */

require_once '../../includes/db_connect.php';

/* ============================================================
METHOD VALIDATION
============================================================ */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'success' => false,
        'message' => 'INVALID_REQUEST_METHOD'
    ]);

    exit;
}

/* ============================================================
INPUTS
============================================================ */

$id =
    trim($_POST['id'] ?? '');

$status =
    trim($_POST['status'] ?? '');

/* ============================================================
VALIDATION
============================================================ */

if (
    empty($id)
    ||
    empty($status)
) {

    echo json_encode([
        'success' => false,
        'message' => 'MISSING_REQUIRED_FIELDS'
    ]);

    exit;
}

/* ============================================================
ALLOWED STATUS
============================================================ */

$allowed_status = [
    'Pending',
    'Approved',
    'Rejected'
];

if (
    !in_array(
        $status,
        $allowed_status
    )
) {

    echo json_encode([
        'success' => false,
        'message' => 'INVALID_STATUS_VALUE'
    ]);

    exit;
}

/* ============================================================
MAIN ENGINE
============================================================ */

try {

    /*
    =========================================================
    CHECK RECORD
    =========================================================
    */

    $check = $pdo->prepare("

        SELECT id

        FROM enrollment_queue

        WHERE id = ?

        LIMIT 1

    ");

    $check->execute([$id]);

    if (!$check->fetch()) {

        echo json_encode([
            'success' => false,
            'message' => 'ENROLLMENT_RECORD_NOT_FOUND'
        ]);

        exit;
    }

    /*
    =========================================================
    UPDATE STATUS
    =========================================================
    */

    $stmt = $pdo->prepare("

        UPDATE enrollment_queue

        SET
            status = ?,
            updated_at = NOW()

        WHERE id = ?

    ");

    $stmt->execute([
        $status,
        $id
    ]);

    /*
    =========================================================
    SUCCESS
    =========================================================
    */

    echo json_encode([
        'success' => true,
        'message' => 'ENROLLMENT_STATUS_UPDATED'
    ]);

}
catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => 'DATABASE_UPDATE_FAILURE',
        'error' => $e->getMessage()
    ]);
}
?>