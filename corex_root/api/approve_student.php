<?php
/**
 * EDULYNTRIX CORE X - ENROLLMENT APPROVAL NODE
 * FULL FIXED VERSION
 */

header('Content-Type: application/json');

require_once '../../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| AUTHORIZATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['hod', 'admin'])
) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized Access'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| GET REQUEST ID
|--------------------------------------------------------------------------
*/

$queue_id = $_POST['id'] ?? null;

if (!$queue_id) {

    $json = json_decode(
        file_get_contents('php://input'),
        true
    );

    $queue_id = $json['id'] ?? null;
}

if (!$queue_id) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid Queue ID'
    ]);

    exit;
}

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | FETCH PENDING STUDENT
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM enrollment_queue
        WHERE id = ?
        AND status = 'pending'
        LIMIT 1
    ");

    $stmt->execute([$queue_id]);

    $temp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$temp) {

        throw new Exception(
            'Student not found or already processed.'
        );
    }
        /*
    |--------------------------------------------------------------------------
    | DUPLICATE CHECK
    |--------------------------------------------------------------------------
    */

    $check = $pdo->prepare("
        SELECT id
        FROM students
        WHERE student_id = ?
        LIMIT 1
    ");

    $check->execute([
        $temp['student_id']
    ]);

    if ($check->fetch()) {

        throw new Exception(
            'Student already exists in registry.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT STUDENT
    |--------------------------------------------------------------------------
    */

    $insert = $pdo->prepare("
        INSERT INTO students
        (
            student_id,
            full_name,
            email,
            phone,
            dept_id,
            password,

            father_name,
            father_phone,

            mother_name,
            mother_phone,

            address,
            profile_pic,

            current_semester,
            semester,
            academic_year,

            status,
            enrollment_date
        )
        VALUES
        (
            :student_id,
            :full_name,
            :email,
            :phone,
            :dept_id,
            :password,

            :father_name,
            :father_phone,

            :mother_name,
            :mother_phone,

            :address,
            :profile_pic,

            1,
            1,
            1,

            'Active',
            CURDATE()
        )
    ");

    $insert->execute([

        ':student_id'   => $temp['student_id'],
        ':full_name'    => $temp['student_name'],
        ':email'        => $temp['email'],
        ':phone'        => $temp['phone'],
        ':dept_id'      => $temp['dept_id'],
        ':password'     => $temp['password'],

        ':father_name'  => $temp['father_name'],
        ':father_phone' => $temp['father_phone'],

        ':mother_name'  => $temp['mother_name'],
        ':mother_phone' => $temp['mother_phone'],

        ':address'      => $temp['address'],
        ':profile_pic'  => $temp['profile_pic']
    ]);
        /*
    |--------------------------------------------------------------------------
    | UPDATE QUEUE STATUS
    |--------------------------------------------------------------------------
    */

    $update = $pdo->prepare("
        UPDATE enrollment_queue
        SET status = 'approved'
        WHERE id = ?
    ");

    $update->execute([
        $queue_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | COMMIT TRANSACTION
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    echo json_encode([

        'success' => true,

        'message' =>
            'Student Approved Successfully'

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

        'success' => false,

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

    error_log(
        'APPROVAL_ERROR: '
        . $e->getMessage()
    );

    echo json_encode([

        'success' => false,

        'message' =>
            'Database Error'

    ]);
}
?>