<?php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connect.php';

/*
|--------------------------------------------------------------------------
| AUTHORIZATION
|--------------------------------------------------------------------------
*/

$role = strtolower(
    trim(
        $_SESSION['role']
        ??
        $_SESSION['user_role']
        ??
        ''
    )
);

if (!in_array($role, ['hod', 'admin', 'faculty'])) {

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized Access',
        'debug_role' => $role
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| SESSION DATA
|--------------------------------------------------------------------------
*/

$staff_id = (int)($_SESSION['user_id'] ?? 0);
$dept_id  = (int)($_SESSION['dept_id'] ?? 0);

if ($staff_id <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid Session'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| REQUEST DATA
|--------------------------------------------------------------------------
*/

$input = json_decode(
    file_get_contents('php://input'),
    true
);

$leave_id =
    $input['id']
    ??
    $_POST['id']
    ??
    null;

$status =
    $input['status']
    ??
    $_POST['status']
    ??
    null;

if (
    empty($leave_id)
    ||
    empty($status)
) {

    echo json_encode([
        'success' => false,
        'message' => 'Missing Request Parameters'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| VALID STATUS
|--------------------------------------------------------------------------
*/

$status = ucfirst(strtolower(trim($status)));

if (
    !in_array(
        $status,
        ['Approved', 'Rejected']
    )
) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid Status'
    ]);

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | VERIFY REQUEST
    |--------------------------------------------------------------------------
    */

    if ($role === 'hod') {

        $verify = $pdo->prepare("
            SELECT leave_id
            FROM leave_requests
            WHERE leave_id = ?
            AND dept_id = ?
            AND status = 'Pending'
            LIMIT 1
        ");

        $verify->execute([
            $leave_id,
            $dept_id
        ]);

    } else {

        $verify = $pdo->prepare("
            SELECT leave_id
            FROM leave_requests
            WHERE leave_id = ?
            AND status = 'Pending'
            LIMIT 1
        ");

        $verify->execute([
            $leave_id
        ]);
    }

    if (!$verify->fetch()) {

        echo json_encode([
            'success' => false,
            'message' => 'Leave Request Not Found'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE REQUEST
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE leave_requests
        SET
            status = ?,
            reviewed_by = ?,
            review_date = NOW()
        WHERE leave_id = ?
    ");

    $stmt->execute([
        $status,
        $staff_id,
        $leave_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Leave Request ' . $status
    ]);

} catch (PDOException $e) {

    error_log(
        'LEAVE_PROCESS_ERROR: '
        . $e->getMessage()
    );

    echo json_encode([
        'success' => false,
        'message' => 'Database Sync Failure'
    ]);
}