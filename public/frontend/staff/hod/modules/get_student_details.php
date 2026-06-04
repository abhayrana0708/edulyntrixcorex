<?php
session_start();
// Use absolute pathing for XAMPP to avoid "File Not Found" errors
$db_path = $_SERVER['DOCUMENT_ROOT'] . '/EdulyntrixCoreX/includes/db_connect.php';

if (!file_exists($db_path)) {
    die("<div style='color:#f87171; padding:20px;'>SYSTEM ERROR: Database connection logic not found at $db_path</div>");
}

require_once $db_path;

$sid = $_GET['student_id'] ?? '';

if (empty($sid)) {
    die("<div style='color:#f87171; padding:20px;'>SECURITY ERROR: Student ID missing from request.</div>");
}

try {
    // UPDATED: Changed 'a.date' to 'a.attendance_date' to match your schema
    $stmt = $pdo->prepare("SELECT a.attendance_date, a.status, s.subject_name 
                           FROM attendance a 
                           JOIN subjects s ON a.subject_id = s.subject_id 
                           WHERE a.student_id = ? 
                           ORDER BY a.attendance_date DESC LIMIT 30");
    $stmt->execute([$sid]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$logs) {
        echo "<div style='color:#94a3b8; padding:40px; text-align:center;'>
                <i class='fa-solid fa-database' style='display:block; font-size:2rem; margin-bottom:15px; opacity:0.3;'></i>
                No historical logs found for Target: <b>$sid</b>
              </div>";
        exit;
    }

    echo '<table style="width:100%; color:#fff; border-collapse:collapse; font-size:0.85rem; font-family:\'Inter\', sans-serif;">';
    echo '<tr style="background: rgba(255,255,255,0.03); color:#64748b; text-align:left;">
            <th style="padding:15px; border-bottom:1px solid rgba(255,255,255,0.1);">SESSION DATE</th>
            <th style="padding:15px; border-bottom:1px solid rgba(255,255,255,0.1);">SUBJECT NODE</th>
            <th style="padding:15px; border-bottom:1px solid rgba(255,255,255,0.1);">STATUS</th>
          </tr>';
          
    foreach ($logs as $log) {
        $status = strtoupper($log['status']);
        // Color mapping for "Supreme Power" theme
        $color = ($status == 'PRESENT') ? '#10b981' : (($status == 'LATE') ? '#f59e0b' : '#f87171');
        $bg = ($status == 'PRESENT') ? 'rgba(16, 185, 129, 0.05)' : 'rgba(248, 113, 113, 0.05)';
        
        echo "<tr style='border-bottom:1px solid rgba(255,255,255,0.03); transition:0.2s;'>
                <td style='padding:12px 15px; color:#cbd5e1;'>".date('d M, Y', strtotime($log['attendance_date']))."</td>
                <td style='padding:12px 15px; font-weight:600; color:#fff;'>".htmlspecialchars($log['subject_name'])."</td>
                <td style='padding:12px 15px;'>
                    <span style='color:$color; background:$bg; padding:4px 10px; border-radius:6px; font-size:0.7rem; font-weight:800; border:1px solid $color;'>
                        $status
                    </span>
                </td>
              </tr>";
    }
    echo '</table>';

} catch (PDOException $e) {
    echo "<div style='color:#f87171; padding:20px;'>
            DATABASE CRITICAL: " . htmlspecialchars($e->getMessage()) . "
          </div>";
}