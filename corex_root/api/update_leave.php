<?php
/**
 * EDULYNTRIX CORE X - LEAVE RESOLUTION NODE
 * Version 7.1: Strict Stream Isolation & Payload Correction
 */

// 1. ISOLATE OUTPUT STREAM
ob_start(); 

/** 2. DATABASE CONNECTIVITY **/
// Path calibrated to root structure: /corex_root/api/ -> /includes/
$db_path = __DIR__ . '/../../includes/db_connect.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'System Path Error: Core Connectivity Missing.']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/** 3. SECURITY & HEADERS **/
header('Content-Type: application/json');

// Check for HOD or Admin clearance
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'hod' && $_SESSION['role'] !== 'admin')) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Security Breach: Unauthorized Authority.']);
    exit;
}

/** 4. DATA ACQUISITION **/
// Support for both Application/X-WWW-Form-Urlencoded and Application/JSON
$request_id = $_POST['request_id'] ?? null;
$status     = $_POST['status'] ?? null;

if (!$request_id) {
    $input = json_decode(file_get_contents("php://input"), true);
    $request_id = $input['request_id'] ?? ($input['id'] ?? null); // Handle both 'request_id' and 'id'
    $status     = $input['status'] ?? null;
}

/** 5. VALIDATION **/
if (!$request_id || !$status) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Protocol Error: Incomplete Payload.']);
    exit;
}

try {
    /** 6. EXECUTION LOGIC **/
    // Standardize status: Ensure it matches 'Approved' or 'Rejected' exactly as per DB
    $finalStatus = ucfirst(strtolower(trim($status)));
    
    // Explicitly target 'Pending' to prevent re-processing and verify HOD department scope
    // We check dept_id to ensure HOD isn't approving a student from another branch
    $hod_dept_id = $_SESSION['dept_id'] ?? 0;

    $sql = "UPDATE leave_requests 
            SET status = :status 
            WHERE request_id = :id 
            AND status = 'Pending'
            AND dept_id = :did";
            
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':status', $finalStatus, PDO::PARAM_STR);
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->bindParam(':did', $hod_dept_id, PDO::PARAM_INT);
    
    $stmt->execute();

    /** 7. VERIFICATION **/
    if (ob_get_length()) ob_clean(); 
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => "Registry Synchronized: Request #{$request_id} set to {$finalStatus}."
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Update Skipped: Application already processed or department mismatch.'
        ]);
    }

} catch (PDOException $e) {
    error_log("EDULYNTRIX_CORE_ERROR: " . $e->getMessage());
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Registry Write Failure: Database Engine Error.'
    ]);
}

// 8. FINAL FLUSH
ob_end_flush();