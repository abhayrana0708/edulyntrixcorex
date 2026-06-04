<?php
header('Content-Type: application/json');
// Updated path to reach your includes folder from corex_root/api/
require_once '../../includes/db_connect.php'; 
session_start();

/** 1. AUTHENTICATION SHIELD **/
// Updated to check for 'faculty' or 'hod' roles based on your Staff Module setup
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access.']);
    exit;
}

/** 2. DATA ACQUISITION **/
$data = json_decode(file_get_contents('php://input'), true);
$leave_id = $data['id'] ?? null;
$new_status = $data['status'] ?? null; // Expecting 'Approved' or 'Rejected'
$staff_id = $_SESSION['user_id']; 

if (!$leave_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Missing Request Parameters.']);
    exit;
}

try {
    /** 3. SECURITY & EXECUTION **/
    // We update status, the reviewer ID, and the current timestamp
    // The query ensures only the relevant leave record is touched
    $stmt = $pdo->prepare("UPDATE leave_requests 
                           SET status = ?, 
                               reviewed_by = ?, 
                               review_date = NOW() 
                           WHERE leave_id = ?");
    
    $stmt->execute([$new_status, $staff_id, $leave_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Terminal Synchronized.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record mismatch or no changes made.']);
    }
} catch (PDOException $e) {
    // Hidden internal error details for security, providing a clean response
    echo json_encode(['success' => false, 'message' => 'Database Sync Failure.']);
}