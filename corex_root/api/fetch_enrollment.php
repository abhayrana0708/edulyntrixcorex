<?php
// 1. Initialize Session FIRST (Crucial for accessing $_SESSION)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 2. Database Connection
// Ensure this path is correct relative to this API file
require_once '../../includes/db_connect.php';

/**
 * 3. AUTHORITY CHECK
 * We pull the department assigned during the login process.
 */
$hod_dept = $_SESSION['department'] ?? ''; 

if (empty($hod_dept)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Security Error: No departmental clearance found in session.'
    ]);
    exit;
}

try {
    /**
     * 4. SCOPED QUERY
     * We filter by 'pending' AND the specific 'branch' of the HOD.
     */
    $sql = "SELECT id, student_name, student_id, branch 
            FROM enrollment_queue 
            WHERE status = 'pending' AND branch = ? 
            ORDER BY created_at ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hod_dept]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Secure Output
    echo json_encode([
        'success' => true, 
        'count' => count($data),
        'department_node' => $hod_dept,
        'data' => $data
    ]);

} catch (PDOException $e) {
    // Log error internally, show generic message to user
    error_log("CoreX DB Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'CoreX Node Sync Failure.'
    ]);
}
?>