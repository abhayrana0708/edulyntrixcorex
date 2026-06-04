<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * HOD ENROLLMENT QUEUE
 * FINAL FULL FIXED VERSION
 * HTML + CSS + JS + PHP
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
DATABASE
============================================================ */

require_once __DIR__ . '/../../../../../includes/db_connect.php';

/* ============================================================
SESSION SECURITY
============================================================ */

$dept_name =
    trim(
        $_SESSION['dept_name']
        ?? ''
    );

/*
============================================================
CRITICAL SECURITY CHECK
============================================================
*/

if (empty($dept_name)) {

    echo "

    <div style='
        padding:30px;
        margin-top:20px;
        background:#fff1f2;
        border:1px solid #fecdd3;
        border-radius:16px;
        color:#dc2626;
        font-family:Inter,sans-serif;
        font-weight:700;
    '>

        SECURITY BLOCK DETECTED

        <br><br>

        HOD department session missing.

        <br>

        Please re-login.

    </div>

    ";

    exit;
}

/* ============================================================
FETCH ENROLLMENT QUEUE
============================================================ */

try {

    $stmtList = $pdo->prepare("

        SELECT *

        FROM enrollment_queue

        WHERE

            LOWER(TRIM(status)) = 'pending'

            AND

            LOWER(TRIM(branch))
            = LOWER(TRIM(?))

        ORDER BY id DESC

    ");

    $stmtList->execute([
        $dept_name
    ]);

    $pending_students =
        $stmtList->fetchAll(PDO::FETCH_ASSOC);

    $pending_count =
        count($pending_students);

}
catch(PDOException $e){

    error_log(
        'ENROLLMENT_QUEUE_ERROR: '
        . $e->getMessage()
    );

    $pending_students = [];
    $pending_count = 0;
}
?>

<!-- =========================================================
CSS
========================================================= -->

<style>

.enrollment-shell{
    width:100%;
}

.enroll-topbar{

    background:#f8fafc;

    border:1px solid #e2e8f0;

    border-radius:14px;

    padding:10px 18px;

    margin-bottom:25px;

    font-size:11px;

    font-family:'JetBrains Mono', monospace;

    color:#64748b;

    letter-spacing:0.5px;
}

.module-title{

    margin-bottom:30px;
}

.module-title h2{

    margin:0;

    font-size:2.2rem;

    font-weight:800;

    color:#0f172a;
}

.module-title h2 span{

    color:#10b981;
}

.module-title p{

    margin-top:8px;

    color:#64748b;

    font-size:0.92rem;
}

.stats-grid{

    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));

    gap:20px;

    margin-bottom:35px;
}

.stat-card{

    background:#ffffff;

    border-radius:22px;

    padding:28px;

    border:1px solid #e2e8f0;

    box-shadow:0 10px 20px rgba(15,23,42,.04);
}

.stat-label{

    font-size:0.7rem;

    color:#94a3b8;

    font-weight:800;

    text-transform:uppercase;

    letter-spacing:1px;
}

.stat-value{

    font-size:3rem;

    font-weight:800;

    margin:10px 0;

    color:#0f172a;
}

.stat-status{

    color:#10b981;

    font-size:0.8rem;

    font-weight:700;
}

.queue-container{

    background:#fff;

    border-radius:24px;

    border:1px solid #e2e8f0;

    overflow:hidden;

    box-shadow:0 10px 25px rgba(15,23,42,.04);
}

.queue-table{

    width:100%;

    border-collapse:collapse;
}

.queue-table thead{

    background:#f8fafc;

    border-bottom:1px solid #e2e8f0;
}

.queue-table th{

    padding:20px 25px;

    text-align:left;

    font-size:0.72rem;

    font-weight:800;

    color:#64748b;

    text-transform:uppercase;

    letter-spacing:1px;
}

.queue-table td{

    padding:22px 25px;

    border-bottom:1px solid #f1f5f9;
}

.queue-row{

    transition:0.25s ease;
}

.queue-row:hover{

    background:#f8fafc;
}

.student-node{

    display:flex;

    align-items:center;

    gap:15px;
}

.student-avatar{

    width:46px;
    height:46px;

    border-radius:14px;

    background:#e0e7ff;

    display:flex;

    align-items:center;
    justify-content:center;

    font-weight:800;

    color:#4338ca;

    font-size:1.1rem;
}

.student-name{

    font-size:1rem;

    font-weight:700;

    color:#0f172a;
}

.student-id{

    font-size:0.78rem;

    font-weight:700;

    color:#6366f1;

    font-family:'JetBrains Mono', monospace;
}

.branch-pill{

    display:inline-block;

    padding:7px 14px;

    border-radius:10px;

    background:#f1f5f9;

    border:1px solid #e2e8f0;

    color:#475569;

    font-size:0.82rem;

    font-weight:600;
}

.action-wrap{

    display:flex;

    gap:12px;
}

.verify-btn{

    border:none;

    background:#10b981;

    color:white;

    padding:11px 18px;

    border-radius:12px;

    font-size:0.75rem;

    font-weight:800;

    cursor:pointer;

    transition:0.25s ease;
}

.verify-btn:hover{

    transform:translateY(-2px);

    box-shadow:0 8px 18px rgba(16,185,129,.25);
}

.reject-btn{

    border:1px solid #fecaca;

    background:#fff1f2;

    color:#ef4444;

    padding:11px 18px;

    border-radius:12px;

    font-size:0.75rem;

    font-weight:800;

    cursor:pointer;

    transition:0.25s ease;
}

.reject-btn:hover{

    background:#fee2e2;
}

.empty-state{

    padding:90px 20px;

    text-align:center;

    color:#94a3b8;
}

.empty-state .icon{

    font-size:4rem;

    margin-bottom:15px;
}

.empty-state .title{

    font-size:1rem;

    font-weight:700;

    color:#475569;
}

.empty-state .sub{

    margin-top:10px;

    font-size:0.8rem;
}

</style>

<!-- =========================================================
HTML
========================================================= -->

<div class="enrollment-shell">

    <!-- TOP BAR -->

    <div class="enroll-topbar">

        COREX_AUTH_NODE:
        [<?= htmlspecialchars($dept_name) ?>]

        |

        ADMISSIONS_PENDING:
        [<?= $pending_count ?>]

    </div>

    <!-- TITLE -->

    <div class="module-title">

        <h2>

            Enrollment

            <span>Queue</span>

        </h2>

        <p>

            Active Administrative Authority:

            <b>
                <?= htmlspecialchars($dept_name) ?>
            </b>

        </p>

    </div>

    <!-- STATS -->

    <div class="stats-grid">

        <div class="stat-card">

            <div class="stat-label">

                Admissions Pended

            </div>

            <div class="stat-value">

                <?= $pending_count ?>

            </div>

            <div class="stat-status">

                HOD Verification Protocol Active

            </div>

        </div>

    </div>

    <!-- TABLE -->

    <div class="queue-container">

        <table class="queue-table">

            <thead>

                <tr>

                    <th>
                        Student Identity
                    </th>

                    <th>
                        Branch Node
                    </th>

                    <th>
                        Protocol
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php if(empty($pending_students)): ?>

                <tr>

                    <td colspan="3">

                        <div class="empty-state">

                            <div class="icon">
                                📂
                            </div>

                            <div class="title">

                                No Pending Enrollments

                            </div>

                            <div class="sub">

                                Waiting for new student requests.

                            </div>

                        </div>

                    </td>

                </tr>

            <?php else: ?>

                <?php foreach($pending_students as $row): ?>

                <tr class="queue-row">

                    <!-- STUDENT -->

                    <td>

                        <div class="student-node">

                            <div class="student-avatar">

                                <?= strtoupper(substr($row['student_name'],0,1)) ?>

                            </div>

                            <div>

                                <div class="student-name">

                                    <?= htmlspecialchars($row['student_name']) ?>

                                </div>

                                <div class="student-id">

                                    <?= htmlspecialchars($row['student_id']) ?>

                                </div>

                            </div>

                        </div>

                    </td>

                    <!-- BRANCH -->

                    <td>

                        <span class="branch-pill">

                            <?= htmlspecialchars($row['branch']) ?>

                        </span>

                    </td>

                    <!-- ACTIONS -->

                    <td>

                        <div class="action-wrap">

                            <button
                                class="verify-btn"

                                onclick="
                                    processNode(
                                        '<?= $row['id'] ?>',
                                        'approve'
                                    )
                                "
                            >

                                VERIFY

                            </button>

                            <button
                                class="reject-btn"

                                onclick="
                                    processNode(
                                        '<?= $row['id'] ?>',
                                        'reject'
                                    )
                                "
                            >

                                REJECT

                            </button>

                        </div>

                    </td>

                </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- =========================================================
JAVASCRIPT
========================================================= -->

<script>

/* ============================================================
PROCESS STUDENT NODE
============================================================ */

function processNode(id, action){

    const confirmation =

        action === 'approve'

        ? 'Authorize this enrollment request?'

        : 'Reject this enrollment request?';

    if(!confirm(confirmation)){
        return;
    }

    /*
    =========================================================
    ENDPOINTS
    =========================================================
    */

    const endpoint =

        action === 'approve'

        ? '/EdulyntrixCoreX/corex_root/api/approve_student.php'

        : '/EdulyntrixCoreX/corex_root/api/reject_student.php';

    /*
    =========================================================
    API CALL
    =========================================================
    */

    fetch(endpoint, {

        method:'POST',

        headers:{
            'Content-Type':'application/json'
        },

        body:JSON.stringify({
            id:id
        })

    })

    .then(response => response.json())

    .then(data => {

        /*
        =====================================================
        SUCCESS
        =====================================================
        */

        if(

            data.success === true

            ||

            data.status === 'success'

        ){

            alert(
                `Student ${action}ed successfully`
            );

            /*
            =================================================
            RELOAD MODULE
            =================================================
            */

            if(
                typeof loadModule === 'function'
            ){

                loadModule(
                    'enrollment',
                    document.getElementById(
                        'nav-enrollment'
                    )
                );

            } else {

                location.reload();
            }

        }

        /*
        =====================================================
        FAILURE
        =====================================================
        */

        else {

            alert(

                data.message

                ||

                'UNKNOWN_SYSTEM_FAILURE'

            );
        }
    })

    /*
    =========================================================
    NETWORK FAILURE
    =========================================================
    */

    .catch(error => {

        console.error(error);

        alert(
            'API CONNECTION FAILURE'
        );
    });
}

</script>