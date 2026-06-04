<?php
session_start();
require_once __DIR__ . '/../../../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $sid = $_POST['student_id'];
    $hod_id = $_SESSION['hod_id'] ?? 'SYSTEM';
    $dept_id = $_SESSION['dept_id'] ?? 3;
    $message = "Your attendance has dropped below the 75% threshold. Immediate improvement required.";

    try {
        $stmt = $pdo->prepare("INSERT INTO student_warnings (student_id, hod_id, dept_id, warning_message, severity) VALUES (?, ?, ?, ?, 'Critical')");
        $stmt->execute([$sid, $hod_id, $dept_id, $message]);
        
        echo json_encode(['status' => 'success', 'message' => 'Warning transmitted to Student Node.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Protocol.']);
}
?>