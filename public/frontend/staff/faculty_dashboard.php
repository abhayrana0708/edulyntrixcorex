<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * FACULTY FROST INTERFACE
 * FINAL STABLE VERSION
 * ============================================================
 */

ini_set('display_errors', 0);

error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
AUTH SHIELD
============================================================ */

$staff_session =

    $_SESSION['staff_id']

    ?? $_SESSION['login_id']

    ?? '';

$role =

    strtolower(
        $_SESSION['role']
        ?? ''
    );

/* ============================================================
STRICT VALIDATION
============================================================ */

if (
    empty($staff_session)
    ||
    $role !== 'faculty'
) {

    header(
        "Location: /EdulyntrixCoreX/public/frontend/staff/login.php?error=unauthorized"
    );

    exit;
}

/* ============================================================
DATABASE
============================================================ */

require_once __DIR__
. '/../../../includes/db_connect.php';

/* ============================================================
IDENTITY
============================================================ */

try {

    $stmt = $pdo->prepare("

        SELECT
            full_name,
            designation,
            department,
            dept_id,
            status

        FROM staff

        WHERE

            staff_id = ?
            OR login_id = ?

        LIMIT 1

    ");

    $stmt->execute([
        $staff_session,
        $staff_session
    ]);

    $faculty =
        $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    =========================================================
    INVALID STAFF
    =========================================================
    */

    if (
        !$faculty
        ||
        strtolower(
            trim($faculty['status'] ?? '')
        ) !== 'active'
    ) {

        session_destroy();

        header(
            "Location: /EdulyntrixCoreX/public/frontend/staff/login.php?error=identity_failure"
        );

        exit;
    }

    /*
    =========================================================
    VARIABLES
    =========================================================
    */

    $display_name =
        $faculty['full_name'];

    $designation =
        $faculty['designation'];

    $display_dept =
        $faculty['department'];

    $dept_id =
        $faculty['dept_id'];

    /*
    =========================================================
    SESSION SYNC
    =========================================================
    */

    $_SESSION['dept_id'] =
        $dept_id;

    $_SESSION['department'] =
        $display_dept;

    $_SESSION['dept_name'] =
        $display_dept;

    /*
    =========================================================
    PROFILE
    =========================================================
    */

    $profile_img =

        "https://ui-avatars.com/api/?name="

        . urlencode($display_name)

        . "&background=4f46e5&color=fff";

    /*
    =========================================================
    METRICS
    =========================================================
    */

    $today =
        date('D');

    /*
    =========================================================
    CLASSES
    =========================================================
    */

    $q_classes = $pdo->prepare("

        SELECT COUNT(*)

        FROM timetable t

        JOIN subjects s
        ON t.subject_id = s.subject_id

        WHERE

            s.assigned_faculty_id = ?
            AND t.day_of_week = ?

    ");

    $q_classes->execute([
        $staff_session,
        $today
    ]);

    $count_classes =
        $q_classes->fetchColumn();

    /*
    =========================================================
    LEAVES
    =========================================================
    */

    $q_leaves = $pdo->prepare("

        SELECT COUNT(*)

        FROM leave_requests

        WHERE
            dept_id = ?
            AND status = 'Pending'

    ");

    $q_leaves->execute([
        $dept_id
    ]);

    $count_leaves =
        $q_leaves->fetchColumn();

    /*
    =========================================================
    ATTENDANCE
    =========================================================
    */

    $q_attendance = $pdo->prepare("

        SELECT

            ROUND(

                AVG(

                    CASE
                        WHEN a.status = 'present'
                        THEN 100
                        ELSE 0
                    END

                ),

                1

            )

        FROM attendance a

        JOIN subjects s
        ON a.subject_id = s.subject_id

        WHERE
            s.assigned_faculty_id = ?

    ");

    $q_attendance->execute([
        $staff_session
    ]);

    $avg_attendance =
        $q_attendance->fetchColumn()
        ?? "0.0";

    /*
    =========================================================
    FINES
    =========================================================
    */

    $q_fines = $pdo->prepare("

        SELECT COUNT(*)

        FROM disciplinary_fines df

        JOIN students s
        ON s.student_id = df.student_id

        WHERE
            s.dept_id = ?

    ");

    $q_fines->execute([
        $dept_id
    ]);

    $count_fines =
        $q_fines->fetchColumn();

}
catch(PDOException $e){

    die(
        "CORE_SYNC_ERROR"
    );
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

    Faculty Node

</title>

<link
href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap"
rel="stylesheet"
>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<style>

/* ============================================================
GLOBAL
============================================================ */

:root{

    --bg:#f8fafc;

    --primary:#4f46e5;

    --primarySoft:#eef2ff;

    --sidebar:#1e1b4b;

    --text:#1e293b;

    --muted:#64748b;

    --border:#e2e8f0;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Plus Jakarta Sans',sans-serif;
}

body{

    background:var(--bg);

    color:var(--text);

    height:100vh;

    overflow:hidden;
}

.layout{
    display:flex;
    height:100vh;
}

/* ============================================================
SIDEBAR
============================================================ */

.sidebar{

    width:270px;

    background:var(--sidebar);

    color:#fff;

    display:flex;

    flex-direction:column;
}

.brand{

    padding:35px 25px;

    font-weight:800;

    font-size:1.2rem;
}

.nav{
    flex:1;
    padding:15px;
}

.nav-title{

    font-size:.68rem;

    color:#818cf8;

    margin:20px 0 10px 15px;

    text-transform:uppercase;

    font-weight:700;
}

.nav-btn{

    width:100%;

    padding:13px 16px;

    border:none;

    border-radius:12px;

    background:none;

    color:#94a3b8;

    text-align:left;

    display:flex;

    align-items:center;

    gap:12px;

    cursor:pointer;

    margin-bottom:6px;

    transition:.3s;
}

.nav-btn:hover{

    background:rgba(255,255,255,.05);

    color:#fff;

    transform:translateX(5px);
}

.nav-btn.active{

    background:var(--primary);

    color:#fff;
}

.logout{

    margin:20px;

    padding:15px;

    border:none;

    border-radius:14px;

    background:#ef4444;

    color:#fff;

    cursor:pointer;

    font-weight:700;
}

/* ============================================================
MAIN
============================================================ */

.main{
    flex:1;
    display:flex;
    flex-direction:column;
}

.topbar{

    height:80px;

    background:#fff;

    border-bottom:1px solid var(--border);

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:0 30px;
}

.clock{

    font-family:'JetBrains Mono';
    font-weight:700;
    color:var(--primary);
}

.viewport{

    flex:1;

    overflow-y:auto;

    padding:35px;
}

.card{

    background:#fff;

    padding:35px;

    border-radius:24px;

    border:1px solid var(--border);
}

.metrics{

    margin-top:30px;

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(220px,1fr));

    gap:20px;
}

.metric{

    background:var(--bg);

    border:1px solid var(--border);

    padding:20px;

    border-radius:20px;

    transition:.3s;

    cursor:pointer;
}

.metric:hover{

    transform:translateY(-4px);

    border-color:var(--primary);
}

.metric-value{

    font-size:2rem;

    font-weight:800;
}

.metric-label{

    margin-top:5px;

    color:var(--muted);

    font-size:.75rem;

    text-transform:uppercase;

    font-weight:700;
}

/* ============================================================
NOTIFICATION PANEL
============================================================ */

.notification-btn{

    width:44px;

    height:44px;

    border:none;

    border-radius:12px;

    background:#eef2ff;

    color:#4f46e5;

    cursor:pointer;

    position:relative;

    font-size:1rem;
}

.notification-dot{

    position:absolute;

    top:10px;

    right:10px;

    width:8px;

    height:8px;

    background:#ef4444;

    border-radius:50%;

    box-shadow:0 0 10px #ef4444;
}

.notification-panel{

    position:absolute;

    top:55px;

    right:0;

    width:380px;

    max-height:520px;

    overflow-y:auto;

    background:#fff;

    border:1px solid #e2e8f0;

    border-radius:18px;

    box-shadow:
        0 20px 40px rgba(0,0,0,.08);

    display:none;

    z-index:99999;
}

</style>

</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">

            EDULYNTRIX CORE

        </div>

        <nav class="nav">

            <div class="nav-title">

                Main Console

            </div>

            <button
                class="nav-btn active"
                onclick="loadNode('timetable', this)"
            >

                <i class="fa-solid fa-calendar-days"></i>

                Timetable

            </button>

            <button
                class="nav-btn"
                onclick="loadNode('mark_attendance', this)"
            >

                <i class="fa-solid fa-user-check"></i>

                Attendance

            </button>

            <div class="nav-title">

                Academic Services

            </div>

            <button
                class="nav-btn"
                onclick="loadNode('leave_review', this)"
            >

                <i class="fa-solid fa-clipboard-check"></i>

                Leave Review

            </button>

            <button
                class="nav-btn"
                id="nav-issue_fine"
                onclick="loadNode('issue_fine', this)"
            >

                <i class="fa-solid fa-receipt"></i>

                Issue Fine

            </button>

            <button
                class="nav-btn"
                onclick="loadNode('class_insights', this)"
            >

                <i class="fa-solid fa-chart-line"></i>

                Insights

            </button>

        </nav>

        <button
            class="logout"
            onclick="window.location.href='/EdulyntrixCoreX/includes/logout.php'"
        >

            TERMINATE SESSION

        </button>

    </aside>

    <!-- MAIN -->

    <main class="main">

        <header class="topbar">

            <!-- LEFT -->

            <div>

                <div style="font-weight:800;">

                    <?= htmlspecialchars($display_name) ?>

                </div>

                <div style="
                    font-size:.75rem;
                    color:#64748b;
                ">

                    <?= htmlspecialchars($display_dept) ?>

                </div>

            </div>

            <!-- RIGHT -->

            <div style="
                display:flex;
                align-items:center;
                gap:18px;
            ">

                <!-- NOTIFICATIONS -->

                <div style="position:relative;">

                    <button
                        id="notificationBell"
                        class="notification-btn"
                        onclick="toggleNotifications()"
                    >

                        <i class="fa-solid fa-bell"></i>

                        <span class="notification-dot"></span>

                    </button>

                    <div
                        class="notification-panel"
                        id="notificationPanel"
                    >

                        <div id="notificationContent">

                            <div style="
                                padding:40px;
                                text-align:center;
                                color:#64748b;
                            ">

                                <i class="fa-solid fa-spinner fa-spin"></i>

                                <div style="
                                    margin-top:10px;
                                ">

                                    Syncing notifications...

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- CLOCK -->

                <div class="clock" id="liveClock">

                    00:00:00

                </div>

            </div>

        </header>

        <section
            class="viewport"
            id="content-view"
        >

            <div class="card">

                <h1 style="
                    font-size:2.2rem;
                    font-weight:800;
                ">

                    Welcome Back,
                    <?= htmlspecialchars(
                        explode(
                            ' ',
                            $display_name
                        )[0]
                    ) ?>

                </h1>

                <p style="
                    margin-top:8px;
                    color:#64748b;
                ">

                    Academic terminal operational.

                </p>

                <div class="metrics">

                    <div class="metric">

                        <div class="metric-value">

                            <?= sprintf("%02d",$count_classes) ?>

                        </div>

                        <div class="metric-label">

                            Classes Today

                        </div>

                    </div>

                    <div class="metric">

                        <div class="metric-value">

                            <?= sprintf("%02d",$count_leaves) ?>

                        </div>

                        <div class="metric-label">

                            Pending Leaves

                        </div>

                    </div>

                    <div class="metric">

                        <div class="metric-value">

                            <?= $avg_attendance ?>%

                        </div>

                        <div class="metric-label">

                            Avg Attendance

                        </div>

                    </div>

                    <div class="metric">

                        <div class="metric-value">

                            <?= sprintf("%02d",$count_fines) ?>

                        </div>

                        <div class="metric-label">

                            Department Fines

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

<script>

/* ============================================================
CLOCK
============================================================ */

function updateClock(){

    const now = new Date();

    document.getElementById(
        'liveClock'
    ).innerText =

        now.toLocaleTimeString(
            'en-GB'
        );
}

setInterval(updateClock,1000);

updateClock();

/* ============================================================
NOTIFICATIONS
============================================================ */

let notifLoaded = false;

async function toggleNotifications(){

    const panel =
        document.getElementById(
            'notificationPanel'
        );

    if(panel.style.display === 'block'){

        panel.style.display = 'none';

        return;
    }

    panel.style.display = 'block';

    if(notifLoaded){
        return;
    }

    try {

        const response = await fetch(

            '/EdulyntrixCoreX/public/frontend/staff/modules/notifications.php?ts='
            +
            Date.now()

        );

        if(!response.ok){

            throw new Error(
                'HTTP_' + response.status
            );
        }

        const html =
            await response.text();

        document.getElementById(
            'notificationContent'
        ).innerHTML = html;

        notifLoaded = true;

    }
    catch(error){

        console.error(error);

        document.getElementById(
            'notificationContent'
        ).innerHTML = `

            <div style="
                padding:40px;
                color:#dc2626;
                text-align:center;
            ">

                Notification sync failed.

            </div>

        `;
    }
}

/* ============================================================
OUTSIDE CLICK CLOSE
============================================================ */

document.addEventListener(
    'click',
    function(e){

        const panel =
            document.getElementById(
                'notificationPanel'
            );

        const bell =
            document.getElementById(
                'notificationBell'
            );

        if(
            panel
            &&
            bell
            &&
            !panel.contains(e.target)
            &&
            !bell.contains(e.target)
        ){

            panel.style.display = 'none';
        }
    }
);

/* ============================================================
MODULE LOADER
============================================================ */

async function loadNode(module,btn=null){

    if(btn){

        document
        .querySelectorAll('.nav-btn')
        .forEach(x=>x.classList.remove('active'));

        btn.classList.add('active');
    }

    const content =
        document.getElementById(
            'content-view'
        );

    content.innerHTML = `

        <div style="
            padding:80px;
            text-align:center;
            color:#64748b;
        ">

            <i class="fa-solid fa-spinner fa-spin"
               style="font-size:2rem;">
            </i>

            <div style="margin-top:15px;">

                Loading module...

            </div>

        </div>

    `;

    try {

        const response = await fetch(

            '/EdulyntrixCoreX/public/frontend/staff/modules/'
            +
            module
            +
            '.php?ts='
            +
            Date.now()

        );

        if(!response.ok){

            throw new Error(
                'HTTP_' + response.status
            );
        }

        const html =
            await response.text();

        content.innerHTML = html;

        const scripts =
            content.querySelectorAll(
                'script'
            );

        scripts.forEach(oldScript => {

            const newScript =
                document.createElement(
                    'script'
                );

            newScript.text =
                oldScript.text;

            document.body.appendChild(
                newScript
            );

            oldScript.remove();
        });

    }
    catch(error){

        console.error(error);

        content.innerHTML = `

            <div style="
                padding:40px;
                background:#fff1f2;
                border-radius:20px;
                border:1px solid #fecaca;
                color:#dc2626;
                text-align:center;
            ">

                MODULE FAILURE

                <br><br>

                ${error.message}

            </div>

        `;
    }
}

/* ============================================================
ATTENDANCE HELPERS
============================================================ */

function fetchStudentList(){

    const subject =
        document.getElementById(
            'subject_id'
        )?.value;

    const start =
        document.getElementById(
            'session_start_time'
        )?.value;

    if(!subject){

        alert(
            'Please select subject.'
        );

        return;
    }

    const container =
        document.getElementById(
            'studentListContainer'
        );

    if(!container){
        return;
    }

    container.innerHTML = `

        <div style="
            text-align:center;
            padding:60px;
            color:#64748b;
        ">

            <i class="fa-solid fa-spinner fa-spin"
               style="font-size:2rem;">
            </i>

            <div style="margin-top:15px;">

                Loading students...

            </div>

        </div>

    `;

    fetch(

        '/EdulyntrixCoreX/public/frontend/staff/modules/attendance_processor.php?action=fetch_students&subject_id='
        +
        encodeURIComponent(subject)
        +
        '&custom_start='
        +
        encodeURIComponent(start)

    )

    .then(r=>{

        if(!r.ok){

            throw new Error(
                'HTTP_' + r.status
            );
        }

        return r.text();
    })

    .then(html=>{

        container.innerHTML = html;
    })

    .catch(err=>{

        console.error(err);

        container.innerHTML = `

            <div style="
                padding:40px;
                color:#dc2626;
                text-align:center;
            ">

                Failed to load students.

            </div>

        `;
    });
}

/* ============================================================
SAVE ATTENDANCE
============================================================ */

function saveAttendance(event){

    event.preventDefault();

    const form =
        document.getElementById(
            'attendanceFinalForm'
        );

    if(!form){

        alert(
            'Attendance form missing.'
        );

        return;
    }

    const btn =
        event.target.closest('button');

    if(!btn){
        return;
    }

    const original =
        btn.innerHTML;

    btn.disabled = true;

    btn.innerHTML = `

        <i class="fa-solid fa-spinner fa-spin"></i>

        SYNCING...

    `;

    fetch(

        '/EdulyntrixCoreX/public/frontend/staff/modules/attendance_processor.php?action=save_attendance',

        {
            method:'POST',
            body:new FormData(form)
        }

    )

    .then(r=>r.json())

    .then(data=>{

        if(data.status==='success'){

            alert(
                'Attendance synchronized.'
            );

            loadNode(
                'mark_attendance'
            );

        } else {

            alert(
                data.message
            );
        }
    })

    .catch(err=>{

        console.error(err);

        alert(
            'Attendance sync failed.'
        );
    })

    .finally(()=>{

        btn.disabled = false;

        btn.innerHTML =
            original;
    });
}

/* ============================================================
ATTENDANCE TIMER
============================================================ */

function startAttendanceTimer(expiryEpoch){

    const timer =
        document.getElementById(
            'countdownClock'
        );

    if(!timer){
        return;
    }

    function update(){

        const now =
            Math.floor(Date.now()/1000);

        const diff =
            expiryEpoch - now;

        if(diff <= 0){

            timer.innerHTML =
                'EXPIRED';

            timer.style.color =
                '#ef4444';

            return;
        }

        const mins =
            Math.floor(diff/60);

        const secs =
            diff % 60;

        timer.innerHTML =

            String(mins)
            .padStart(2,'0')

            +

            ':'

            +

            String(secs)
            .padStart(2,'0');
    }

    update();

    setInterval(update,1000);
}

</script>

</body>
</html>