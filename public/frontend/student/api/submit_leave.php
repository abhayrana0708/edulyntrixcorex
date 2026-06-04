<?php
/**
 * EDULYNTRIX CORE X - STUDENT LEAVE SUBMISSION API
 * Version 7.12: Hybrid Payload Capture & Authority Sync
 */

// 1. Initialize Buffer & Headers
ob_start(); 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

// 2. Path Resolution (Climbing 4 levels to root)
$db_file = '../../../../includes/db_connect.php';

if (!file_exists($db_file)) {
    ob_clean(); 
    echo json_encode(['success' => false, 'message' => 'Critical: Database Node Path Error.']);
    exit;
}
require_once $db_file;

// 3. Security & Payload Capture
$sid     = $_SESSION['student_id'] ?? null;
$dept_id = $_SESSION['dept_id'] ?? null;

/**
 * HYBRID CAPTURE: Detects JSON or Form POST
 */
$raw_input = file_get_contents("php://input");
$input = json_decode($raw_input, true);

// If JSON decoding failed, fall back to standard $_POST
if (empty($input)) {
    $input = $_POST;
}

// 4. THE HANDSHAKE GATEKEEPER
if (!$sid || empty($input)) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Handshake Failure: ' . (!$sid ? 'Session Expired' : 'No Data Payload Received')
    ]);
    exit;
}

// 5. Data Extraction
$type    = $input['leave_type'] ?? 'Casual';
$start   = $input['start_date'] ?? '';
$end     = $input['end_date'] ?? '';
$reason  = trim($input['reason'] ?? '');

// 6. Validation
if (empty($start) || empty($end) || empty($reason)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Protocol Error: Data fields incomplete.']);
    exit;
}

/**
 * CRITICAL: If dept_id is missing from session, we attempt to 
 * fetch it once from the DB before failing (Safety Failover).
 */
if (!$dept_id && $sid) {
    $stmt = $pdo->prepare("SELECT dept_id FROM students WHERE student_id = ?");
    $stmt->execute([$sid]);
    $dept_id = $stmt->fetchColumn();
    $_SESSION['dept_id'] = $dept_id; // Re-sync session
}

if (!$dept_id) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Authority Error: Dept ID missing. Please Re-login.']);
    exit;
}

try {
    // 7. Registry Write
    $sql = "INSERT INTO leave_requests (student_id, dept_id, leave_type, start_date, end_date, reason, status, applied_on) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sid, $dept_id, $type, $start, $end, $reason]);

    ob_clean(); 
    echo json_encode([
        'success' => true, 
        'message' => 'Nexus Link Established: Request Transmitted to HOD.'
    ]);
    exit;

} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Registry Write Error: ' . $e->getMessage()]);
    exit;
}