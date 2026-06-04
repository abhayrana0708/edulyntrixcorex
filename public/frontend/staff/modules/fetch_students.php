<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * ATTENDANCE SESSION TERMINAL
 * FINAL STABLE VERSION
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

require_once __DIR__
. '/../../../../includes/db_connect.php';

/* ============================================================
VALIDATION
============================================================ */

$subject_id =
    $_GET['subject_id']
    ?? null;

$custom_start =
    $_GET['custom_start']
    ?? date('H:i');

if (!$subject_id) {

    die("

        <div style='
            padding:40px;
            color:#ef4444;
            background:#fff1f2;
            border-radius:20px;
            border:1px solid #fecaca;
            font-weight:700;
            text-align:center;
        '>

            SUBJECT_NOT_SELECTED

        </div>

    ");
}

/* ============================================================
SUBJECT
============================================================ */

$subjectStmt = $pdo->prepare("

    SELECT *

    FROM subjects

    WHERE subject_id = ?

    LIMIT 1

");

$subjectStmt->execute([
    $subject_id
]);

$subject =
    $subjectStmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {

    die("

        <div style='
            padding:40px;
            color:#ef4444;
            background:#fff1f2;
            border-radius:20px;
            border:1px solid #fecaca;
            font-weight:700;
            text-align:center;
        '>

            SUBJECT_NODE_FAILURE

        </div>

    ");
}

/* ============================================================
SESSION TIMER
============================================================ */

$today =
    date('Y-m-d');

$session_epoch =
    strtotime(
        $today
        . ' '
        . $custom_start
    );

$expiry_epoch =
    $session_epoch + (20 * 60);

$is_expired =
    time() > $expiry_epoch;

/* ============================================================
FETCH STUDENTS
============================================================ */

$studentStmt = $pdo->prepare("

    SELECT

        student_id,
        full_name,
        current_semester,
        dept_id

    FROM students

    WHERE

        dept_id = ?

        AND

        current_semester = ?

        AND

        status = 'Active'

    ORDER BY full_name ASC

");

$studentStmt->execute([

    $subject['dept_id'],

    $subject['semester']

]);

$students =
    $studentStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$students) {

    die("

        <div style='
            padding:50px;
            background:#fff;
            border-radius:24px;
            border:1px dashed #cbd5e1;
            text-align:center;
            color:#94a3b8;
            font-weight:700;
        '>

            NO_STUDENTS_AVAILABLE

        </div>

    ");
}
?>

<!-- =========================================================
LIVE SESSION BAR
========================================================= -->

<div id="attendanceSessionBar"
     style="
        background:#241b5a;
        color:#fff;
        padding:18px 24px;
        border-radius:18px;
        margin-bottom:24px;
        display:flex;
        justify-content:space-between;
        align-items:center;
     ">

    <div style="
        display:flex;
        align-items:center;
        gap:12px;
    ">

        <span style="
            width:12px;
            height:12px;
            border-radius:50%;
            background:<?= $is_expired ? '#ef4444' : '#22c55e' ?>;
            box-shadow:
                0 0 10px <?= $is_expired ? '#ef4444' : '#22c55e' ?>;
        "></span>

        <span style="
            font-weight:800;
            letter-spacing:.5px;
        ">

            <?= $is_expired
                ? 'SESSION EXPIRED'
                : 'ATTENDANCE SESSION ACTIVE'
            ?>

        </span>

    </div>

    <div id="attendanceTimer"
         style="
            font-family:'JetBrains Mono';
            color:#facc15;
            font-weight:800;
            font-size:1rem;
         ">

        --:--

    </div>

</div>

<!-- =========================================================
FORM
========================================================= -->

<form id="attendanceForm">

    <input
        type="hidden"
        name="subject_id"
        value="<?= htmlspecialchars($subject_id) ?>"
    >

    <input
        type="hidden"
        name="att_date"
        value="<?= date('Y-m-d') ?>"
    >

    <!-- TABLE -->

    <div style="
        background:#fff;
        border-radius:24px;
        overflow:hidden;
        border:1px solid #e2e8f0;
    ">

        <!-- HEADER -->

        <div style="
            display:grid;
            grid-template-columns:1.5fr 1fr;
            padding:18px;
            background:#f8fafc;
            font-size:.72rem;
            font-weight:800;
            color:#64748b;
            text-transform:uppercase;
        ">

            <div>
                Student Identity
            </div>

            <div>
                Attendance Node
            </div>

        </div>

        <!-- ROWS -->

        <?php foreach($students as $student): ?>

        <div style="
            display:grid;
            grid-template-columns:1.5fr 1fr;
            padding:18px;
            border-top:1px solid #f1f5f9;
            align-items:center;
        ">

            <!-- STUDENT -->

            <div>

                <div style="
                    font-family:'JetBrains Mono';
                    font-weight:800;
                    color:#4f46e5;
                    font-size:1rem;
                ">

                    <?= htmlspecialchars($student['student_id']) ?>

                </div>

                <div style="
                    margin-top:4px;
                    color:#0f172a;
                    font-weight:700;
                ">

                    <?= htmlspecialchars($student['full_name']) ?>

                </div>

            </div>

            <!-- STATUS -->

            <div>

                <div style="
                    display:flex;
                    gap:10px;
                    flex-wrap:wrap;
                ">

                    <?php
                    $statuses = [

                        'present' => 'P',
                        'absent'  => 'A',
                        'late'    => 'LT',
                        'leave'   => 'LV'

                    ];
                    ?>

                    <?php foreach($statuses as $value => $label): ?>

                    <label class="status-wrapper">

                        <input
                            type="radio"
                            name="attendance[<?= $student['student_id'] ?>]"
                            value="<?= $value ?>"
                            <?= $value === 'present'
                                ? 'checked'
                                : ''
                            ?>
                            <?= $is_expired
                                ? 'disabled'
                                : ''
                            ?>
                        >

                        <span class="status-chip">

                            <?= $label ?>

                        </span>

                    </label>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <!-- SUBMIT -->

    <?php if(!$is_expired): ?>

    <div style="
        display:flex;
        justify-content:flex-end;
        margin-top:24px;
    ">

        <button
            type="button"
            onclick="submitAttendance()"
            id="commitAttendanceBtn"
            style="
                background:#4f46e5;
                color:#fff;
                border:none;
                padding:16px 30px;
                border-radius:14px;
                font-weight:800;
                cursor:pointer;
                transition:.3s;
            "
        >

            ☁ COMMIT ROLL CALL

        </button>

    </div>

    <?php endif; ?>

</form>

<!-- =========================================================
STYLE
========================================================= -->

<style>

.status-wrapper{
    cursor:pointer;
}

.status-wrapper input{
    display:none;
}

.status-chip{

    width:58px;
    height:38px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:#f1f5f9;

    border-radius:12px;

    font-size:.78rem;

    font-weight:800;

    color:#475569;

    transition:.25s;

    border:2px solid transparent;
}

.status-chip:hover{

    transform:translateY(-2px);
}

.status-wrapper input:checked + .status-chip{

    background:#4f46e5;

    color:#fff;

    border-color:#312e81;

    box-shadow:
        0 0 18px rgba(79,70,229,.45);

    transform:scale(1.06);
}

.status-wrapper input:disabled + .status-chip{

    opacity:.4;

    cursor:not-allowed;
}

</style>

<!-- =========================================================
SCRIPT
========================================================= -->

<script>

/* ============================================================
LIVE TIMER
============================================================ */

(function(){

    const expiry =
        <?= $expiry_epoch ?>;

    const timer =
        document.getElementById(
            'attendanceTimer'
        );

    function updateTimer(){

        const now =
            Math.floor(Date.now()/1000);

        const diff =
            expiry - now;

        if(diff <= 0){

            timer.innerHTML =
                'EXPIRED';

            timer.style.color =
                '#ef4444';

            clearInterval(loop);

            const btn =
                document.getElementById(
                    'commitAttendanceBtn'
                );

            if(btn){

                btn.disabled = true;

                btn.innerHTML =
                    'SESSION EXPIRED';

                btn.style.opacity = '.5';
            }

            return;
        }

        const mins =
            Math.floor(diff / 60);

        const secs =
            diff % 60;

        timer.innerHTML =

            String(mins).padStart(2,'0')

            +

            ':'

            +

            String(secs).padStart(2,'0');
    }

    updateTimer();

    const loop =
        setInterval(updateTimer,1000);

})();

/* ============================================================
SUBMIT ATTENDANCE
============================================================ */

async function submitAttendance(){

    const form =
        document.getElementById(
            'attendanceForm'
        );

    const btn =
        document.getElementById(
            'commitAttendanceBtn'
        );

    btn.disabled = true;

    btn.innerHTML = 'SYNCING...';

    try {

        const response = await fetch(

            '/EdulyntrixCoreX/public/frontend/staff/modules/attendance_processor.php?action=save_attendance',

            {
                method:'POST',
                body:new FormData(form)
            }
        );

        const result =
            await response.json();

        if(result.status === 'success'){

            alert(
                'Attendance synchronized.'
            );

            btn.innerHTML =
                'SYNC COMPLETE';

            btn.style.background =
                '#22c55e';

        } else {

            alert(
                result.message
                || 'SYNC_FAILURE'
            );

            btn.disabled = false;

            btn.innerHTML =
                '☁ COMMIT ROLL CALL';
        }

    }
    catch(error){

        console.error(error);

        alert(
            'NETWORK_FAILURE'
        );

        btn.disabled = false;

        btn.innerHTML =
            '☁ COMMIT ROLL CALL';
    }
}

</script>