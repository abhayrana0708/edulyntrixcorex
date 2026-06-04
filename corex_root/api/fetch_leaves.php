<?php
/**
 * EDULYNTRIX CORE X - HOD LEAVE SYNCHRONIZER
 * Version 7.9.2: Table-Aligned Mapping
 */

header('Content-Type: application/json');

/** 1. DATABASE CONNECTIVITY **/
// Corrected absolute path for Mac XAMPP
$db_path = __DIR__ . '/../../includes/db_connect.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    echo json_encode(['success' => false, 'message' => 'Critical: Database Node Offline.']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/** 2. RESOLVE HOD AUTHORITY **/
// Dashboard.php stores dept in 'dept_name', while login might use 'department'
$hod_dept_session = $_SESSION['dept_name'] ?? $_SESSION['department'] ?? 'Unknown';
$hod_dept_id = 0; 

// Normalize for strict matching
$dept_normalized = strtolower(trim($hod_dept_session));

/** * MAPPING LOGIC 
 * Strictly aligned with your provided Department Table IDs
 */
if (str_contains($dept_normalized, 'computer')) {
    $hod_dept_id = 1; // Computer Science
} elseif (str_contains($dept_normalized, 'information') || str_contains($dept_normalized, 'it')) {
    $hod_dept_id = 2; // Information Technology
} elseif (str_contains($dept_normalized, 'mechanical')) {
    $hod_dept_id = 3; // Mechanical Engineering
} elseif (str_contains($dept_normalized, 'electrical')) {
    $hod_dept_id = 4; // Electrical Engineering
}

// Security Gate: Prevent unauthorized data access
if ($hod_dept_id === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Access Denied: Unrecognized Department.',
        'debug' => "System received: " . $hod_dept_session
    ]);
    exit;
}

try {
    /** 3. THE ISOLATED QUERY **/
    $sql = "SELECT 
                l.request_id, 
                l.student_id, 
                l.leave_type, 
                l.start_date, 
                l.end_date, 
                l.reason, 
                l.status, 
                l.applied_on,
                s.full_name 
            FROM leave_requests l
            INNER JOIN students s ON l.student_id = s.student_id
            WHERE l.dept_id = :did 
            AND l.status = 'Pending'
            ORDER BY l.applied_on DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':did' => $hod_dept_id]);
    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /** 4. SUPREME OUTPUT CLEANING **/
    if (ob_get_length()) ob_clean(); 
    
    echo json_encode([
        'success' => true, 
        'data' => $leaves,
        'meta' => [
            'active_dept' => $hod_dept_session,
            'mapped_id' => $hod_dept_id,
            'count' => count($leaves)
        ]
    ]);

} catch (PDOException $e) {
    error_log("EDULYNTRIX_LOG: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Registry Sync Failure: Connection to Leave Node Interrupted.'
    ]);
}