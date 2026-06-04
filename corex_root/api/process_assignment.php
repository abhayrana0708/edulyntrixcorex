<?php
/**
 * EDULYNTRIX CORE X - DEPLOYMENT API
 * Location: /corex_root/api/process_assignment.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| DATABASE PATH RESOLUTION
|--------------------------------------------------------------------------
*/

$db_path =
    dirname(__DIR__, 2)
    . '/includes/db_connect.php';

if (!file_exists($db_path)) {

    $db_path =
        $_SERVER['DOCUMENT_ROOT']
        . '/edulyntrixcorex/includes/db_connect.php';
}

if (file_exists($db_path)) {

    require_once $db_path;

} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'DB_LINK_NOT_FOUND_AT_' . $db_path
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| REQUEST METHOD VALIDATION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'INVALID_REQUEST_METHOD'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| INPUT SANITIZATION
|--------------------------------------------------------------------------
*/

$faculty_id =
    trim($_POST['faculty_id'] ?? '');

$subject_id =
    trim($_POST['subject_id'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (empty($faculty_id)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'MISSING_FACULTY_ID'
    ]);

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | VERIFY FACULTY EXISTS
    |--------------------------------------------------------------------------
    */

    $faculty_check = $pdo->prepare("
        SELECT
            staff_id,
            dept_id,
            role
        FROM staff
        WHERE staff_id = ?
        LIMIT 1
    ");

    $faculty_check->execute([$faculty_id]);

    $faculty =
        $faculty_check->fetch(PDO::FETCH_ASSOC);

    if (!$faculty) {

        throw new Exception(
            'FACULTY_NOT_FOUND'
        );
    }

    if ($faculty['role'] !== 'faculty') {

        throw new Exception(
            'INVALID_FACULTY_ROLE'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!empty($subject_id)) {

        $subject_check = $pdo->prepare("
            SELECT
                subject_id,
                dept_id
            FROM subjects
            WHERE subject_id = ?
            LIMIT 1
        ");

        $subject_check->execute([$subject_id]);

        $subject =
            $subject_check->fetch(PDO::FETCH_ASSOC);

        if (!$subject) {

            throw new Exception(
                'SUBJECT_NOT_FOUND'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CROSS-DEPARTMENT PROTECTION
        |--------------------------------------------------------------------------
        */

        if (
            $subject['dept_id']
            !=
            $faculty['dept_id']
        ) {

            throw new Exception(
                'CROSS_DEPARTMENT_ASSIGNMENT_BLOCKED'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION START
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | CLEAR OLD ASSIGNMENTS
    |--------------------------------------------------------------------------
    |
    | One faculty can only handle
    | one subject at a time.
    |--------------------------------------------------------------------------
    */

    $clear = $pdo->prepare("
        UPDATE subjects

        SET assigned_faculty_id = NULL

        WHERE assigned_faculty_id = ?
    ");

    $clear->execute([$faculty_id]);

    /*
    |--------------------------------------------------------------------------
    | ASSIGN NEW SUBJECT
    |--------------------------------------------------------------------------
    */

    if (!empty($subject_id)) {

        $assign = $pdo->prepare("
            UPDATE subjects

            SET assigned_faculty_id = ?

            WHERE subject_id = ?
        ");

        $assign->execute([
            $faculty_id,
            $subject_id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'DEPLOYMENT_SYNCED'
    ]);

} catch (Exception $e) {

    if (
        isset($pdo)
        &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

} catch (PDOException $e) {

    if (
        isset($pdo)
        &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'DATABASE_ERROR'
    ]);
}