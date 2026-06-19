<?php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid Request Method.'
    ]);

    exit;
}

$enrollment =
    trim($_POST['enrollment_no'] ?? '');

$email =
    trim($_POST['email'] ?? '');

if (
    empty($enrollment) ||
    empty($email)
) {

    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);

    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            student_id,
            email,
            full_name
        FROM students
        WHERE student_id = ?
        AND email = ?
        LIMIT 1
    ");

    $stmt->execute([
        $enrollment,
        $email
    ]);

    $student =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {

        echo json_encode([
            'success' => false,
            'message' => 'Student record not found.'
        ]);

        exit;
    }

    /*
    ---------------------------------------
    REMOVE OLD TOKENS
    ---------------------------------------
    */

    $deleteStmt =
        $pdo->prepare("
            DELETE FROM password_resets
            WHERE student_id = ?
        ");

    $deleteStmt->execute([
        $student['student_id']
    ]);

    /*
    ---------------------------------------
    CREATE TOKEN
    ---------------------------------------
    */

    $token =
        bin2hex(
            random_bytes(32)
        );

    $expires =
        date(
            'Y-m-d H:i:s',
            time() + 3600
        );

    $insertStmt =
        $pdo->prepare("
            INSERT INTO password_resets
            (
                student_id,
                email,
                reset_token,
                expires_at
            )
            VALUES
            (
                ?, ?, ?, ?
            )
        ");

    $insertStmt->execute([
        $student['student_id'],
        $student['email'],
        password_hash(
            $token,
            PASSWORD_DEFAULT
        ),
        $expires
    ]);

    $_SESSION['reset_token'] =
        $token;

    $_SESSION['reset_student'] =
        $student['student_id'];

    echo json_encode([

        'success' => true,

        'message' =>
            'Identity Verified.',

        'redirect' =>
            'create_new_password.php?token='
            . urlencode($token)

    ]);

} catch (PDOException $e) {

    error_log(
        'PASSWORD_RESET_ERROR: '
        . $e->getMessage()
    );

    echo json_encode([
        'success' => false,
        'message' => 'Database Error.'
    ]);
}