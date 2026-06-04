<?php
/**
 * EDULYNTRIX CORE X - NEXUS LIGHT ACKNOWLEDGEMENT NODE
 * Clears active warnings for the current student session.
 */
session_start();
require_once '../../../includes/db_connect.php';

// Auth Check
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit();
}

$student_id = (string)$_SESSION['student_id'];

try {
    // Set is_read to 1 for all unread warnings for this student
    $stmt = $pdo->prepare("UPDATE student_warnings SET is_read = 1 WHERE student_id = ? AND is_read = 0");
    $stmt->execute([$student_id]);

    echo json_encode(['status' => 'success', 'message' => 'Node Cleared']);
} catch (PDOException $e) {
    error_log("ACK Error: " . $e->getMessage());
    echo json_encode(['status' => 'error']);
}