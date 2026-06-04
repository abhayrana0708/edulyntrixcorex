<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Secure Uplink: Exit 4 levels to root
require_once __DIR__ . '/../../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrollment = $_POST['enrollment_no'] ?? '';
    $email = $_POST['email'] ?? '';

    try {
        // Verify credentials against student registry
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ? AND email = ? LIMIT 1");
        $stmt->execute([$enrollment, $email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => true, 'message' => 'Token dispatched to ' . htmlspecialchars($email)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No matching records found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
    exit;
}