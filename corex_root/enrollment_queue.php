<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * ENROLLMENT QUEUE
 * FINAL STABLE VERSION
 * HTML + CSS + JS + PHP
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
DATABASE
============================================================ */

require_once __DIR__ . '/../includes/db_connect.php';

/* ============================================================
FETCH ENROLLMENT DATA
============================================================ */

try {

    $stmt = $pdo->prepare("

        SELECT
            id,
            student_name,
            student_id,
            email,
            branch,
            status,
            request_date,
            created_at

        FROM enrollment_queue

        ORDER BY created_at DESC

    ");

    $stmt->execute();

    $students =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

}
catch(PDOException $e){

    die(
        "ENROLLMENT_SYNC_FAILURE : "
        .
        $e->getMessage()
    );
}

/* ============================================================
COUNTERS
============================================================ */

$pending  = 0;
$approved = 0;
$rejected = 0;

foreach($students as $s){

    $status =
        strtolower(
            trim($s['status'])
        );

    if($status === 'pending'){

        $pending++;

    } elseif($status === 'approved'){

        $approved++;

    } elseif($status === 'rejected'){

        $rejected++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>

    Enrollment Queue

</title>

<link
href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap"
rel="stylesheet"
>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<style>

/* ============================================================
ROOT
============================================================ */

:root{

    --bg:#030b17;

    --panel:#071120;

    --border:rgba(0,255,255,.08);

    --accent:#16e0ff;

    --text:#e2e8f0;

    --muted:#64748b;

    --success:#10b981;

    --danger:#ef4444;

    --warning:#f59e0b;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{

    background:
    linear-gradient(
        rgba(0,0,0,.92),
        rgba(0,0,0,.95)
    ),
    repeating-linear-gradient(
        90deg,
        rgba(0,255,255,.03) 0px,
        rgba(0,255,255,.03) 1px,
        transparent 1px,
        transparent 60px
    ),
    #020817;

    color:var(--text);

    padding:35px;
}

/* ============================================================
CONTAINER
============================================================ */

.queue-shell{

    background:rgba(7,17,32,.94);

    border:1px solid var(--border);

    border-radius:24px;

    overflow:hidden;

    backdrop-filter:blur(12px);
}

/* ============================================================
HEADER
============================================================ */

.queue-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:28px 35px;

    border-bottom:1px solid var(--border);
}

.title{

    font-size:1.8rem;

    font-weight:800;
}

.title span{

    color:var(--accent);
}

.subtitle{

    margin-top:8px;

    color:var(--muted);

    font-size:.8rem;
}

/* ============================================================
STATS
============================================================ */

.stats{

    display:flex;

    gap:15px;
}

.stat-box{

    min-width:120px;

    background:rgba(255,255,255,.02);

    border:1px solid var(--border);

    border-radius:18px;

    padding:18px;
}

.stat-value{

    font-size:1.6rem;

    font-weight:800;
}

.stat-label{

    margin-top:5px;

    font-size:.7rem;

    color:var(--muted);

    text-transform:uppercase;
}

/* ============================================================
TABLE
============================================================ */

.queue-table{

    width:100%;

    border-collapse:collapse;
}

.queue-table thead{

    background:rgba(255,255,255,.02);
}

.queue-table th{

    text-align:left;

    padding:18px 24px;

    font-size:.72rem;

    text-transform:uppercase;

    letter-spacing:1px;

    color:#8ca3bf;

    border-bottom:1px solid var(--border);
}

.queue-table td{

    padding:24px;

    border-bottom:1px solid rgba(255,255,255,.04);
}

.queue-table tbody tr{

    transition:.25s ease;
}

.queue-table tbody tr:hover{

    background:rgba(255,255,255,.02);
}

/* ============================================================
STUDENT
============================================================ */

.student-wrap{

    display:flex;

    align-items:center;

    gap:15px;
}

.avatar{

    width:52px;

    height:52px;

    border-radius:16px;

    background:linear-gradient(
        135deg,
        #00f2ff,
        #2563eb
    );

    display:flex;

    align-items:center;

    justify-content:center;

    color:#000;

    font-weight:800;
}

.student-name{

    font-weight:700;

    margin-bottom:4px;
}

.student-id{

    color:var(--accent);

    font-size:.76rem;

    font-family:'JetBrains Mono';
}

/* ============================================================
BADGES
============================================================ */

.badge{

    padding:9px 16px;

    border-radius:40px;

    font-size:.68rem;

    font-weight:800;

    text-transform:uppercase;
}

.pending{

    background:rgba(245,158,11,.08);

    color:var(--warning);

    border:1px solid rgba(245,158,11,.2);
}

.approved{

    background:rgba(16,185,129,.08);

    color:var(--success);

    border:1px solid rgba(16,185,129,.2);
}

.rejected{

    background:rgba(239,68,68,.08);

    color:var(--danger);

    border:1px solid rgba(239,68,68,.2);
}

/* ============================================================
BUTTONS
============================================================ */

.action-wrap{

    display:flex;

    gap:10px;
}

.btn{

    border:none;

    padding:11px 16px;

    border-radius:12px;

    font-size:.72rem;

    font-weight:800;

    cursor:pointer;

    transition:.25s ease;
}

.btn:hover{

    transform:translateY(-2px);
}

.btn-approve{

    background:#10b981;

    color:#fff;
}

.btn-reject{

    background:#ef4444;

    color:#fff;
}

/* ============================================================
EMPTY
============================================================ */

.empty{

    text-align:center;

    padding:70px;

    color:#64748b;
}

/* ============================================================
RESPONSIVE
============================================================ */

@media(max-width:1100px){

    .queue-header{

        flex-direction:column;

        align-items:flex-start;

        gap:20px;
    }

    .queue-table{

        display:block;

        overflow-x:auto;
    }
}

</style>

</head>

<body>

<div class="queue-shell">

    <!-- HEADER -->

    <div class="queue-header">

        <div>

            <div class="title">

                Enrollment
                <span>

                    Queue

                </span>

            </div>

            <div class="subtitle">

                Applicant verification, onboarding control and approval authority.

            </div>

        </div>

        <div class="stats">

            <div class="stat-box">

                <div class="stat-value">

                    <?= $pending ?>

                </div>

                <div class="stat-label">

                    Pending

                </div>

            </div>

            <div class="stat-box">

                <div class="stat-value">

                    <?= $approved ?>

                </div>

                <div class="stat-label">

                    Approved

                </div>

            </div>

            <div class="stat-box">

                <div class="stat-value">

                    <?= $rejected ?>

                </div>

                <div class="stat-label">

                    Rejected

                </div>

            </div>

        </div>

    </div>

    <!-- TABLE -->

    <table class="queue-table">

        <thead>

            <tr>

                <th>Applicant</th>
                <th>Branch</th>
                <th>Email</th>
                <th>Request Date</th>
                <th>Status</th>
                <th style="text-align:right;">

                    Actions

                </th>

            </tr>

        </thead>

        <tbody>

        <?php if(count($students) > 0): ?>

            <?php foreach($students as $s): ?>

            <?php
            $status =
                strtolower(
                    trim($s['status'])
                );
            ?>

            <tr id="row-<?= $s['id'] ?>">

                <!-- STUDENT -->

                <td>

                    <div class="student-wrap">

                        <div class="avatar">

                            <?= strtoupper(
                                substr(
                                    $s['student_name'],
                                    0,
                                    1
                                )
                            ) ?>

                        </div>

                        <div>

                            <div class="student-name">

                                <?= htmlspecialchars(
                                    $s['student_name']
                                ) ?>

                            </div>

                            <div class="student-id">

                                <?= htmlspecialchars(
                                    $s['student_id']
                                ) ?>

                            </div>

                        </div>

                    </div>

                </td>

                <!-- BRANCH -->

                <td>

                    <?= htmlspecialchars(
                        $s['branch']
                    ) ?>

                </td>

                <!-- EMAIL -->

                <td>

                    <?= htmlspecialchars(
                        $s['email']
                    ) ?>

                </td>

                <!-- DATE -->

                <td>

                    <?= date(
                        'd M Y',
                        strtotime(
                            $s['created_at']
                        )
                    ) ?>

                </td>

                <!-- STATUS -->

                <td>

                    <span
                        class="badge <?= $status ?>"
                        id="badge-<?= $s['id'] ?>"
                    >

                        <?= strtoupper($status) ?>

                    </span>

                </td>

                <!-- ACTIONS -->

                <td style="text-align:right;">

                    <div class="action-wrap">

                        <button
                            class="btn btn-approve"
                            onclick="updateEnrollment(
                                <?= $s['id'] ?>,
                                'approved'
                            )"
                        >

                            APPROVE

                        </button>

                        <button
                            class="btn btn-reject"
                            onclick="updateEnrollment(
                                <?= $s['id'] ?>,
                                'rejected'
                            )"
                        >

                            REJECT

                        </button>

                    </div>

                </td>

            </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="6">

                    <div class="empty">

                        NO ENROLLMENT REQUESTS FOUND

                    </div>

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

<script>

/* ============================================================
UPDATE ENROLLMENT
============================================================ */

function updateEnrollment(id,status){

    const formData = new FormData();

    formData.append('id',id);

    formData.append('status',status);

    fetch(

        'api/update_enrollment.php',

        {
            method:'POST',
            body:formData
        }

    )

    .then(res=>res.json())

    .then(data=>{

        if(data.success){

            const badge =
                document.getElementById(
                    'badge-' + id
                );

            badge.innerHTML =
                status.toUpperCase();

            badge.className =
                'badge ' + status;

        } else {

            alert(
                data.message
            );
        }
    })

    .catch(err=>{

        console.error(err);

        alert(
            'Enrollment update failed.'
        );
    });
}

</script>

</body>
</html>