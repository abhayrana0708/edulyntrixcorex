<?php
session_start();
require_once '../../../../includes/db_connect.php';

$tx_id = $_GET['tx_id'] ?? null;
$sid = $_SESSION['student_id'] ?? null;

if (!$tx_id || !$sid) die("Invalid Request.");

$stmt = $pdo->prepare("SELECT ph.*, fr.category, s.full_name FROM payment_history ph 
                       JOIN finance_records fr ON ph.fee_id = fr.fee_id 
                       JOIN students s ON ph.student_id = s.student_id 
                       WHERE ph.transaction_id = ? AND ph.student_id = ?");
$stmt->execute([$tx_id, $sid]);
$data = $stmt->fetch();

if (!$data) die("Transaction not found.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt_<?= $tx_id ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 50px; color: #1e293b; background: #f8fafc; }
        .receipt-card { background: white; max-width: 600px; margin: auto; padding: 40px; border-radius: 20px; box-shadow: 0 10px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .header { text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 0.95rem; }
        .label { color: #64748b; font-weight: 600; }
        .value { color: #1e293b; font-weight: 800; }
        .total-box { background: #f8fafc; padding: 20px; border-radius: 12px; margin-top: 30px; text-align: center; border: 1px dashed #cbd5e1; }
        .btn-print { margin-top: 30px; width: 100%; padding: 15px; background: #1e293b; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>
    <div class="receipt-card">
        <div class="header">
            <h1 style="margin:0; font-size:1.5rem;">EDULYNTRIX COREX</h1>
            <p style="margin:5px 0; color:#64748b;">Official Payment Acknowledgment</p>
        </div>
        <div class="row"><span class="label">Transaction ID</span><span class="value"><?= $data['transaction_id'] ?></span></div>
        <div class="row"><span class="label">Student Name</span><span class="value"><?= $data['full_name'] ?></span></div>
        <div class="row"><span class="label">Fee Category</span><span class="value"><?= $data['category'] ?></span></div>
        <div class="row"><span class="label">Date & Time</span><span class="value"><?= date('d M Y, h:i A', strtotime($data['payment_date'])) ?></span></div>
        
        <div class="total-box">
            <small style="color:#64748b; font-weight:700;">AMOUNT PAID</small>
            <div style="font-size: 2rem; color: #10b981; font-weight: 900;">₹<?= number_format($data['amount_paid'], 2) ?></div>
        </div>

        <button class="btn-print" onclick="window.print()">PRINT RECEIPT</button>
    </div>
</body>
</html>