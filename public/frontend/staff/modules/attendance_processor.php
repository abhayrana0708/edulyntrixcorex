<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X - ATTENDANCE PROCESSOR
 * FINAL LIVE SESSION VERSION
 * FULL PHP + HTML + CSS + JS
 * ============================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../includes/db_connect.php';

/* ============================================================
REQUEST
============================================================ */

$action = $_GET['action'] ?? '';

/* ============================================================
SESSION
============================================================ */

$staff_id = null;

if (!empty($_SESSION['staff_id'])) {

    $staff_id = trim($_SESSION['staff_id']);

} elseif (!empty($_SESSION['login_id'])) {

    $staff_id = trim($_SESSION['login_id']);

} elseif (!empty($_SESSION['faculty_id'])) {

    $staff_id = trim($_SESSION['faculty_id']);
}

/* ============================================================
AUTH
============================================================ */

if (
    !$staff_id &&
    in_array($action, ['fetch_students', 'save_attendance'])
) {

    header('Content-Type: application/json');

    echo json_encode([
        'status' => 'error',
        'message' => 'FACULTY_SESSION_NOT_FOUND'
    ]);

    exit;
}

/* ============================================================
FETCH STUDENTS
============================================================ */

if ($action === 'fetch_students') {

    $subject_id = $_GET['subject_id'] ?? 0;
    $today      = date('Y-m-d');

    try {

        $verify = $pdo->prepare("

            SELECT
                subject_id,
                dept_id,
                semester,
                academic_year

            FROM subjects

            WHERE
                subject_id = ?
                AND (
                    TRIM(assigned_faculty_id) = ?
                    OR assigned_faculty_id IS NULL
                )

            LIMIT 1

        ");

        $verify->execute([
            $subject_id,
            trim($staff_id)
        ]);

        $subject_meta =
            $verify->fetch(PDO::FETCH_ASSOC);

        if (!$subject_meta) {

            echo "

            <div class='error-box'>

                SUBJECT_ACCESS_DENIED

            </div>

            ";

            exit;
        }

        $stmt = $pdo->prepare("

            SELECT

                s.student_id,
                s.full_name,

                (
                    SELECT COUNT(*)

                    FROM leave_requests l

                    WHERE
                        l.student_id = s.student_id
                        AND l.status = 'Approved'
                        AND ? BETWEEN l.start_date AND l.end_date

                ) AS on_leave

            FROM students s

            WHERE
                s.dept_id = ?
                AND s.current_semester = ?
                AND s.status = 'Active'

            ORDER BY s.student_id ASC

        ");

        $stmt->execute([

            $today,

            $subject_meta['dept_id'],

            $subject_meta['semester']

        ]);

        $students =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$students) {

            echo "

            <div class='empty-box'>

                NO STUDENTS FOUND

            </div>

            ";

            exit;
        }

        ?>

        <style>

        .att-wrapper{
            width:100%;
            margin-top:20px;
        }

        .live-session-box{

            margin-bottom:20px;

            padding:16px 22px;

            border-radius:16px;

            background:#0f172a;

            color:#38bdf8;

            font-weight:800;

            letter-spacing:1px;

            box-shadow:
                0 0 25px rgba(56,189,248,.18);

            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .pulse-dot{

            width:10px;
            height:10px;

            border-radius:50%;

            background:#22c55e;

            box-shadow:
                0 0 15px #22c55e;

            animation:pulse 1.2s infinite;
        }

        @keyframes pulse{

            0%{
                transform:scale(1);
                opacity:1;
            }

            50%{
                transform:scale(1.4);
                opacity:.5;
            }

            100%{
                transform:scale(1);
                opacity:1;
            }
        }

        .att-table{
            width:100%;
            border-collapse:collapse;
            overflow:hidden;
            border-radius:18px;
            background:#ffffff;
            box-shadow:0 10px 30px rgba(0,0,0,.06);
        }

        .att-table thead tr{
            background:#f8fafc;
        }

        .att-table th{
            padding:18px;
            text-align:left;
            color:#475569;
            font-size:.72rem;
            text-transform:uppercase;
            letter-spacing:1px;
        }

        .att-table td{
            padding:18px;
            border-top:1px solid #f1f5f9;
        }

        .student-id{
            font-weight:800;
            color:#4f46e5;
            font-family:monospace;
            font-size:.85rem;
        }

        .student-name{
            margin-top:6px;
            font-weight:600;
            color:#0f172a;
        }

        .status-group{
            display:flex;
            gap:10px;
            justify-content:center;
            flex-wrap:wrap;
        }

        .att-radio-input{
            position:absolute;
            opacity:0;
            pointer-events:none;
        }

        .status-chip{

            display:flex;
            align-items:center;
            justify-content:center;

            width:58px;
            height:42px;

            border-radius:12px;

            background:#f1f5f9;

            color:#475569;

            font-size:.76rem;
            font-weight:900;

            cursor:pointer;

            transition:.22s ease;

            user-select:none;

            border:2px solid transparent;
        }

        .status-chip:hover{
            transform:translateY(-2px);
        }

        .commit-btn{

            margin-top:25px;

            padding:15px 35px;

            border:none;

            border-radius:14px;

            background:linear-gradient(
                135deg,
                #4f46e5,
                #312e81
            );

            color:#fff;

            font-weight:800;

            cursor:pointer;

            transition:.25s ease;
        }

        .commit-btn:hover{
            transform:translateY(-2px);
        }

        .empty-box,
        .error-box{

            padding:35px;

            border-radius:18px;

            text-align:center;

            font-weight:700;
        }

        .empty-box{
            background:#f8fafc;
            color:#64748b;
        }

        .error-box{
            background:#fff1f2;
            color:#dc2626;
        }

        </style>

        <div class="att-wrapper">

            <div class="live-session-box">

                <div style="
                    display:flex;
                    align-items:center;
                    gap:12px;
                ">

                    <div class="pulse-dot"></div>

                    LIVE ATTENDANCE SESSION

                </div>

                <div id="liveAttendanceTimer">

                    20:00

                </div>

            </div>

            <form id="attendanceFinalForm">

                <input
                    type="hidden"
                    name="subject_id"
                    value="<?= htmlspecialchars($subject_id) ?>"
                >

                <input
                    type="hidden"
                    name="att_date"
                    value="<?= $today ?>"
                >

                <table class="att-table">

                    <thead>

                        <tr>
                            <th>Student</th>
                            <th style="text-align:center;">
                                Attendance
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($students as $stu): ?>

                        <tr>

                            <td>

                                <div class="student-id">
                                    <?= $stu['student_id'] ?>
                                </div>

                                <div class="student-name">
                                    <?= htmlspecialchars($stu['full_name']) ?>
                                </div>

                            </td>

                            <td>

                                <div class="status-group">

                                    <?= renderAttOption(
                                        $stu['student_id'],
                                        'present',
                                        'P',
                                        true,
                                        '#16a34a'
                                    ) ?>

                                    <?= renderAttOption(
                                        $stu['student_id'],
                                        'absent',
                                        'A',
                                        false,
                                        '#dc2626'
                                    ) ?>

                                    <?= renderAttOption(
                                        $stu['student_id'],
                                        'late',
                                        'LT',
                                        false,
                                        '#f59e0b'
                                    ) ?>

                                    <?= renderAttOption(
                                        $stu['student_id'],
                                        'leave',
                                        'LV',
                                        false,
                                        '#0891b2'
                                    ) ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

                <button
                    type="button"
                    class="commit-btn"
                    id="commitBtn"
                    onclick="saveAttendance(event)"
                >

                    ☁ COMMIT ROLL CALL

                </button>

            </form>

        </div>

        <script>

        let attendanceExpiry = null;

        function startAttendanceTimer(minutes = 20){

            attendanceExpiry =
                Date.now()
                + (minutes * 60 * 1000);

            updateAttendanceTimer();

            setInterval(
                updateAttendanceTimer,
                1000
            );
        }

        function updateAttendanceTimer(){

            const timer =
                document.getElementById(
                    'liveAttendanceTimer'
                );

            const commitBtn =
                document.getElementById(
                    'commitBtn'
                );

            if(!timer) return;

            const remaining =
                attendanceExpiry - Date.now();

            if(remaining <= 0){

                timer.innerHTML =
                    'SESSION EXPIRED';

                timer.style.color =
                    '#ef4444';

                if(commitBtn){

                    commitBtn.disabled = true;

                    commitBtn.innerHTML =
                        'SESSION CLOSED';

                    commitBtn.style.background =
                        '#64748b';
                }

                document.querySelectorAll(
                    '.status-chip'
                ).forEach(chip => {

                    chip.style.pointerEvents =
                        'none';

                    chip.style.opacity =
                        '.5';
                });

                return;
            }

            const mins =
                Math.floor(
                    remaining / 1000 / 60
                );

            const secs =
                Math.floor(
                    (remaining / 1000) % 60
                );

            timer.innerHTML =
                `${mins}:${secs
                    .toString()
                    .padStart(2,'0')}`;
        }

        function activateAttendanceChips(){

            const radios =
                document.querySelectorAll(
                    '.att-radio-input'
                );

            radios.forEach(radio => {

                const updateState = () => {

                    const row =
                        radio.closest('tr');

                    row.querySelectorAll(
                        '.status-chip'
                    ).forEach(chip => {

                        chip.style.background =
                            '#f1f5f9';

                        chip.style.color =
                            '#475569';

                        chip.style.transform =
                            'scale(1)';

                        chip.style.boxShadow =
                            'none';

                        chip.style.border =
                            '2px solid transparent';
                    });

                    if(radio.checked){

                        const chip =
                            document.querySelector(
                                `label[for="${radio.id}"]`
                            );

                        const color =
                            radio.dataset.color;

                        chip.style.background =
                            color;

                        chip.style.color =
                            '#ffffff';

                        chip.style.transform =
                            'scale(1.08)';

                        chip.style.boxShadow =
                            `0 0 22px ${color}99`;

                        chip.style.border =
                            `2px solid ${color}`;
                    }
                };

                radio.addEventListener(
                    'change',
                    updateState
                );

                updateState();
            });
        }

        async function saveAttendance(event){

            event.preventDefault();

            const form =
                document.getElementById(
                    'attendanceFinalForm'
                );

            const btn =
                document.getElementById(
                    'commitBtn'
                );

            const formData =
                new FormData(form);

            btn.disabled = true;

            btn.innerHTML =
                'SYNCING ATTENDANCE...';

            try{

                const response = await fetch(

                    window.location.pathname
                    + '?action=save_attendance',

                    {
                        method:'POST',
                        body:formData
                    }
                );

                const result =
                    await response.json();

                if(result.status === 'success'){

                    btn.innerHTML =
                        '✓ ATTENDANCE COMMITTED';

                    btn.style.background =
                        '#16a34a';

                    btn.style.boxShadow =
                        '0 0 25px rgba(22,163,74,.55)';

                } else {

                    btn.disabled = false;

                    btn.innerHTML =
                        '☁ COMMIT ROLL CALL';

                    alert(result.message);
                }

            } catch(err){

                btn.disabled = false;

                btn.innerHTML =
                    '☁ COMMIT ROLL CALL';

                alert('NETWORK FAILURE');
            }
        }

        document.addEventListener(
            'DOMContentLoaded',
            () => {

                activateAttendanceChips();

                startAttendanceTimer(20);
            }
        );

        </script>

        <?php

    } catch (PDOException $e) {

        echo "

        <div class='error-box'>

            " . htmlspecialchars($e->getMessage()) . "

        </div>

        ";
    }

    exit;
}

/* ============================================================
SAVE ATTENDANCE
============================================================ */

if ($action === 'save_attendance') {

    header('Content-Type: application/json');

    $subject_id = $_POST['subject_id'] ?? '';
    $date = $_POST['att_date'] ?? date('Y-m-d');
    $attendance_data = $_POST['attendance'] ?? [];

    if (
        empty($subject_id) ||
        empty($attendance_data)
    ) {

        echo json_encode([
            'status'=>'error',
            'message'=>'INVALID_PAYLOAD'
        ]);

        exit;
    }

    try {

        $verify = $pdo->prepare("

            SELECT
                dept_id,
                academic_year

            FROM subjects

            WHERE subject_id=?

            LIMIT 1

        ");

        $verify->execute([$subject_id]);

        $meta = $verify->fetch(PDO::FETCH_ASSOC);

        $pdo->beginTransaction();

        $delete = $pdo->prepare("

            DELETE FROM attendance

            WHERE
                subject_id=?
                AND attendance_date=?

        ");

        $delete->execute([
            $subject_id,
            $date
        ]);

        $insert = $pdo->prepare("

            INSERT INTO attendance (

                student_id,
                subject_id,
                dept_id,
                academic_year,
                status,
                attendance_date,
                created_at,
                session_start_time

            )

            VALUES (

                ?, ?, ?, ?, ?, ?, NOW(), ?

            )

        ");

        foreach($attendance_data as $sid => $status){

            $insert->execute([

                trim($sid),

                $subject_id,

                $meta['dept_id'],

                $meta['academic_year'],

                strtolower(trim($status)),

                $date,

                date('Y-m-d H:i:s')
            ]);
        }

        $pdo->commit();

        echo json_encode([
            'status'=>'success'
        ]);

    } catch(Exception $e){

        if($pdo->inTransaction()){
            $pdo->rollBack();
        }

        echo json_encode([
            'status'=>'error',
            'message'=>$e->getMessage()
        ]);
    }

    exit;
}

/* ============================================================
RENDER OPTIONS
============================================================ */

function renderAttOption(
    $stu_id,
    $val,
    $label,
    $checked,
    $color
){

    $input_id =
        'att_' .
        $stu_id .
        '_' .
        $val;

    $is_checked =
        $checked
        ? 'checked'
        : '';

    return "

    <div>

        <input
            type='radio'
            id='$input_id'
            name='attendance[$stu_id]'
            value='$val'
            class='att-radio-input'
            data-color='$color'
            $is_checked
        >

        <label
            for='$input_id'
            class='status-chip'
        >

            $label

        </label>

    </div>

    ";
}
?>