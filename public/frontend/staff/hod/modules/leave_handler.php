<?php
/**
 * EDULYNTRIX CORE X - LEAVE HANDLER (SILK)
 */
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = $_POST['leave_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $authorized_by = $_SESSION['user_id'];

    if (!$leave_id || !in_array($status, ['Approved', 'Rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Request Parameters']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE leave_requests SET status = ?, reviewed_by = ?, review_date = NOW() WHERE leave_id = ?");
        $stmt->execute([$status, $authorized_by, $leave_id]);

        echo json_encode(['status' => 'success', 'message' => "Request $status"]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}