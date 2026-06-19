<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../../../../includes/db_connect.php';

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['student_id'])) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized Access'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| METHOD CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid Request Method'
    ]);

    exit;
}

$student_id =
    trim($_SESSION['student_id']);

$phone =
    trim($_POST['phone'] ?? '');

$email =
    trim($_POST['email'] ?? '');

$address =
    trim($_POST['address'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (
    empty($phone) ||
    empty($email)
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Required fields missing'
    ]);

    exit;
}

if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid Email Address'
    ]);

    exit;
}

if (
    !preg_match(
        '/^[6-9][0-9]{9}$/',
        $phone
    )
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid Mobile Number'
    ]);

    exit;
}
try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | DUPLICATE EMAIL CHECK
    |--------------------------------------------------------------------------
    */

    $checkEmail = $pdo->prepare("
        SELECT student_id
        FROM students
        WHERE email = ?
        AND student_id != ?
        LIMIT 1
    ");

    $checkEmail->execute([
        $email,
        $student_id
    ]);

    if ($checkEmail->fetch()) {

        throw new Exception(
            'Email already exists.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH CURRENT PROFILE IMAGE
    |--------------------------------------------------------------------------
    */

    $currentStmt = $pdo->prepare("
        SELECT profile_pic
        FROM students
        WHERE student_id = ?
        LIMIT 1
    ");

    $currentStmt->execute([
        $student_id
    ]);

    $currentUser =
        $currentStmt->fetch(
            PDO::FETCH_ASSOC
        );

    $new_pic_name = null;

    /*
    |--------------------------------------------------------------------------
    | PROFILE IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['profile_pic']) &&
        $_FILES['profile_pic']['error']
            === UPLOAD_ERR_OK
    ) {

        if (
            $_FILES['profile_pic']['size']
            >
            (2 * 1024 * 1024)
        ) {

            throw new Exception(
                'Image exceeds 2MB limit.'
            );
        }

        $mime =
            mime_content_type(
                $_FILES['profile_pic']['tmp_name']
            );

        $allowed = [

            'image/jpeg' => 'jpg',

            'image/png'  => 'png',

            'image/webp' => 'webp'
        ];

        if (!isset($allowed[$mime])) {

            throw new Exception(
                'Invalid image format.'
            );
        }

        if (
            getimagesize(
                $_FILES['profile_pic']['tmp_name']
            ) === false
        ) {

            throw new Exception(
                'Invalid image file.'
            );
        }

        $uploadDir =
            dirname(__DIR__, 4)
            .
            '/uploads/profiles/';

        if (!is_dir($uploadDir)) {

            mkdir(
                $uploadDir,
                0755,
                true
            );
        }

        $new_pic_name =
            'STU_' .
            $student_id .
            '_' .
            time() .
            '.' .
            $allowed[$mime];

        $destination =
            $uploadDir .
            $new_pic_name;

        if (
            !move_uploaded_file(
                $_FILES['profile_pic']['tmp_name'],
                $destination
            )
        ) {

            throw new Exception(
                'Profile upload failed.'
            );
        }
                /*
        |--------------------------------------------------------------------------
        | REMOVE OLD IMAGE
        |--------------------------------------------------------------------------
        */

        if (

            !empty($currentUser['profile_pic'])

            &&

            $currentUser['profile_pic'] !== 'default.png'

            &&

            $currentUser['profile_pic'] !== 'default_avatar.png'

        ) {

            $oldFile =

                $uploadDir

                .

                $currentUser['profile_pic'];

            if (file_exists($oldFile)) {

                @unlink($oldFile);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE PROFILE PIC
        |--------------------------------------------------------------------------
        */

        $picStmt = $pdo->prepare("
            UPDATE students
            SET profile_pic = ?
            WHERE student_id = ?
        ");

        $picStmt->execute([

            $new_pic_name,

            $student_id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE DATA
    |--------------------------------------------------------------------------
    */

    $updateStmt = $pdo->prepare("
        UPDATE students
        SET

            phone = ?,
            email = ?,
            address = ?

        WHERE student_id = ?
    ");

    $updateStmt->execute([

        $phone,

        $email,

        $address,

        $student_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    $_SESSION['email'] = $email;

    if ($new_pic_name) {

        $_SESSION['profile_pic'] =
            $new_pic_name;
    }

    echo json_encode([

        'status'  => 'success',

        'message' =>
            'Profile updated successfully.',

        'new_pic' =>
            $new_pic_name
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

        'message' =>
            $e->getMessage()
    ]);
}