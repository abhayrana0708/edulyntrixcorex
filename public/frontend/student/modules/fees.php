<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../../../includes/db_connect.php'; 

$sid = $_SESSION['student_id'] ?? null;
$records = [];
$history = [];
$total_due = 0;

if ($sid) {
    try {
        // 1. Fetch Active Dues
        $stmt = $pdo->prepare("SELECT * FROM finance_records WHERE student_id = ? ORDER BY due_date ASC");
        $stmt->execute([$sid]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($records as $r) {
            $total_due += ($r['total_amount'] - $r['paid_amount']);
        }

        // 2. Fetch Payment History
        $h_stmt = $pdo->prepare("SELECT ph.*, fr.category FROM payment_history ph 
                                 JOIN finance_records fr ON ph.fee_id = fr.fee_id 
                                 WHERE ph.student_id = ? ORDER BY ph.payment_date DESC");
        $h_stmt->execute([$sid]);
        $history = $h_stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) { 
        echo "<div style='padding:20px; color:red;'>Database Error: " . $e->getMessage() . "</div>"; 
    }
}
?>

<div class="module-entrance" style="animation: nexusFadeIn 0.5s ease-out forwards;">
    
    <div style="margin-bottom:2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="color: #1e293b; font-weight: 800; letter-spacing: -0.5px;">Finance <b>Monitor</b></h2>
            <p style="color:#64748b; font-size: 0.95rem;">Real-time audit of your tuition and fees</p>
        </div>
        
        <div style="background: <?= $total_due > 0 ? '#fff1f2' : '#f0fdf4' ?>; padding: 15px 25px; border-radius: 20px; border: 1px solid <?= $total_due > 0 ? '#fecdd3' : '#bbf7d0' ?>; text-align: right;">
            <small style="display:block; color:#64748b; font-weight:700; font-size:0.65rem; text-transform:uppercase;">Total Outstanding</small>
            <span style="font-size: 1.6rem; font-weight: 900; color: <?= $total_due > 0 ? '#e11d48' : '#16a34a' ?>;">₹<?= number_format($total_due, 2) ?></span>
        </div>
    </div>

    <h4 style="color:#1e293b; margin-bottom:15px; font-weight:800; font-size:0.9rem;">Outstanding Fees</h4>
    <div style="background: white; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02); margin-bottom: 3rem;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 20px; font-size: 0.7rem; color: #64748b; font-weight: 800; text-transform: uppercase;">Category</th>
                    <th style="padding: 20px; font-size: 0.7rem; color: #64748b; font-weight: 800; text-transform: uppercase;">Amount</th>
                    <th style="padding: 20px; font-size: 0.7rem; color: #64748b; font-weight: 800; text-transform: uppercase;">Status</th>
                    <th style="padding: 20px; font-size: 0.7rem; color: #64748b; font-weight: 800; text-transform: uppercase;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($records as $row): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 20px;">
                        <span style="display:block; font-weight: 700; color: #1e293b;"><?= htmlspecialchars($row['category']) ?></span>
                        <small style="color:#94a3b8;">Due: <?= date('d M, Y', strtotime($row['due_date'])) ?></small>
                    </td>
                    <td style="padding: 20px; color: #475569; font-weight: 600;">₹<?= number_format($row['total_amount'], 2) ?></td>
                    <td style="padding: 20px;">
                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; background: <?= $row['status'] == 'Paid' ? '#dcfce7' : '#fee2e2' ?>; color: <?= $row['status'] == 'Paid' ? '#16a34a' : '#ef4444' ?>;">
                            <?= strtoupper($row['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 20px;">
                        <?php if($row['status'] !== 'Paid'): ?>
                            <button onclick="alert('Redirecting to Payment Gateway...')" style="background: #1e293b; color: white; border: none; padding: 10px 18px; border-radius: 12px; font-size: 0.75rem; cursor: pointer; font-weight: 700;">PAY NOW</button>
                        <?php else: ?>
                            <span style="color:#10b981; font-weight:700; font-size:0.8rem;">✔ SETTLED</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h4 style="color:#1e293b; margin-bottom:15px; font-weight:800; font-size:0.9rem;">Recent Transactions</h4>
    <div style="display: grid; gap: 15px;">
        <?php if(empty($history)): ?>
            <p style="text-align:center; color:#94a3b8; padding:20px;">No transaction history found.</p>
        <?php else: foreach($history as $h): ?>
            <div style="background: white; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div style="display:flex; gap:15px; align-items:center;">
                    <div style="background:#f0fdf4; color:#16a34a; padding:10px; border-radius:12px; font-weight:900;">₹</div>
                    <div>
                        <small style="color:#3b82f6; font-weight:800; font-size:0.6rem;"><?= $h['transaction_id'] ?></small>
                        <h5 style="margin:0; color:#1e293b; font-size:0.9rem;"><?= htmlspecialchars($h['category']) ?></h5>
                        <small style="color:#94a3b8;"><?= date('d M Y, h:i A', strtotime($h['payment_date'])) ?></small>
                    </div>
                </div>
                <div style="text-align:right;">
                    <span style="display:block; font-weight:800; color:#10b981;">+ ₹<?= number_format($h['amount_paid'], 2) ?></span>
                    <a href="api/generate_receipt.php?tx_id=<?= $h['transaction_id'] ?>" target="_blank" style="font-size:0.7rem; color:#3b82f6; text-decoration:none; font-weight:700;">DOWNLOAD PDF</a>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>