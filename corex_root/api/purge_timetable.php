<?php
/**
 * EDULYNTRIX CORE X - BATCH PURGE API
 * Logic: Terminate all schedule nodes for the HOD's specific department.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. SET HEADER FOR JSON RESPONSE
header('Content-Type: application/json');

// 2. CORE CONNECTION
$db_path = $_SERVER['DOCUMENT_ROOT'] . '/edulyntrixcorex/includes/db_connect.php';
if (file_exists($db_path)) {
    require_once $db_path;
} else {
    echo json_encode(['status' => 'error', 'message' => 'DB_PATH_NOT_FOUND']);
    exit;
}

// 3. AUTHENTICATION & DEPT CONTEXT CHECK
$dept_id = $_SESSION['dept_id'] ?? 0;

if ($dept_id > 0) {
    try {
        // 4. EXECUTE BATCH DELETE
        // This targets only the HOD's department for safety
        $stmt = $pdo->prepare("DELETE FROM timetable WHERE dept_id = ?");
        $result = $stmt->execute([$dept_id]);

        if ($result) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'TIMELINE_DELETED',
                'affected_nodes' => $stmt->rowCount()
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'PURGE_EXECUTION_FAILED']);
        }

    } catch (PDOException $e) {
        error_log("Purge Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'DATABASE_SYNC_FAILURE']);
    }
} else {
    // Prevent unauthorized or unauthenticated requests
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'UNAUTHORIZED_ACCESS']);
}