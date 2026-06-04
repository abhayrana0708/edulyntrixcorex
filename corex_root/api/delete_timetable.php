<?php
/**
 * EDULYNTRIX CORE X - TIMETABLE TERMINATOR API
 * Purpose: Safely removes a schedule node from the timeline.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');

// 1. DYNAMIC PATH VERIFICATION
$db_path = '../../includes/db_connect.php';
if (!file_exists($db_path)) {
    echo json_encode(['status' => 'error', 'message' => 'SYSTEM_PATH_FAILURE: db_connect unreachable']);
    exit;
}
require_once $db_path;

// 2. PROTOCOL & PAYLOAD VALIDATION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capture ID and ensure it is a valid integer
    $node_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$node_id) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'MALFORMED_REQUEST: Valid Node ID required.'
        ]);
        exit;
    }

    try {
        // 3. EXECUTE TERMINATION
        $stmt = $pdo->prepare("DELETE FROM timetable WHERE id = ? LIMIT 1");
        $stmt->execute([$node_id]);

        // 4. VERIFY DELETION SUCCESS
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status'  => 'success',
                'message' => "Node #{$node_id} terminated successfully."
            ]);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'NODE_NOT_FOUND: The target node may have already been removed.'
            ]);
        }

    } catch (PDOException $e) {
        // Log error internally, return generic message to frontend for security
        error_log("Timetable Delete Error: " . $e->getMessage());
        echo json_encode([
            'status'  => 'error', 
            'message' => 'DATABASE_SYNC_FAILURE: Unable to reach core.'
        ]);
    }
} else {
    // Handle invalid request methods
    http_response_code(405);
    echo json_encode([
        'status'  => 'error', 
        'message' => 'INVALID_PROTOCOL: Only POST requests are permitted.'
    ]);
}