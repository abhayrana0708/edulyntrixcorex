<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * FACULTY TIMETABLE MODULE
 * FULL FIXED STABLE VERSION
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

/* ============================================================
DATABASE
============================================================ */

require_once __DIR__
. '/../../../../includes/db_connect.php';

/* ============================================================
FIXED SESSION RESOLUTION
============================================================ */

$session_user =

    $_SESSION['staff_id']

    ?? $_SESSION['login_id']

    ?? $_SESSION['faculty_id']

    ?? null;

if (!$session_user) {

    echo "

        <div class='silk-error'>

            SESSION_EXPIRED

        </div>

    ";

    exit;
}

/* ============================================================
FETCH STAFF
============================================================ */

try {

    $staffQuery = $pdo->prepare("

        SELECT

            staff_id,
            full_name,
            department

        FROM staff

        WHERE

            TRIM(staff_id)=?

            OR

            TRIM(login_id)=?

        LIMIT 1

    ");

    $staffQuery->execute([

        trim($session_user),

        trim($session_user)

    ]);

    $staffData =
        $staffQuery->fetch(PDO::FETCH_ASSOC);

    if (!$staffData) {

        echo "

            <div class='silk-error'>

                FACULTY_NOT_FOUND

            </div>

        ";

        exit;
    }

    $staff_id =
        trim($staffData['staff_id']);

}
catch(PDOException $e){

    echo "

        <div class='silk-error'>

            STAFF_RESOLUTION_FAILED

        </div>

    ";

    exit;
}

/* ============================================================
DAY MATRIX
============================================================ */

$days_map = [

    'Mon' => 'Monday',
    'Tue' => 'Tuesday',
    'Wed' => 'Wednesday',
    'Thu' => 'Thursday',
    'Fri' => 'Friday',
    'Sat' => 'Saturday'

];

$currentDayShort =
    date('D');

$currentTime =
    date('H:i:s');

/* ============================================================
FETCH TIMETABLE
============================================================ */

try {

    $stmt = $pdo->prepare("

        SELECT

            TRIM(t.day_of_week) AS day_key,

            t.id,
            t.day_of_week,
            t.start_time,
            t.end_time,
            t.room_no,
            t.lecture_type,

            s.subject_name,
            s.subject_code

        FROM timetable t

        INNER JOIN subjects s

            ON t.subject_id = s.subject_id

        WHERE

            TRIM(s.assigned_faculty_id)=?

        ORDER BY

            FIELD(

                TRIM(t.day_of_week),

                'Mon',
                'Tue',
                'Wed',
                'Thu',
                'Fri',
                'Sat'

            ),

            t.start_time ASC

    ");

    $stmt->execute([
        $staff_id
    ]);

    $rows =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    =========================================================
    SAFE GROUPING
    =========================================================
    */

    $schedule = [];

    foreach($rows as $row){

        $key =
            trim($row['day_key']);

        if(!isset($schedule[$key])){

            $schedule[$key] = [];
        }

        $schedule[$key][] = $row;
    }

}
catch(PDOException $e){

    echo "

        <div class='silk-error'>

            TIMETABLE_FETCH_FAILED

            <br><br>

            " . htmlspecialchars($e->getMessage()) . "

        </div>

    ";

    exit;
}

?>

<!-- =========================================================
MAIN CONTAINER
========================================================= -->

<div class="silk-timetable-container fade-in">

    <!-- HEADER -->

    <div class="silk-header">

        <div class="title-group">

            <h2 class="main-title">

                Academic

                <span class="highlight">

                    Schedule

                </span>

            </h2>

            <div class="time-stamp">

                <i class="fa-solid fa-clock"></i>

                Internal Node Time:

                <strong>

                    <?= date('H:i') ?>

                </strong>

            </div>

        </div>

        <div class="sync-badge">

            <div class="pulse-dot"></div>

            CORE LIVE SYNC

        </div>

    </div>

    <!-- GRID -->

    <div class="timetable-grid">

        <?php foreach($days_map as $short => $full): ?>

        <?php

        $isToday =
            ($short === $currentDayShort);

        ?>

        <div class="day-column <?= $isToday ? 'active-day' : '' ?>">

            <!-- DAY HEADER -->

            <div class="day-header">

                <span class="day-name">

                    <?= strtoupper($full) ?>

                </span>

                <?php if($isToday): ?>

                <span class="today-pill">

                    ACTIVE TODAY

                </span>

                <?php endif; ?>

            </div>

            <!-- LECTURES -->

            <div class="lecture-stack">

                <?php if(!empty($schedule[$short])): ?>

                    <?php foreach($schedule[$short] as $lecture): ?>

                    <?php

                    $isLive =

                        $isToday

                        &&

                        $currentTime >= $lecture['start_time']

                        &&

                        $currentTime <= $lecture['end_time'];

                    ?>

                    <div class="lecture-card
                        <?= strtolower(
                            $lecture['lecture_type']
                            ?? 'theory'
                        ) ?>

                        <?= $isLive
                            ? 'live-now'
                            : '' ?>">

                        <?php if($isLive): ?>

                        <div class="live-indicator">

                            <span class="live-dot"></span>

                            LIVE NOW

                        </div>

                        <?php endif; ?>

                        <!-- TIME -->

                        <div class="time-meta">

                            <?= date(
                                'H:i',
                                strtotime(
                                    $lecture['start_time']
                                )
                            ) ?>

                            —

                            <?= date(
                                'H:i',
                                strtotime(
                                    $lecture['end_time']
                                )
                            ) ?>

                        </div>

                        <!-- SUBJECT -->

                        <div class="sub-title">

                            <?= htmlspecialchars(
                                $lecture['subject_name']
                            ) ?>

                        </div>

                        <!-- CODE -->

                        <div class="subject-code">

                            <?= htmlspecialchars(
                                $lecture['subject_code']
                            ) ?>

                        </div>

                        <!-- FOOTER -->

                        <div class="card-footer">

                            <div class="room">

                                <i class="fa-solid fa-location-dot"></i>

                                RM:

                                <?= htmlspecialchars(
                                    $lecture['room_no']
                                    ?? 'NA'
                                ) ?>

                            </div>

                            <div class="type-tag">

                                <?= strtoupper(
                                    htmlspecialchars(
                                        $lecture['lecture_type']
                                        ?? 'Theory'
                                    )
                                ) ?>

                            </div>

                        </div>

                    </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <!-- EMPTY -->

                    <div class="empty-node">

                        <i class="fa-solid fa-calendar-minus"></i>

                        <p>

                            No Sessions

                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

</div>

<!-- =========================================================
STYLE
========================================================= -->

<style>

/* ============================================================
ROOT
============================================================ */

:root{

    --s-bg:#0f172a;

    --s-card:rgba(30,41,59,0.55);

    --s-emerald:#10b981;

    --s-indigo:#6366f1;

    --s-border:rgba(255,255,255,0.06);

    --s-text:#e2e8f0;

    --s-text-dim:#64748b;
}

/* ============================================================
CONTAINER
============================================================ */

.silk-timetable-container{

    color:var(--s-text);

    animation:fadeIn .4s ease;
}

/* ============================================================
HEADER
============================================================ */

.silk-header{

    display:flex;

    justify-content:space-between;

    align-items:flex-end;

    margin-bottom:35px;

    border-bottom:1px solid var(--s-border);

    padding-bottom:20px;
}

.main-title{

    font-size:1.8rem;

    font-weight:800;

    margin:0;
}

.highlight{

    color:var(--s-emerald);

    text-shadow:
        0 0 20px rgba(16,185,129,.25);
}

.time-stamp{

    margin-top:8px;

    font-size:.72rem;

    color:var(--s-text-dim);
}

.sync-badge{

    background:
        rgba(16,185,129,.08);

    color:var(--s-emerald);

    border:
        1px solid rgba(16,185,129,.18);

    padding:8px 14px;

    border-radius:8px;

    font-size:.65rem;

    font-weight:900;

    display:flex;

    align-items:center;

    gap:8px;
}

.pulse-dot{

    width:6px;

    height:6px;

    border-radius:50%;

    background:var(--s-emerald);

    box-shadow:
        0 0 12px var(--s-emerald);

    animation:pulse 1.2s infinite;
}

/* ============================================================
GRID
============================================================ */

.timetable-grid{

    display:grid;

    grid-template-columns:
        repeat(auto-fit,minmax(230px,1fr));

    gap:18px;
}

.day-column{

    border-radius:16px;

    padding:12px;
}

.active-day{

    background:
        rgba(99,102,241,.05);

    box-shadow:
        inset 0 0 25px rgba(99,102,241,.03);
}

.day-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:15px;

    padding:0 5px;
}

.day-name{

    font-size:.72rem;

    font-weight:800;

    letter-spacing:1px;

    color:var(--s-text-dim);
}

.today-pill{

    background:var(--s-indigo);

    color:#fff;

    font-size:.52rem;

    padding:3px 7px;

    border-radius:5px;

    font-weight:900;
}

/* ============================================================
LECTURE CARD
============================================================ */

.lecture-card{

    background:var(--s-card);

    border:
        1px solid var(--s-border);

    border-left:
        3px solid #334155;

    border-radius:14px;

    padding:16px;

    margin-bottom:12px;

    position:relative;

    transition:.3s ease;
}

.lecture-card:hover{

    transform:translateY(-3px);

    border-color:var(--s-emerald);

    background:
        rgba(30,41,59,.75);
}

.lecture-card.live-now{

    border-left-color:var(--s-emerald);

    background:
        rgba(16,185,129,.08);

    box-shadow:
        0 10px 25px -10px rgba(16,185,129,.35);
}

.lecture-card.theory{
    border-left-color:var(--s-indigo);
}

.lecture-card.practical{
    border-left-color:var(--s-emerald);
}

.lecture-card.major{
    border-left-color:#f59e0b;
}

/* ============================================================
LIVE
============================================================ */

.live-indicator{

    position:absolute;

    top:12px;

    right:12px;

    background:
        rgba(16,185,129,.1);

    color:var(--s-emerald);

    font-size:.55rem;

    font-weight:900;

    padding:3px 7px;

    border-radius:4px;

    display:flex;

    align-items:center;

    gap:4px;
}

.live-dot{

    width:5px;

    height:5px;

    border-radius:50%;

    background:var(--s-emerald);

    animation:blink 1s infinite;
}

/* ============================================================
META
============================================================ */

.time-meta{

    color:var(--s-emerald);

    font-size:.68rem;

    font-weight:800;

    font-family:'JetBrains Mono', monospace;

    margin-bottom:8px;
}

.sub-title{

    color:#fff;

    font-size:.95rem;

    font-weight:700;

    line-height:1.25;
}

.subject-code{

    margin-top:6px;

    color:#94a3b8;

    font-size:.68rem;

    font-family:'JetBrains Mono', monospace;
}

.card-footer{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-top:15px;

    padding-top:10px;

    border-top:
        1px solid rgba(255,255,255,.05);
}

.room{

    color:var(--s-text-dim);

    font-size:.65rem;

    font-weight:700;
}

.type-tag{

    background:
        rgba(255,255,255,.06);

    color:#cbd5e1;

    padding:4px 8px;

    border-radius:5px;

    font-size:.55rem;

    font-weight:900;
}

/* ============================================================
EMPTY
============================================================ */

.empty-node{

    border:
        1px dashed rgba(255,255,255,.08);

    border-radius:14px;

    padding:30px 10px;

    text-align:center;

    color:#475569;

    font-size:.72rem;
}

.empty-node i{

    display:block;

    margin-bottom:10px;

    font-size:1.4rem;

    opacity:.5;
}

/* ============================================================
ERROR
============================================================ */

.silk-error{

    background:#0f172a;

    color:#f87171;

    border:
        1px solid rgba(248,113,113,.25);

    border-radius:12px;

    padding:20px;

    font-family:monospace;
}

/* ============================================================
ANIMATION
============================================================ */

@keyframes blink{

    0%,100%{
        opacity:1;
    }

    50%{
        opacity:.35;
    }
}

@keyframes pulse{

    0%,100%{
        transform:scale(1);
    }

    50%{
        transform:scale(.7);
    }
}

@keyframes fadeIn{

    from{
        opacity:0;
        transform:translateY(10px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

</style>

<!-- =========================================================
SCRIPT
========================================================= -->

<script>

/* ============================================================
LIVE NODE REFRESH
============================================================ */

setInterval(() => {

    const liveCards =
        document.querySelectorAll(
            '.live-now'
        );

    liveCards.forEach(card => {

        card.style.boxShadow =

            '0 0 25px rgba(16,185,129,.25)';
    });

}, 5000);

</script>