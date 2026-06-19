
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../../../includes/db_connect.php';

$sid = $_SESSION['student_id'] ?? null;

$records = [];
$history = [];
$total_due = 0;

if ($sid) {

    try {

        /*
        |--------------------------------------------------------------------------
        | ACTIVE FINANCE RECORDS
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT *
            FROM finance_records
            WHERE student_id = ?
            ORDER BY due_date ASC
        ");

        $stmt->execute([$sid]);

        $records =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        foreach ($records as $record) {

            $balance =
                max(
                    0,
                    (
                        $record['total_amount']
                        -
                        $record['paid_amount']
                    )
                );

            $total_due += $balance;
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENT HISTORY
        |--------------------------------------------------------------------------
        */

        $historyStmt = $pdo->prepare("
            SELECT

                ph.*,
                fr.category

            FROM payment_history ph

            LEFT JOIN finance_records fr
                ON ph.fee_id = fr.fee_id

            WHERE ph.student_id = ?

            ORDER BY ph.payment_date DESC
        ");

        $historyStmt->execute([$sid]);

        $history =
            $historyStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

    } catch (PDOException $e) {

        echo "
        <div
            style='
                padding:20px;
                background:#fee2e2;
                color:#b91c1c;
                border-radius:12px;
                margin-bottom:20px;
            '
        >
            Finance Module Error:
            "
            .
            htmlspecialchars(
                $e->getMessage()
            )
            .
        "
        </div>
        ";
    }
}
?>

<div
class="module-entrance"
style="
animation:nexusFadeIn .5s ease-out forwards;
"
>

<div
style="
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:2rem;
flex-wrap:wrap;
gap:20px;
"
>

<div>

<h2
style="
color:#1e293b;
font-weight:800;
margin:0;
"
>
Finance <b>Monitor</b>
</h2>

<p
style="
color:#64748b;
margin-top:5px;
"
>
Real-time audit of your tuition and fee records
</p>

</div>

<div
style="
background:
<?= $total_due > 0 ? '#fff1f2' : '#f0fdf4' ?>;

padding:18px 25px;

border-radius:20px;

border:1px solid
<?= $total_due > 0 ? '#fecdd3' : '#bbf7d0' ?>;
"
>

<small
style="
display:block;
font-size:.65rem;
font-weight:800;
color:#64748b;
text-transform:uppercase;
"
>
Total Outstanding
</small>

<div
style="
font-size:1.6rem;
font-weight:900;
color:
<?= $total_due > 0 ? '#e11d48' : '#16a34a' ?>;
"
>
₹<?= number_format($total_due, 2) ?>
</div>

</div>

</div>

<h4
style="
color:#1e293b;
font-weight:800;
margin-bottom:15px;
"
>
Outstanding Fees
</h4>

<div
style="
background:white;
border-radius:24px;
border:1px solid #e2e8f0;
overflow:hidden;
margin-bottom:3rem;
"
>

<table
class="fees-table"
style="
width:100%;
border-collapse:collapse;
"
>

<thead>

<tr
style="
background:#f8fafc;
"
>

<th>Category</th>
<th>Balance</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>
    ```php id="t4m9xr"
<?php if (empty($records)): ?>

<tr>

<td
colspan="4"
style="
padding:40px;
text-align:center;
color:#94a3b8;
font-weight:600;
"
>

No fee records found.

</td>

</tr>

<?php else: ?>

<?php foreach ($records as $row): ?>

<?php

$balance =
    max(
        0,
        (
            $row['total_amount']
            -
            $row['paid_amount']
        )
    );

?>

<tr
style="
border-bottom:1px solid #f1f5f9;
"
>

<td
style="
padding:20px;
"
>

<div
style="
font-weight:700;
color:#1e293b;
"
>
<?= htmlspecialchars($row['category']) ?>
</div>

<small
style="
color:#94a3b8;
"
>
Due:
<?= date('d M, Y', strtotime($row['due_date'])) ?>
</small>

</td>

<td
style="
padding:20px;
font-weight:700;
color:#334155;
"
>
₹<?= number_format($balance, 2) ?>
</td>

<td
style="
padding:20px;
"
>

<?php if ($row['status'] === 'Paid'): ?>

<span
style="
background:#dcfce7;
color:#16a34a;
padding:6px 12px;
border-radius:20px;
font-size:.65rem;
font-weight:800;
"
>
PAID
</span>

<?php elseif ($balance > 0): ?>

<span
style="
background:#fee2e2;
color:#ef4444;
padding:6px 12px;
border-radius:20px;
font-size:.65rem;
font-weight:800;
"
>
PENDING
</span>

<?php else: ?>

<span
style="
background:#e0f2fe;
color:#0284c7;
padding:6px 12px;
border-radius:20px;
font-size:.65rem;
font-weight:800;
"
>
PROCESSING
</span>

<?php endif; ?>

</td>

<td
style="
padding:20px;
"
>

<?php if ($balance > 0): ?>

<button

type="button"

onclick="
alert(
'Payment Gateway Integration Pending'
)
"

style="
background:#1e293b;
color:white;
border:none;
padding:10px 18px;
border-radius:12px;
font-size:.75rem;
font-weight:700;
cursor:pointer;
"

>
PAY NOW
</button>

<?php else: ?>

<span
style="
color:#16a34a;
font-weight:700;
"
>
✔ SETTLED
</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>

<h4
style="
color:#1e293b;
font-weight:800;
margin-bottom:15px;
"
>
Recent Transactions
</h4>

<div
style="
display:grid;
gap:15px;
"
>
<?php if (empty($history)): ?>

<div
style="
background:white;
padding:30px;
border-radius:20px;
border:1px solid #e2e8f0;
text-align:center;
color:#94a3b8;
font-weight:600;
"
>

No transaction history found.

</div>

<?php else: ?>

<?php foreach ($history as $txn): ?>

<div
style="
background:white;
padding:20px;
border-radius:20px;
border:1px solid #e2e8f0;
display:flex;
justify-content:space-between;
align-items:center;
gap:20px;
flex-wrap:wrap;
"
>

<div
style="
display:flex;
align-items:center;
gap:15px;
"
>

<div
style="
width:50px;
height:50px;
border-radius:12px;
background:#f0fdf4;
display:flex;
align-items:center;
justify-content:center;
font-weight:900;
font-size:1.2rem;
color:#16a34a;
"
>
₹
</div>

<div>

<div
style="
font-size:.65rem;
font-weight:800;
color:#3b82f6;
"
>
<?= htmlspecialchars($txn['transaction_id']) ?>
</div>

<div
style="
font-weight:700;
color:#1e293b;
margin-top:3px;
"
>
<?= htmlspecialchars($txn['category'] ?? 'Fee Payment') ?>
</div>

<div
style="
font-size:.75rem;
color:#94a3b8;
margin-top:3px;
"
>
<?= date('d M Y, h:i A', strtotime($txn['payment_date'])) ?>
</div>

</div>

</div>

<div
style="
text-align:right;
"
>

<div
style="
font-weight:800;
font-size:1rem;
color:#16a34a;
"
>
₹<?= number_format($txn['amount_paid'], 2) ?>
</div>

<a

href="api/generate_receipt.php?tx_id=<?= urlencode($txn['transaction_id']) ?>"

target="_blank"

style="
display:inline-block;
margin-top:5px;
font-size:.75rem;
font-weight:700;
color:#2563eb;
text-decoration:none;
"

>
DOWNLOAD RECEIPT
</a>

</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>
<style>

.fees-table th{
padding:20px;
text-align:left;
font-size:.7rem;
font-weight:800;
text-transform:uppercase;
color:#64748b;
}

.fees-table td{
padding:20px;
}

@media (max-width:768px){

.module-entrance{
padding:10px;
}

.fees-table,
.fees-table thead,
.fees-table tbody,
.fees-table th,
.fees-table td,
.fees-table tr{
display:block;
width:100%;
}

.fees-table thead{
display:none;
}

.fees-table tr{
background:#fff;
border:1px solid #e2e8f0;
border-radius:15px;
margin-bottom:15px;
padding:10px;
}

.fees-table td{
padding:10px !important;
border:none;
}

.fees-table td::before{
display:block;
font-size:.65rem;
font-weight:800;
color:#64748b;
margin-bottom:4px;
text-transform:uppercase;
}

.fees-table tr td:nth-child(1)::before{
content:"Category";
}

.fees-table tr td:nth-child(2)::before{
content:"Balance";
}

.fees-table tr td:nth-child(3)::before{
content:"Status";
}

.fees-table tr td:nth-child(4)::before{
content:"Action";
}

.module-entrance > div:first-child{
flex-direction:column;
align-items:flex-start !important;
}

}

</style>

<script>

/*
|--------------------------------------------------------------------------
| FUTURE PAYMENT GATEWAY HOOK
|--------------------------------------------------------------------------
*/

function initiatePayment(feeId){

console.log(
'Payment Request:',
feeId
);

/*

Future Integration:

Razorpay
Paytm
PhonePe
Stripe

*/

}

/*
|--------------------------------------------------------------------------
| MODULE READY
|--------------------------------------------------------------------------
*/

document.addEventListener(
'DOMContentLoaded',
function(){

console.log(
'Finance Monitor Loaded'
);

}
);

</script>
