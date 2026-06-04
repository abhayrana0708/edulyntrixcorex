<?php
/**
 * EDULYNTRIX CORE X - ENROLLMENT QUEUE HANDLER
 * Version 8.2.1: Branch-Scoped Synchronization
 */

// 1. ISOLATE OUTPUT STREAM
ob_start(); 

header('Content-Type: application/json');

/** 2. DATABASE CONNECTIVITY **/
$db_path = __DIR__ . '/../../includes/db_connect.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Core Connectivity Missing.']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/** 3. AUTHORITY CHECK **/
// We use 'dept_name' from your staff/HOD session to filter the 'branch' column
$hod_dept = $_SESSION['dept_name'] ?? ''; 

if (empty($hod_dept)) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Security Error: No departmental clearance found in session.'
    ]);
    exit;
}

try {
    /** 4. SCOPED QUERY **/
    // Table: enrollment_queue | Filters: branch (matches dept_name) & status (pending)
    $sql = "SELECT 
                id, 
                student_name, 
                student_id, 
                branch,
                created_at 
            FROM enrollment_queue 
            WHERE status = 'pending' 
            AND LOWER(branch) = LOWER(?) 
            ORDER BY created_at ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hod_dept]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /** 5. SECURE OUTPUT **/
    if (ob_get_length()) ob_clean();
    
    echo json_encode([
        'success' => true, 
        'count' => count($data),
        'department_node' => $hod_dept,
        'data' => $data
    ]);

} catch (PDOException $e) {
    error_log("CoreX Enrollment Error: " . $e->getMessage());
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Database Sync Failure: Node Interrupted.' 
    ]);
}

ob_end_flush();
?>