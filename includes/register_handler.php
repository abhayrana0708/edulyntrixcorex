<?php
/**
 * EDULYNTRIX CORE X - ENROLLMENT HANDLER
 * Redirects data to Queue for HOD Approval
 */
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. COLLECT DATA
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $dept_id   = $_POST['dept_id'];
    $branch    = $_POST['branch_name']; // Captured via JS from the dropdown text
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Generate Student ID (Format: 2026 + DEPT_PREFIX + RANDOM)
    $student_id = "2026" . strtoupper(substr($branch, 0, 3)) . rand(100, 999);

    // 2. IMAGE UPLOAD LOGIC
    $profile_pic = "default_avatar.png";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $profile_pic = $student_id . "_" . time() . "." . $ext;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], "../assets/img/profiles/" . $profile_pic);
    }

    try {
        /** * THE CORE FIX: INSERT INTO QUEUE, NOT STUDENTS **/
        $sql = "INSERT INTO enrollment_queue (
                    student_id, 
                    student_name, 
                    email, 
                    phone,
                    dept_id, 
                    branch, 
                    password, 
                    profile_pic,
                    status, 
                    request_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $student_id,
            $full_name,
            $email,
            $phone,
            $dept_id,
            $branch,
            $password,
            $profile_pic
        ]);

        // Success: Redirect to a status page
        echo "<script>
                alert('Success! Your enrollment is pending HOD approval.');
                window.location.href = '../public/frontend/student/student_login.php';
              </script>";

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            die("ERROR: This email or ID is already in the queue.");
        }
        die("SYSTEM_FAILURE: " . $e->getMessage());
    }
}