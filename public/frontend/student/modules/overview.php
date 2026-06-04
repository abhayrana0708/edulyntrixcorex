<?php
/**
 * EDULYNTRIX CORE X - STUDENT OVERVIEW
 * Theme: Nexus Light (Pure White + Soft Blue)
 * Update: Integrated 'fine_description' notifications
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../../../includes/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    die("<div style='padding:20px; color:#ef4444;'>Session Expired. Please Re-login.</div>");
}

$sid = $_SESSION['student_id'];
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : 1;

try {
    // 1. Fetch Student Profile Data
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$sid]);
    $student = $stmt->fetch();

    if (!$student) {
        die("<div style='padding:20px; color:#ef4444;'>Student Record Not Found.</div>");
    }

    // 2. FETCH ACTIVE WARNINGS (HOD Logic)
    $warnStmt = $pdo->prepare("SELECT warning_message, created_at FROM student_warnings 
                               WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
    $warnStmt->execute([$sid]);
    $hod_warning = $warnStmt->fetch();

    // 3. NEW: FETCH RECENT UNPAID FINES (Financial Logic)
    $fineStmt = $pdo->prepare("SELECT fine_description, total_amount, due_date 
                               FROM finance_records 
                               WHERE student_id = ? AND category = 'Disciplinary Fine' AND status != 'Paid' 
                               ORDER BY due_date DESC LIMIT 1");
    $fineStmt->execute([$sid]);
    $active_fine = $fineStmt->fetch();

    // 4. Calculate Real Attendance Percentage
    $attStmt = $pdo->prepare("SELECT 
        COUNT(*) as total, 
        SUM(CASE WHEN LOWER(status) = 'present' OR LOWER(status) = 'late' THEN 1 ELSE 0 END) as present 
        FROM attendance WHERE student_id = ? AND academic_year = ?");
    $attStmt->execute([$sid, $selected_year]);
    $attData = $attStmt->fetch();
    
    $attendance_pct = ($attData['total'] > 0) 
        ? round(($attData['present'] / $attData['total']) * 100, 1) 
        : 0;

    // 5. Fetch the Most Recent Payment
    $latestPaymentStmt = $pdo->prepare("SELECT p.amount_paid, p.payment_date, f.category 
                                        FROM payment_history p 
                                        JOIN finance_records f ON p.fee_id = f.fee_id 
                                        WHERE p.student_id = ? 
                                        ORDER BY p.payment_date DESC LIMIT 1");
    $latestPaymentStmt->execute([$sid]);
    $lastPayment = $latestPaymentStmt->fetch();

    // Variables for Progress
    $current_gpa = $student['cgpa'] ?? '0.00';
    $earned_credits = $student['credits_earned'] ?? 0;
    $total_required = 160;
    $credit_progress = ($earned_credits / $total_required) * 100;

} catch (PDOException $e) {
    die("<div style='padding:20px; color:#ef4444;'>CoreX Sync Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<div class="module-entrance" style="animation: nexusFadeIn 0.5s ease-out forwards;">
    
    <div style="margin-bottom:2rem; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 style="color: #1e293b; font-weight: 800; letter-spacing: -0.5px;">Academic <b>Progress</b></h2>
            <p style="color:#64748b; font-size: 0.95rem;">Reviewing records for <b>Year 0<?= $selected_year ?></b></p>
        </div>
        <div style="text-align: right;">
            <span style="background: <?= (strtolower($student['status'] ?? '') == 'active') ? '#ecfdf5' : '#fff7ed' ?>; 
                         color: <?= (strtolower($student['status'] ?? '') == 'active') ? '#10b981' : '#f59e0b' ?>; 
                         padding: 8px 16px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; border: 1px solid currentColor;">
                ● <?= strtoupper($student['status'] ?? 'PENDING') ?>
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:20px; margin-bottom: 2rem;">
        <div style="background: white; padding:25px; border-radius:24px; border:1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <div style="width: 8px; height: 8px; background: <?= $attendance_pct < 75 ? '#f87171' : '#10b981' ?>; border-radius: 50%;"></div>
                <small style="color:#64748b; font-weight: 700; letter-spacing: 0.5px; font-size: 0.7rem;">ATTENDANCE</small>
            </div>
            <h3 style="font-size: 2rem; color: #1e293b; font-weight: 800;"><?= $attendance_pct ?>%</h3>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 10px; margin-top: 15px; overflow: hidden;">
                <div style="width: <?= $attendance_pct ?>%; height: 100%; background: <?= $attendance_pct < 75 ? '#f87171' : '#10b981' ?>; border-radius: 10px;"></div>
            </div>
        </div>

        <div style="background: white; padding:25px; border-radius:24px; border:1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <div style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%;"></div>
                <small style="color:#64748b; font-weight: 700; font-size: 0.7rem;">CURRENT CGPA</small>
            </div>
            <h3 style="font-size: 2rem; color: #1e293b; font-weight: 800;"><?= number_format($current_gpa, 2) ?></h3>
            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 10px;">Semester <?= $student['current_semester'] ?? 'N/A' ?></p>
        </div>

        <div style="background: white; padding:25px; border-radius:24px; border:1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <div style="width: 8px; height: 8px; background: #6366f1; border-radius: 50%;"></div>
                <small style="color:#64748b; font-weight: 700; font-size: 0.7rem;">CREDITS EARNED</small>
            </div>
            <h3 style="font-size: 2rem; color: #1e293b; font-weight: 800;"><?= $earned_credits ?><span style="font-size: 1rem; color: #cbd5e1; font-weight: 400;"> / <?= $total_required ?></span></h3>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 10px; margin-top: 15px; overflow: hidden;">
                <div style="width: <?= min($credit_progress, 100) ?>%; height: 100%; background: #6366f1;"></div>
            </div>
        </div>
    </div>

    <!-- Notification Feed -->
    <div style="background: #f8fafc; padding:30px; border-radius:24px; border:1px solid #e2e8f0;">
        <h4 style="color:#1e293b; margin-bottom:15px; font-weight: 800; font-size: 1rem;">Nexus Hub Notifications</h4>
        <ul style="list-style: none; padding: 0; margin: 0;">
            
            <!-- NEW: Fine Alert Notification -->
            <?php if($active_fine): ?>
            <li style="padding: 18px; background: #fff7ed; border-radius: 16px; margin-bottom: 12px; border: 1px solid #ffedd5; font-size: 0.9rem; color: #9a3412; display: flex; align-items: flex-start; gap: 15px;">
                <div style="background: #f97316; padding: 8px; border-radius: 10px; color: white; font-weight: bold;">₹</div>
                <div>
                    <span style="display: block; font-weight: 800; margin-bottom: 4px;">Pending Fine: ₹<?= number_format($active_fine['total_amount'], 2) ?></span>
                    <span style="color: #c2410c;"><?= htmlspecialchars($active_fine['fine_description']) ?></span>
                    <small style="display: block; margin-top: 8px; opacity: 0.7; font-weight: 700;">DUE BY: <?= date('d M, Y', strtotime($active_fine['due_date'])) ?></small>
                </div>
            </li>
            <?php endif; ?>

            <?php if($hod_warning): ?>
            <li style="padding: 15px; background: #fff1f2; border-radius: 12px; margin-bottom: 12px; border: 1px solid #fecdd3; font-size: 0.9rem; color: #9f1239; display: flex; align-items: center; gap: 15px;">
                <div style="background: #fb7185; padding: 8px; border-radius: 10px; color: white;">⚠</div>
                <span><b>Academic Alert:</b> <?= htmlspecialchars($hod_warning['warning_message']) ?></span>
            </li>
            <?php endif; ?>

            <?php if($lastPayment): ?>
            <li style="padding: 15px 0; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; color: #475569; display: flex; align-items: center; gap: 15px;">
                <div style="background: #f0fdf4; padding: 8px; border-radius: 10px; color: #16a34a; font-weight: 900; min-width: 32px; text-align: center;">₹</div>
                <span>Recent Payment: <b>₹<?= number_format($lastPayment['amount_paid'], 2) ?></b> for <b><?= htmlspecialchars($lastPayment['category']) ?></b> on <?= date('d M', strtotime($lastPayment['payment_date'])) ?>.</span>
            </li>
            <?php endif; ?>

            <li style="padding: 15px 0; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; color: #475569; display: flex; align-items: center; gap: 15px;">
                <div style="background: #dcfce7; padding: 8px; border-radius: 10px; color: #10b981;">✔</div>
                <span>Your registration for <b>Semester <?= $student['current_semester'] ?? 'N/A' ?></b> has been verified.</span>
            </li>
        </ul>
    </div>
</div>