<?php
session_start();
require_once '../../../includes/db_connect.php';

echo "<h2>CoreX Leave System Debugger</h2>";

// 1. Check Session
echo "<b>1. Session Check:</b> ";
if (isset($_SESSION['student_id'])) {
    echo "<span style='color:green;'>Active (ID: " . $_SESSION['student_id'] . ")</span>";
} else {
    echo "<span style='color:red;'>Inactive! (You need to login)</span>";
}

// 2. Check Table Existence
echo "<br><b>2. Table Check:</b> ";
try {
    $pdo->query("SELECT 1 FROM leave_requests LIMIT 1");
    echo "<span style='color:green;'>'leave_requests' table exists.</span>";
} catch (Exception $e) {
    echo "<span style='color:red;'>Table missing! Error: " . $e->getMessage() . "</span>";
}

// 3. Check Data for current ID
if (isset($_SESSION['student_id'])) {
    echo "<br><b>3. Data Check:</b> ";
    $sid = $_SESSION['student_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leave_requests WHERE student_id = ?");
    $stmt->execute([$sid]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "<span style='color:green;'>Found $count records for $sid.</span>";
    } else {
        echo "<span style='color:orange;'>Table is empty for ID $sid.</span>";
    }
}
?>