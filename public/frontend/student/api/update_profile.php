<?php
session_start();
header('Content-Type: application/json');
require_once '../../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['student_id'])) {
    
    $sid = (string)$_SESSION['student_id'];
    $phone   = $_POST['phone'] ?? '';
    $email   = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $new_pic_name = null;

    try {
        $pdo->beginTransaction();

        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
            $fileExtension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $new_pic_name = "STU_" . $sid . "_" . time() . "." . $fileExtension;
                
                // --- ROBUST PATH LOGIC FOR MAC/LINUX ---
                // 1. Try Absolute Path via DOCUMENT_ROOT (Most reliable on macOS)
                $abs_path = $_SERVER['DOCUMENT_ROOT'] . "/edulyntrixcorex/uploads/profiles/";
                // 2. Fallback to Relative Path
                $rel_path = "../../../../uploads/profiles/";
                
                // Determine which one to use
                $upload_dir = is_dir($abs_path) ? $abs_path : $rel_path;

                // Final safety check/creation
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $dest_path = $upload_dir . $new_pic_name;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Success: Update DB
                    $sql_pic = "UPDATE students SET profile_pic = :pic WHERE student_id = :sid";
                    $stmt_pic = $pdo->prepare($sql_pic);
                    $stmt_pic->bindValue(':pic', $new_pic_name, PDO::PARAM_STR);
                    $stmt_pic->bindValue(':sid', $sid, PDO::PARAM_STR);
                    $stmt_pic->execute();
                } else {
                    // Check specifically for Mac permissions in the error message
                    if (!is_writable($upload_dir)) {
                        throw new Exception("Permission Denied: Run 'chmod -R 777 uploads' in terminal.");
                    }
                    throw new Exception("File move failed. Check PHP upload_max_filesize in php.ini.");
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file format.']);
                exit;
            }
        }

        // Update text data
        $sql = "UPDATE students SET phone = :phone, email = :email, address = :address WHERE student_id = :sid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, PDO::PARAM_STR);
        $stmt->bindValue(':sid', $sid, PDO::PARAM_STR);
        $stmt->execute();

        $pdo->commit();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Nexus Synced Successfully.',
            'new_pic' => $new_pic_name
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(['status' => 'error', 'message' => 'Sync Failure: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access.']);
}