<?php

require_once __DIR__ . '/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/frontend/student/registration.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| CSRF CHECK
|--------------------------------------------------------------------------
*/

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('Invalid request.');
}

/*
|--------------------------------------------------------------------------
| INPUTS
|--------------------------------------------------------------------------
*/

$full_name     = trim($_POST['full_name'] ?? '');
$father_name   = trim($_POST['father_name'] ?? '');
$father_phone  = trim($_POST['father_phone'] ?? '');
$mother_name   = trim($_POST['mother_name'] ?? '');
$mother_phone  = trim($_POST['mother_phone'] ?? '');
$address       = trim($_POST['address'] ?? '');
$email         = trim($_POST['email'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$dept_id       = (int)($_POST['dept_id'] ?? 0);
$password      = $_POST['password'] ?? '';

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (
    empty($full_name) ||
    empty($father_name) ||
    empty($father_phone) ||
    empty($mother_name) ||
    empty($mother_phone) ||
    empty($address) ||
    empty($email) ||
    empty($phone) ||
    empty($dept_id) ||
    empty($password)
) {
    die('Please fill all required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email address.');
}

if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
    die('Invalid student mobile number.');
}

if (!preg_match('/^[6-9][0-9]{9}$/', $father_phone)) {
    die('Invalid father mobile number.');
}

if (!preg_match('/^[6-9][0-9]{9}$/', $mother_phone)) {
    die('Invalid mother mobile number.');
}

if (strlen($password) < 8) {
    die('Password must be at least 8 characters.');
}

try {

    /*
    |--------------------------------------------------------------------------
    | DEPARTMENT CHECK
    |--------------------------------------------------------------------------
    */

    $deptStmt = $pdo->prepare("
        SELECT dept_name
        FROM departments
        WHERE id = ?
        LIMIT 1
    ");

    $deptStmt->execute([$dept_id]);

    $department = $deptStmt->fetch(PDO::FETCH_ASSOC);

    if (!$department) {
        die('Invalid department selected.');
    }

    $branch = $department['dept_name'];

    /*
    |--------------------------------------------------------------------------
    | DUPLICATE EMAIL CHECK
    |--------------------------------------------------------------------------
    */

    $checkEmail = $pdo->prepare("
        SELECT id
        FROM enrollment_queue
        WHERE email = ?
        LIMIT 1
    ");

    $checkEmail->execute([$email]);

    if ($checkEmail->fetch()) {
        die('Email already registered.');
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE STUDENT ID
    |--------------------------------------------------------------------------
    */

    $prefix = strtoupper(
        substr(
            preg_replace('/[^A-Za-z]/', '', $branch),
            0,
            3
        )
    );

    $student_id =
        date('Y') .
        $prefix .
        strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

    /*
    |--------------------------------------------------------------------------
    | PASSWORD HASH
    |--------------------------------------------------------------------------
    */

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    $profile_pic = 'default.png';

    if (
        isset($_FILES['profile_pic']) &&
        $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK
    ) {

        if ($_FILES['profile_pic']['size'] > 2 * 1024 * 1024) {
            die('Image size exceeds 2MB.');
        }

        $mime = mime_content_type(
            $_FILES['profile_pic']['tmp_name']
        );

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowed[$mime])) {
            die('Invalid image format.');
        }

        if (
            getimagesize(
                $_FILES['profile_pic']['tmp_name']
            ) === false
        ) {
            die('Invalid image file.');
        }

        $uploadDir =
            dirname(__DIR__) .
            '/uploads/profiles/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $profile_pic =
            $student_id .
            '_' .
            time() .
            '.' .
            $allowed[$mime];

        move_uploaded_file(
            $_FILES['profile_pic']['tmp_name'],
            $uploadDir . $profile_pic
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT ENROLLMENT QUEUE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO enrollment_queue
        (
            student_id,
            student_name,

            father_name,
            father_phone,

            mother_name,
            mother_phone,

            address,

            email,
            phone,

            dept_id,
            branch,

            password,
            profile_pic,

            status,
            request_date
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW()
        )
    ");

    $stmt->execute([
        $student_id,
        $full_name,

        $father_name,
        $father_phone,

        $mother_name,
        $mother_phone,

        $address,

        $email,
        $phone,

        $dept_id,
        $branch,

        $passwordHash,
        $profile_pic
    ]);

    header(
        'Location: ../public/frontend/student/student_login.php?success=1&id=' .
        urlencode($student_id)
    );

    exit;

} catch (PDOException $e) {

    error_log(
        'REGISTER_HANDLER_ERROR: ' .
        $e->getMessage()
    );

    die('Registration failed. Please try again later.');
}