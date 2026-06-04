<?php
/**
 * EDULYNTRIX CORE X - STAFF NOTIFICATIONS
 * Theme: Indigo Frost (Deep Indigo + Crystal White)
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/** 1. DATABASE UPLINK **/
// Absolute path verified from EdulyntrixCoreX root
$db_path = realpath(__DIR__ . '/../../../../includes/db_connect.php');
if (!$db_path || !file_exists($db_path)) {
    die("<div style='padding:30px; background:#fff1f2; border:1px solid #fda4af; border-radius:15px; color:#be123c; font-family:sans-serif;'>
            <b style='display:block; margin-bottom:10px;'>CORE_UPLINK_FATAL:</b> 
            The database connector could not be located at: $db_path
         </div>");
}
require_once $db_path;

/** 2. IDENTITY SYNC LAYER **/
// Captures either the numeric 'id' or the alphanumeric 'staff_id' from session
$raw_session_id = $_SESSION['staff_id'] ?? $_SESSION['user_id'] ?? $_SESSION['login_id'] ?? '';
$true_staff_id = '';
$dept_name = '';

try {
    // Cross-reference session data with the staff table to get the proper STF-XXXX string
    $id_stmt = $pdo->prepare("
        SELECT staff_id, department 
        FROM staff 
        WHERE id = ? OR staff_id = ? OR login_id = ? 
        LIMIT 1
    ");
    $id_stmt->execute([$raw_session_id, $raw_session_id, $raw_session_id]);
    $faculty = $id_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($faculty) {
        $true_staff_id = $faculty['staff_id'];
        $dept_name = $faculty['department'];
    } else {
        $true_staff_id = $raw_session_id; // Fallback to raw session if no match found
    }

    /** 3. DATA ACQUISITION **/
    // Fetch only the most recent activity for this specific staff member
    $stmt = $pdo->prepare("
        SELECT 
            'Fine Issued' as type, 
            description as msg, 
            created_at, 
            student_id,
            total_amount
        FROM disciplinary_fines 
        WHERE staff_id = ? 
        ORDER BY created_at DESC 
        LIMIT 6
    ");
    $stmt->execute([$true_staff_id]);
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $alerts = []; // Fail silently to maintain UI integrity
}
?>

<div class="notifications-container" style="padding: 10px; animation: slideInRight 0.4s ease-out;">
    <style>
        @keyframes slideInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        .notif-card { 
            background: #ffffff; 
            border-left: 4px solid #6366f1; 
            padding: 15px; 
            margin-bottom: 12px; 
            border-radius: 12px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: 0.3s;
        }
        .notif-card:hover { transform: scale(1.01); background: #fefeff; border-left-color: #4338ca; }
        .notif-badge { 
            font-weight: 800; 
            font-size: 0.65rem; 
            color: #6366f1; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 5px;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3 style="color: #1e1b4b; font-size: 1.1rem; font-weight: 800; letter-spacing: -0.5px; margin: 0;">Live Activity</h3>
            <p style="font-size: 0.7rem; color: #94a3b8; margin: 2px 0 0 0;">Monitoring: <?= htmlspecialchars($true_staff_id) ?></p>
        </div>
        <span style="font-size: 0.7rem; background: #eef2ff; color: #6366f1; padding: 4px 10px; border-radius: 20px; font-weight: 700;">
            <?= count($alerts) ?> Recent
        </span>
    </div>

    <?php if (empty($alerts)): ?>
        <div style="text-align: center; padding: 50px 20px; background: #f8fafc; border-radius: 16px; border: 1px dashed #e2e8f0;">
            <i class="fa-solid fa-bell-slash" style="font-size: 1.5rem; color: #cbd5e1; margin-bottom: 10px;"></i>
            <p style="color: #94a3b8; font-size: 0.85rem; margin: 0;">No system alerts recorded for your account yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($alerts as $alert): ?>
            <div class="notif-card">
                <span class="notif-badge"><?= $alert['type'] ?></span>
                <div style="font-size: 0.85rem; color: #1e293b; font-weight: 500; line-height: 1.4;">
                    Issued <b>₹<?= number_format($alert['total_amount'], 2) ?></b> to 
                    <span style="color: #4338ca;"><?= htmlspecialchars($alert['student_id']) ?></span>: 
                    <?= htmlspecialchars($alert['msg']) ?>
                </div>
                <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 10px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-regular fa-clock"></i>
                    <?= date('M d • h:i A', strtotime($alert['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>