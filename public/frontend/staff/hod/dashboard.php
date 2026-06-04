<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X - HOD EXECUTIVE HUB
 * FINAL FIXED VERSION
 * STABLE MODULE ENGINE + ENROLLMENT FIX
 * ============================================================
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
SECURITY GATEWAY
============================================================ */

if (
    !isset($_SESSION['user_id'])
    ||
    (
        $_SESSION['role'] !== 'hod'
        &&
        $_SESSION['role'] !== 'admin'
    )
) {

    header(
        "Location: ../../../../index.php?error=unauthorized_protocol"
    );

    exit();
}

/* ============================================================
DATABASE
============================================================ */

require_once __DIR__ . '/../../../../includes/db_connect.php';

/* ============================================================
HOD PROFILE
============================================================ */

$hod_id = $_SESSION['user_id'];

try {

    $stmt = $pdo->prepare("

        SELECT
            name,
            department,
            profile_pic

        FROM hod_accounts

        WHERE hod_id = ?

        LIMIT 1

    ");

    $stmt->execute([$hod_id]);

    $hod = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($hod) {

        $display_name =
            !empty($hod['name'])
            ? $hod['name']
            : ($_SESSION['full_name'] ?? 'Executive');

        $display_dept =
            !empty($hod['department'])
            ? $hod['department']
            : ($_SESSION['dept_name'] ?? 'Academic');

        $_SESSION['full_name'] = $display_name;
        $_SESSION['dept_name'] = $display_dept;

    } else {

        $display_name =
            $_SESSION['full_name']
            ?? 'Unknown Executive';

        $display_dept =
            $_SESSION['dept_name']
            ?? 'Command Center';
    }

    $profile_img =
        !empty($hod['profile_pic'])

        ? "assets/img/profiles/" . $hod['profile_pic']

        : "https://ui-avatars.com/api/?name="
            . urlencode($display_name)
            . "&background=10b981&color=000&bold=true&size=128";

}
catch(PDOException $e){

    $display_name =
        $_SESSION['full_name']
        ?? 'Executive';

    $display_dept =
        $_SESSION['dept_name']
        ?? 'Management';

    $profile_img =
        "https://ui-avatars.com/api/?name="
        . urlencode($display_name)
        . "&background=10b981&color=000&bold=true&size=128";
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

        <?= htmlspecialchars($display_dept) ?>

        | Executive Hub

    </title>

    <!-- FONTS -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap"
        rel="stylesheet"
    >

    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

<style>

/* ============================================================
ROOT
============================================================ */

:root {

    --emerald:#10b981;
    --emerald-soft:rgba(16,185,129,.1);

    --bg-deep:#020617;
    --bg-glass:rgba(15,23,42,.65);

    --border-light:rgba(255,255,255,.08);

    --text-main:#f1f5f9;
    --text-muted:#94a3b8;

    --danger:#f87171;

    --silk-ease:cubic-bezier(.16,1,.3,1);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{

    height:100vh;
    overflow:hidden;

    background:var(--bg-deep);

    color:var(--text-main);
}

/* ============================================================
BACKGROUND
============================================================ */

.mesh-bg{

    position:fixed;
    inset:0;

    background:
        radial-gradient(
            circle at 50% 50%,
            #064e3b 0%,
            #020617 100%
        );

    z-index:-2;
}

.bg-branding{

    position:fixed;

    top:50%;
    left:50%;

    transform:translate(-50%,-50%);

    font-size:8vw;
    font-weight:900;

    color:rgba(255,255,255,.015);

    pointer-events:none;

    z-index:-1;
}

/* ============================================================
LAYOUT
============================================================ */

.app-shell{

    display:flex;
    height:100vh;
}

/* ============================================================
SIDEBAR
============================================================ */

.sidebar{

    width:280px;

    background:#0a1128;

    border-right:1px solid var(--border-light);

    display:flex;
    flex-direction:column;
}

.brand-section{

    padding:35px 25px;

    display:flex;
    align-items:center;

    gap:12px;
}

.logo-box{

    width:35px;
    height:35px;

    border-radius:8px;

    background:var(--emerald);

    display:grid;
    place-items:center;

    color:#000;
    font-weight:800;

    box-shadow:0 0 20px rgba(16,185,129,.4);
}

.brand-text{

    font-weight:700;
    font-size:1.1rem;
}

.accent-x{

    color:var(--emerald);
}

.navigation-stack{

    flex:1;
    overflow-y:auto;

    padding:0 15px;
}

.nav-label{

    font-size:.65rem;
    font-weight:800;

    color:#475569;

    text-transform:uppercase;
    letter-spacing:1.5px;

    margin:25px 0 10px 15px;
}

.nav-link{

    width:100%;

    border:none;
    background:none;

    color:var(--text-muted);

    text-align:left;

    padding:12px 18px;

    border-radius:12px;

    cursor:pointer;

    font-size:.85rem;
    font-weight:600;

    transition:.3s var(--silk-ease);

    display:flex;
    align-items:center;

    gap:12px;
}

.nav-link:hover{

    background:rgba(255,255,255,.05);

    color:#fff;
}

.nav-link.active{

    background:var(--bg-glass);

    color:var(--emerald);

    border-left:3px solid var(--emerald);

    border-radius:0 12px 12px 0;
}

/* ============================================================
MAIN
============================================================ */

.main-wrapper{

    flex:1;

    display:flex;
    flex-direction:column;

    min-width:0;
}

.top-header{

    height:80px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:0 40px;

    border-bottom:1px solid var(--border-light);

    backdrop-filter:blur(10px);
}

.search-container{

    width:350px;
    height:45px;

    display:flex;
    align-items:center;

    gap:12px;

    padding:0 18px;

    background:var(--bg-glass);

    border:1px solid var(--border-light);

    border-radius:14px;
}

.search-container input{

    width:100%;

    background:none;
    border:none;
    outline:none;

    color:#fff;

    font-size:.85rem;
}

.header-right{

    display:flex;
    align-items:center;

    gap:20px;
}

/* ============================================================
CLOCK
============================================================ */

.clock-box{

    text-align:right;

    border-right:1px solid var(--border-light);

    padding-right:20px;
}

#liveTime{

    font-family:'JetBrains Mono';

    font-size:1.1rem;
    font-weight:700;

    color:var(--emerald);
}

#liveDate{

    font-size:.6rem;

    color:var(--text-muted);

    text-transform:uppercase;
    font-weight:700;
}

/* ============================================================
PROFILE
============================================================ */

.profile-node{

    display:flex;
    align-items:center;

    gap:12px;

    padding:6px 14px;

    background:var(--bg-glass);

    border:1px solid var(--border-light);

    border-radius:12px;
}

.avatar{

    width:38px;
    height:38px;

    object-fit:cover;

    border-radius:8px;

    border:2px solid var(--emerald);
}

/* ============================================================
CONTENT
============================================================ */

.content-body{

    flex:1;

    overflow-y:auto;

    padding:40px;

    background:
        radial-gradient(
            circle at 50% 50%,
            rgba(16,185,129,.03) 0%,
            transparent 100%
        );
}

/* ============================================================
WELCOME
============================================================ */

.executive-header{

    display:flex;
    justify-content:space-between;
    align-items:flex-end;

    margin-bottom:40px;

    border-left:4px solid var(--emerald);

    padding-left:25px;
}

.glitch-text{

    font-size:2.5rem;
    font-weight:900;

    letter-spacing:-1px;

    text-transform:uppercase;
}

.stat-monitor{

    min-width:220px;

    padding:20px 30px;

    background:var(--bg-glass);

    border:1px solid var(--border-light);

    border-radius:20px;

    text-align:right;
}

.stat-value{

    font-family:'JetBrains Mono';

    font-size:2.2rem;
    font-weight:800;

    color:var(--emerald);
}

/* ============================================================
BUTTONS
============================================================ */

.prime-btn{

    padding:18px 35px;

    border:none;
    border-radius:10px;

    cursor:pointer;

    background:var(--emerald);

    color:#000;

    font-size:.75rem;
    font-weight:800;

    text-transform:uppercase;

    transition:.3s;

    box-shadow:0 5px 25px rgba(16,185,129,.3);
}

.prime-btn:hover{

    transform:translateY(-3px);

    box-shadow:0 0 30px var(--emerald);
}

.logout-btn{

    width:100%;

    padding:12px;

    border-radius:10px;

    cursor:pointer;

    border:1px solid rgba(248,113,113,.2);

    background:rgba(248,113,113,.05);

    color:var(--danger);

    font-weight:800;
}

.logout-btn:hover{

    background:var(--danger);

    color:#000;
}

/* ============================================================
ANIMATIONS
============================================================ */

.pulse{

    width:8px;
    height:8px;

    display:inline-block;

    margin-right:10px;

    background:var(--emerald);

    border-radius:50%;

    animation:pulseGlow 2s infinite;
}

@keyframes pulseGlow{

    0%{
        box-shadow:0 0 0 0 rgba(16,185,129,.7);
    }

    70%{
        box-shadow:0 0 0 10px rgba(16,185,129,0);
    }

    100%{
        box-shadow:0 0 0 0 rgba(16,185,129,0);
    }
}

.fade-in-up{

    animation:fadeInUp .6s var(--silk-ease) forwards;
}

@keyframes fadeInUp{

    from{
        opacity:0;
        transform:translateY(20px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

</style>

</head>

<body>

<div class="mesh-bg"></div>

<div class="bg-branding">

    EDULYNTRIXCOREX

</div>

<div class="app-shell">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand-section">

            <div class="logo-box">
                EX
            </div>

            <div class="brand-text">

                EDULYNTRIX

                <span class="accent-x">
                    COREX
                </span>

            </div>

        </div>

        <nav class="navigation-stack">

            <div class="nav-label">
                Operations
            </div>

            <button
                class="nav-link"
                id="nav-enrollment"
                onclick="loadModule('enrollment', this)"
            >

                <i class="fa-solid fa-id-card-clip"></i>

                Enrollment Queue

            </button>

            <button
                class="nav-link"
                id="nav-leave_requests"
                onclick="loadModule('leave_requests', this)"
            >

                <i class="fa-solid fa-envelope-open-text"></i>

                Leave Requests

            </button>

            <div class="nav-label">
                Institutional Control
            </div>

            <button
                class="nav-link"
                id="nav-attendance"
                onclick="loadModule('attendance', this)"
            >

                <i class="fa-solid fa-clipboard-check"></i>

                Attendance Audit

            </button>

            <button
                class="nav-link"
                id="nav-faculty_deployment"
                onclick="loadModule('faculty_deployment', this)"
            >

                <i class="fa-solid fa-user-tie"></i>

                Faculty Deployment

            </button>

            <button
                class="nav-link"
                id="nav-timetable"
                onclick="loadModule('timetable', this)"
            >

                <i class="fa-solid fa-calendar-days"></i>

                Timetable Scheduler

            </button>

            <div class="nav-label">
                Governance
            </div>

            <button
                class="nav-link"
                id="nav-system_status"
                onclick="loadModule('system_status', this)"
            >

                <i class="fa-solid fa-chart-column"></i>

                System Status

            </button>

        </nav>

        <div style="padding:20px;">

            <button
                onclick="confirmTermination()"
                class="logout-btn"
            >

                <i class="fa-solid fa-power-off"></i>

                TERMINATE SESSION

            </button>

        </div>

    </aside>

    <!-- MAIN -->

    <div class="main-wrapper">

        <!-- HEADER -->

        <header class="top-header">

            <div class="search-container">

                <i
                    class="fa-solid fa-magnifying-glass"
                    style="color:var(--emerald)"
                ></i>

                <input
                    type="text"
                    placeholder="Search institutional data nodes..."
                >

            </div>

            <div class="header-right">

                <div class="clock-box">

                    <div id="liveTime">

                        00:00:00

                    </div>

                    <div id="liveDate">

                        LOADING...

                    </div>

                </div>

                <div class="profile-node">

                    <div class="profile-text">

                        <div
                            style="font-weight:700;"
                        >

                            <?= htmlspecialchars($display_name) ?>

                        </div>

                        <div
                            style="
                                font-size:.55rem;
                                color:var(--emerald);
                                font-weight:800;
                                text-transform:uppercase;
                            "
                        >

                            <?= htmlspecialchars($display_dept) ?>

                            COMMAND

                        </div>

                    </div>

                    <img
                        src="<?= $profile_img ?>"
                        class="avatar"
                    >

                </div>

            </div>

        </header>

        <!-- CONTENT -->

        <section
            class="content-body"
            id="mainContent"
        >

            <div class="fade-in-up">

                <div class="executive-header">

                    <div>

                        <h1 class="glitch-text">

                            Welcome,

                            <b style="color:var(--emerald);">

                                <?= explode(' ', $display_name)[0] ?>.

                            </b>

                        </h1>

                        <p
                            style="
                                color:var(--text-muted);
                                font-size:.7rem;
                                letter-spacing:1px;
                                margin-top:10px;
                            "
                        >

                            <span class="pulse"></span>

                            AUTH_NODE:

                            <b style="color:var(--emerald)">

                                <?= htmlspecialchars($display_dept) ?>

                            </b>

                        </p>

                    </div>

                    <div class="stat-monitor">

                        <div
                            style="
                                font-size:.6rem;
                                color:var(--text-muted);
                                text-transform:uppercase;
                                font-weight:800;
                            "
                        >

                            System Load

                        </div>

                        <div
                            class="stat-value"
                            id="statCounter"
                        >

                            100%

                        </div>

                    </div>

                </div>

                <button
                    class="prime-btn"
                    onclick="loadModule('enrollment', document.getElementById('nav-enrollment'))"
                >

                    INITIALIZE ENROLLMENT REGISTRY

                </button>

            </div>

        </section>

    </div>

</div>

<!-- ============================================================
JAVASCRIPT
============================================================ -->

<script>

/* ============================================================
ROOTS
============================================================ */

const DASHBOARD_ROOT =
    '/edulyntrixcorex/public/frontend/staff/hod';

/* ============================================================
LIVE CLOCK
============================================================ */

function runClock(){

    const now = new Date();

    document.getElementById('liveTime')
        .innerText =
            now.toLocaleTimeString(
                'en-GB',
                { hour12:false }
            );

    document.getElementById('liveDate')
        .innerText =
            now.toLocaleDateString(
                'en-GB',
                {
                    weekday:'short',
                    day:'2-digit',
                    month:'short',
                    year:'numeric'
                }
            ).toUpperCase();
}

setInterval(runClock, 1000);

runClock();

/* ============================================================
MODULE LOADER
============================================================ */

function loadModule(module, btn){

    /*
    =========================================================
    ACTIVE STATE
    =========================================================
    */

    document
        .querySelectorAll('.nav-link')
        .forEach(link => {

            link.classList.remove('active');
        });

    if(btn){
        btn.classList.add('active');
    }

    /*
    =========================================================
    LOADER
    =========================================================
    */

    const mainContent =
        document.getElementById('mainContent');

    mainContent.innerHTML = `

        <div style="
            color:var(--emerald);
            font-family:JetBrains Mono;
            padding:40px;
            font-size:.8rem;
        ">

            <i class="fa-solid fa-sync fa-spin"></i>

            [ ACCESSING NODE:
            ${module.toUpperCase()} ]

        </div>

    `;

    /*
    =========================================================
    FETCH MODULE
    =========================================================
    */

    fetch(

        `${DASHBOARD_ROOT}/modules/${module}.php`

    )

    .then(response => {

        if(!response.ok){

            throw new Error(
                'MODULE_NOT_FOUND'
            );
        }

        return response.text();
    })

    .then(data => {

        mainContent.innerHTML =
            `<div class="fade-in-up">${data}</div>`;

        /*
        =====================================================
        EXECUTE INLINE SCRIPTS
        =====================================================
        */

        const scripts =
            mainContent.querySelectorAll('script');

        scripts.forEach(oldScript => {

            const newScript =
                document.createElement('script');

            if(oldScript.src){

                newScript.src = oldScript.src;

            } else {

                newScript.text =
                    oldScript.textContent;
            }

            document.body.appendChild(newScript);

            oldScript.remove();
        });
    })

    .catch(error => {

        console.error(error);

        mainContent.innerHTML = `

            <div style="
                color:#f87171;
                padding:40px;
                font-family:JetBrains Mono;
            ">

                CRITICAL ERROR:
                MODULE NODE OFFLINE

                <br><br>

                PATH:
                ${DASHBOARD_ROOT}/modules/${module}.php

            </div>

        `;
    });
}

/* ============================================================
LOGOUT
============================================================ */

function confirmTermination(){

    if(
        confirm(
            'TERMINATE SESSION?'
        )
    ){

        window.location.href =
            '../../../../includes/logout.php';
    }
}

</script>

</body>
</html>