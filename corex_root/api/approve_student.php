<?php
/**
 * EDULYNTRIX CORE X - ENROLLMENT APPROVAL NODE
 * Version 11.2.0: Multi-Format Data Parser
 */
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Authority Check
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hod' && $_SESSION['role'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access Protocol.']);
    exit;
}

/** * BUG FIX: DATA PARSING 
 * We check $_POST first, then fallback to JSON input to ensure the ID is captured.
 */
$queue_id = $_POST['id'] ?? null;

if (!$queue_id) {
    $json_data = json_decode(file_get_contents('php://input'), true);
    $queue_id = $json_data['id'] ?? null;
}

if (!$queue_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Node ID. Protocol requires a valid Identifier.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2. Fetch full data from Enrollment Queue
    $stmt = $pdo->prepare("SELECT * FROM enrollment_queue WHERE id = ? AND status = 'pending' LIMIT 1");
    $stmt->execute([$queue_id]);
    $temp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$temp) {
        throw new Exception("Student request node not found or already processed in the registry.");
    }

    /**
     * 3. Full Registry Integration
     * Fixed the placeholder count to match your actual execution array.
     */
    $insertSql = "INSERT INTO students (
        student_id, 
        full_name, 
        email,
        dept_id, 
        password, 
        status, 
        current_semester,
        semester,
        academic_year,
        enrollment_date
    ) VALUES (:sid, :name, :email, :did, :pass, 'Active', '1st', 1, 1, NOW())";

    $insertStmt = $pdo->prepare($insertSql);
    
    // Mapping the data from enrollment_queue to students table
    $insertStmt->execute([
        ':sid'   => $temp['student_id'],
        ':name'  => $temp['student_name'],
        ':email' => $temp['email'],
        ':did'   => $temp['dept_id'] ?? 0, // Fallback to 0 if dept_id is missing
        ':pass'  => $temp['password'] 
    ]);

    // 4. Update Queue Status to 'approved'
    $updateStmt = $pdo->prepare("UPDATE enrollment_queue SET status = 'approved' WHERE id = ?");
    $updateStmt->execute([$queue_id]);

    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Node Approved: " . htmlspecialchars($temp['student_name']) . " integrated into Core Registry."
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
}