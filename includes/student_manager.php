<?php
require_once('db_connect.php');

/**
 * EDULYNTRIX CORE X - STUDENT LOGIC CONTROLLER
 * Handles Public Registration -> HOD Queue -> Admin Registry
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. REGISTRATION HANDLER (From your existing register.html)
    if ($action == 'register') {
        $full_name = trim($_POST['full_name']);
        $email     = trim($_POST['email']);
        $dept_id   = (int)$_POST['dept_id'];
        $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            // Check if email already exists in the Nexus
            $check = $pdo->prepare("SELECT id FROM students WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->fetch()) {
                header("Location: ../register.php?error=identity_exists");
                exit();
            }

            // Insert as PENDING (Awaiting HOD Approval)
            $sql = "INSERT INTO students (full_name, email, dept_id, password, status, enrollment_date) 
                    VALUES (?, ?, ?, ?, 'Pending', CURDATE())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $email, $dept_id, $password]);

            // Redirect back to your login.html with a success message
            header("Location: ../login.html?msg=awaiting_hod_approval");
            exit();

        } catch (PDOException $e) {
            die("Nexus Ingress Failure: " . $e->getMessage());
        }
    }

    // 2. HOD APPROVAL HANDLER (The "Green Light")
    if ($action == 'approve_student') {
        $id = (int)$_POST['student_id'];
        
        // Generate the Student ID only upon approval (STU-YYYY-NNN)
        $year = date('Y');
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'");
        $next_num = $count_stmt->fetchColumn() + 1;
        $student_id = "STU-" . $year . "-" . str_pad($next_num, 3, "0", STR_PAD_LEFT);

        $update = $pdo->prepare("UPDATE students SET student_id = ?, status = 'Active' WHERE id = ?");
        $update->execute([$student_id, $id]);

        header("Location: ../corex_root/layout.php?page=hod_approvals&status=synced");
        exit();
    }
}