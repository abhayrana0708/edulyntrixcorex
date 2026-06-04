<?php
/**
 * EDULYNTRIX CORE X - ENROLLMENT REJECTION NODE
 * Version 11.2.5: Secure Branch-Scoped Termination
 */
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/** 1. SUPREME AUTHORITY VALIDATION **/
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hod' && $_SESSION['role'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'CRITICAL: Unauthorized Access Protocol.']);
    exit;
}

/** 2. DATA ACQUISITION & BUG FIX **/
// We check $_POST first (sent by your dashboard) and fallback to JSON input
$queue_id = $_POST['id'] ?? null;
if (!$queue_id) {
    $json_data = json_decode(file_get_contents('php://input'), true);
    $queue_id = $json_data['id'] ?? null;
}

$hod_dept = isset($_SESSION['dept_name']) ? trim($_SESSION['dept_name']) : null;

if (!$queue_id || !$hod_dept) {
    echo json_encode([
        'success' => false, 
        'message' => 'PROTOCOL ERROR: Node ID or Department Identity Missing.'
    ]);
    exit;
}

try {
    /** 3. SECURITY SYNC: SCOPED UPDATE 
     * We use LOWER(TRIM()) to ensure the HOD's session dept matches the DB branch exactly.
     **/
    $sql = "UPDATE enrollment_queue 
            SET status = 'rejected' 
            WHERE id = ? 
            AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) 
            AND status = 'pending'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$queue_id, $hod_dept]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'PROTOCOL SUCCESS: Enrollment Node Rejected & Archived.'
        ]);
    } else {
        /** * If rowCount is 0, it means:
         * 1. The ID doesn't exist.
         * 2. The ID is already approved/rejected.
         * 3. The branch name in the DB doesn't match the HOD's session dept.
         **/
        echo json_encode([
            'success' => false, 
            'message' => 'SECURITY ALERT: Node not found, already processed, or unauthorized branch access.'
        ]);
    }

} catch (PDOException $e) {
    error_log("CoreX Reject Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'SYSTEM ERROR: Database node unresponsive.'
    ]);
}
?>