<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../includes/db_connect.php';

if (!isset($_SESSION['student_id'])) {

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized Access.'
    ]);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid Request.'
    ]);

    exit;
}

$student_id =
    trim($_SESSION['student_id']);

$current_password =
    $_POST['current_password'] ?? '';

$new_password =
    $_POST['new_password'] ?? '';

$confirm_password =
    $_POST['confirm_password'] ?? '';

if (
    empty($current_password)
    ||
    empty($new_password)
    ||
    empty($confirm_password)
) {

    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);

    exit;
}
if ($new_password !== $confirm_password) {

    echo json_encode([
        'success' => false,
        'message' => 'Passwords do not match.'
    ]);

    exit;
}
if (strlen($new_password) < 8) {

    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters.'
    ]);

    exit;
}
try {

    $stmt = $pdo->prepare("
        SELECT password
        FROM students
        WHERE student_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $student_id
    ]);

    $student =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {

        echo json_encode([
            'success' => false,
            'message' => 'Student not found.'
        ]);

        exit;
    }
    if (
        !password_verify(
            $current_password,
            $student['password']
        )
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect.'
        ]);

        exit;
    }
    if (
        password_verify(
            $new_password,
            $student['password']
        )
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'New password cannot be same as current password.'
        ]);

        exit;
    }
    $new_hash =
        password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

    $updateStmt = $pdo->prepare("
        UPDATE students
        SET password = ?
        WHERE student_id = ?
    ");

    $updateStmt->execute([
        $new_hash,
        $student_id
    ]);

    session_unset();
    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully. Please login again.',
        'redirect' => '/EdulyntrixCoreX/public/frontend/student/student_login.php'
    ]);
} catch (PDOException $e) {

    error_log(
        'CHANGE_PASSWORD_ERROR: '
        . $e->getMessage()
    );

    echo json_encode([
        'success' => false,
        'message' => 'System Error.'
    ]);
}