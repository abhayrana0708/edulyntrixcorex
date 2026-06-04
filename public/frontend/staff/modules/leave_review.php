<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * LEAVE REVIEW TERMINAL
 * FINAL STABLE VERSION
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
ERROR CONTROL
============================================================ */

ini_set('display_errors', 0);

error_reporting(E_ALL);

/* ============================================================
DATABASE
============================================================ */

require_once __DIR__
. '/../../../../includes/db_connect.php';

/* ============================================================
SESSION VALIDATION
============================================================ */

$staff_session =

    $_SESSION['staff_id']

    ?? $_SESSION['login_id']

    ?? '';

$user_role = strtolower(

    trim(
        $_SESSION['role']
        ?? 'faculty'
    )
);

if (empty($staff_session)) {

    die("SESSION_EXPIRED");
}

/* ============================================================
FETCH STAFF CONTEXT
============================================================ */

try {

    $stmt_dept = $pdo->prepare("

        SELECT

            dept_id,
            department,
            status,
            role

        FROM staff

        WHERE

            (
                staff_id = ?
                OR login_id = ?
            )

        LIMIT 1

    ");

    $stmt_dept->execute([

        $staff_session,

        $staff_session

    ]);

    $staff =
        $stmt_dept->fetch(PDO::FETCH_ASSOC);

    /*
    =========================================================
    STAFF VALIDATION
    =========================================================
    */

    if (!$staff) {

        throw new Exception(
            'STAFF_IDENTITY_FAILURE'
        );
    }

    /*
    =========================================================
    STATUS CHECK
    =========================================================
    */

    if (

        strtolower(
            trim($staff['status'])
        ) !== 'active'

    ) {

        throw new Exception(
            'STAFF_ACCOUNT_DISABLED'
        );
    }

    /*
    =========================================================
    VARIABLES
    =========================================================
    */

    $dept_id =
        $staff['dept_id'];

    $dept_name =
        $staff['department'];

    /*
    =========================================================
    LEAVE REQUEST FETCH
    =========================================================
    */

    $query = "

        SELECT

            lr.request_id,
            lr.student_id,
            lr.leave_type,
            lr.start_date,
            lr.end_date,
            lr.reason,
            lr.applied_on,
            lr.status,

            s.full_name,
            s.current_semester

        FROM leave_requests lr

        JOIN students s
        ON lr.student_id = s.student_id

        WHERE

            lr.dept_id = ?

            AND

            lr.end_date >= CURDATE()

        ORDER BY

            CASE
                WHEN lr.status = 'Pending'
                THEN 0
                ELSE 1
            END,

            lr.start_date ASC

    ";

    $stmt = $pdo->prepare($query);

    $stmt->execute([
        $dept_id
    ]);

    $requests =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

}
catch(Exception $e){

    echo "

    <div style='
        padding:25px;
        color:#ef4444;
        background:#fff1f2;
        border-radius:20px;
        border:1px solid #fecaca;
        font-weight:700;
    '>

        CORE_SYNC_ERROR:

        " . htmlspecialchars($e->getMessage()) . "

    </div>

    ";

    exit;
}
?>

<style>

.leave-grid{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(340px,1fr));

    gap:22px;
}

.leave-card{

    background:#fff;

    border-radius:24px;

    border:1px solid #e2e8f0;

    padding:22px;

    transition:.3s;

    position:relative;

    overflow:hidden;
}

.leave-card:hover{

    transform:translateY(-4px);

    box-shadow:0 15px 35px rgba(15,23,42,.06);
}

.leave-top{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:18px;
}

.student-chip{

    background:#eef2ff;

    color:#4f46e5;

    padding:6px 12px;

    border-radius:8px;

    font-size:.72rem;

    font-weight:800;

    font-family:'JetBrains Mono';
}

.status-chip{

    padding:6px 12px;

    border-radius:50px;

    font-size:.68rem;

    font-weight:800;

    color:#fff;
}

.leave-meta{

    display:flex;

    gap:14px;

    background:#f8fafc;

    border:1px solid #f1f5f9;

    border-radius:14px;

    padding:12px;

    margin-top:16px;
}

.meta-block{
    flex:1;
}

.meta-title{

    font-size:.6rem;

    color:#94a3b8;

    font-weight:800;

    margin-bottom:4px;
}

.meta-value{

    font-size:.8rem;

    font-weight:700;

    color:#334155;
}

.reason-box{

    margin-top:16px;

    background:#fafafa;

    border:1px solid #f1f5f9;

    border-radius:14px;

    padding:14px;
}

.reason-title{

    font-size:.65rem;

    color:#94a3b8;

    font-weight:800;

    margin-bottom:8px;
}

.reason-text{

    font-size:.82rem;

    color:#334155;

    line-height:1.6;
}

.action-grid{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:12px;

    margin-top:22px;
}

.approve-btn,
.reject-btn{

    border:none;

    padding:13px;

    border-radius:12px;

    cursor:pointer;

    font-weight:800;

    transition:.3s;
}

.approve-btn{

    background:#22c55e;

    color:#fff;
}

.reject-btn{

    background:#fff;

    border:1px solid #fecaca;

    color:#ef4444;
}

.approve-btn:hover{

    transform:translateY(-2px);

    box-shadow:0 12px 25px rgba(34,197,94,.25);
}

.reject-btn:hover{

    transform:translateY(-2px);

    background:#fff5f5;
}

.await-box{

    margin-top:22px;

    background:#eef2ff;

    color:#4f46e5;

    text-align:center;

    padding:12px;

    border-radius:12px;

    font-size:.72rem;

    font-weight:800;
}

.empty-box{

    padding:70px 20px;

    background:#fff;

    border-radius:24px;

    border:1px solid #e2e8f0;

    text-align:center;
}

</style>

<!-- HEADER -->

<div style="margin-bottom:28px;">

    <h2 style="
        font-size:1.8rem;
        font-weight:800;
        color:#1e1b4b;
    ">

        Leave Review

        <span style="color:#4f46e5;">
            Terminal
        </span>

    </h2>

    <p style="
        margin-top:6px;
        color:#64748b;
        font-size:.85rem;
    ">

        Monitoring active & future requests for:

        <b>
            <?= htmlspecialchars($dept_name) ?>
        </b>

    </p>

</div>

<?php if(empty($requests)): ?>

<div class="empty-box">

    <i class="fa-solid fa-calendar-check"
       style="
            font-size:4rem;
            color:#e2e8f0;
            margin-bottom:18px;
       ">
    </i>

    <h3 style="
        color:#1e293b;
        font-weight:800;
    ">

        No Upcoming Leaves

    </h3>

    <p style="
        color:#94a3b8;
        margin-top:10px;
    ">

        Department schedule is fully operational.

    </p>

</div>

<?php else: ?>

<div class="leave-grid">

<?php foreach($requests as $req):

$statusColor = '#64748b';

if($req['status'] === 'Approved'){
    $statusColor = '#22c55e';
}
elseif($req['status'] === 'Rejected'){
    $statusColor = '#ef4444';
}
?>

<div
    class="leave-card"
    id="leave-card-<?= $req['request_id'] ?>"
>

    <div class="leave-top">

        <div class="student-chip">

            <?= htmlspecialchars($req['student_id']) ?>

        </div>

        <div class="status-chip"
             style="background:<?= $statusColor ?>;">

            <?= strtoupper($req['status']) ?>

        </div>

    </div>

    <div style="
        font-size:1.15rem;
        font-weight:800;
        color:#1e293b;
    ">

        <?= htmlspecialchars($req['full_name']) ?>

    </div>

    <div style="
        margin-top:4px;
        color:#64748b;
        font-size:.82rem;
    ">

        Semester:
        <?= htmlspecialchars($req['current_semester']) ?>

        •

        <?= htmlspecialchars($req['leave_type']) ?>

    </div>

    <!-- DATES -->

    <div class="leave-meta">

        <div class="meta-block">

            <div class="meta-title">

                START DATE

            </div>

            <div class="meta-value">

                <?= date(
                    'd M Y',
                    strtotime($req['start_date'])
                ) ?>

            </div>

        </div>

        <div class="meta-block">

            <div class="meta-title">

                END DATE

            </div>

            <div class="meta-value">

                <?= date(
                    'd M Y',
                    strtotime($req['end_date'])
                ) ?>

            </div>

        </div>

    </div>

    <!-- REASON -->

    <div class="reason-box">

        <div class="reason-title">

            LEAVE JUSTIFICATION

        </div>

        <div class="reason-text">

            <?= nl2br(
                htmlspecialchars(
                    $req['reason']
                )
            ) ?>

        </div>

    </div>

    <!-- ACTIONS -->

    <?php if(

        $user_role === 'hod'

        &&

        $req['status'] === 'Pending'

    ): ?>

    <div class="action-grid">

        <button
            class="approve-btn"
            onclick="updateLeaveStatus(
                <?= $req['request_id'] ?>,
                'Approved'
            )"
        >

            APPROVE

        </button>

        <button
            class="reject-btn"
            onclick="updateLeaveStatus(
                <?= $req['request_id'] ?>,
                'Rejected'
            )"
        >

            REJECT

        </button>

    </div>

    <?php elseif(

        $user_role !== 'hod'

        &&

        $req['status'] === 'Pending'

    ): ?>

    <div class="await-box">

        AWAITING HOD ACTION

    </div>

    <?php endif; ?>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<script>

/* ============================================================
LIVE LEAVE STATUS ENGINE
============================================================ */

async function updateLeaveStatus(
    requestId,
    status
){

    /*
    =========================================================
    CONFIRM
    =========================================================
    */

    const confirmed = confirm(

        `Confirm ${status} action?`

    );

    if(!confirmed){
        return;
    }

    /*
    =========================================================
    CARD
    =========================================================
    */

    const card =
        document.getElementById(
            'leave-card-' + requestId
        );

    /*
    =========================================================
    LOADING EFFECT
    =========================================================
    */

    if(card){

        card.style.opacity = '.6';

        card.style.pointerEvents =
            'none';
    }

    try {

        /*
        =====================================================
        REQUEST
        =====================================================
        */

        const response = await fetch(

            '/EdulyntrixCoreX/public/frontend/staff/modules/process_leave.php',

            {
                method:'POST',

                headers:{
                    'Content-Type':
                    'application/x-www-form-urlencoded'
                },

                body:

                    'request_id='
                    +
                    encodeURIComponent(requestId)

                    +

                    '&status='

                    +

                    encodeURIComponent(status)
            }
        );

        /*
        =====================================================
        RESPONSE
        =====================================================
        */

        const result =
            await response.json();

        /*
        =====================================================
        SUCCESS
        =====================================================
        */

        if(result.success){

            /*
            =================================================
            GLOW EFFECT
            =================================================
            */

            if(card){

                card.style.transition =
                    '.4s';

                card.style.transform =
                    'scale(.98)';

                card.style.opacity =
                    '0';

                setTimeout(()=>{

                    card.remove();

                },400);
            }

        }

        /*
        =====================================================
        FAILURE
        =====================================================
        */

        else {

            alert(
                result.message
                || 'ACTION_FAILED'
            );

            if(card){

                card.style.opacity = '1';

                card.style.pointerEvents =
                    'auto';
            }
        }

    }
    catch(error){

        console.error(error);

        alert(
            'NETWORK_FAILURE'
        );

        if(card){

            card.style.opacity = '1';

            card.style.pointerEvents =
                'auto';
        }
    }
}

</script>