<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X - ATTENDANCE UI
 * FINAL STABLE VERSION
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/../../../../includes/db_connect.php';

/* ============================================================
FACULTY SESSION
============================================================ */

$staff_id =

    $_SESSION['staff_id']

    ?? $_SESSION['login_id']

    ?? null;

if (!$staff_id) {

    die("

        <div style='
            padding:20px;
            background:#fff1f2;
            color:#dc2626;
            border-radius:12px;
            border:1px solid #fecdd3;
            font-weight:700;
        '>

            FACULTY_SESSION_NOT_FOUND

        </div>

    ");
}

/* ============================================================
FETCH SUBJECTS
============================================================ */

try {

    $stmt = $pdo->prepare("

        SELECT

            subject_id,
            subject_name,
            subject_code

        FROM subjects

        WHERE

            TRIM(assigned_faculty_id)=?

        ORDER BY subject_code ASC

    ");

    $stmt->execute([
        trim($staff_id)
    ]);

    $my_subjects =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

}
catch(PDOException $e){

    die($e->getMessage());
}
?>

<div class="attendance-container">

    <!-- HEADER -->

    <div style="
        margin-bottom:30px;
        display:flex;
        justify-content:space-between;
        align-items:center;
    ">

        <div>

            <h2 style="
                font-weight:800;
                color:#1e293b;
            ">

                Mark Attendance

            </h2>

            <p style="
                color:#64748b;
                font-size:.85rem;
            ">

                Select subject and begin attendance sync.

            </p>

        </div>

        <div style="
            background:#fff;
            padding:10px 20px;
            border-radius:12px;
            border:1px solid #e2e8f0;
            font-weight:700;
            color:#4f46e5;
        ">

            <?= date('d M, Y') ?>

        </div>

    </div>

    <!-- CONTROL PANEL -->

    <div style="
        background:#fff;
        padding:25px;
        border-radius:20px;
        border:1px solid #e2e8f0;
        margin-bottom:25px;
    ">

        <div style="
            display:grid;
            grid-template-columns:1fr 200px auto;
            gap:20px;
            align-items:flex-end;
        ">

            <!-- SUBJECT -->

            <div>

                <label style="
                    display:block;
                    font-size:.65rem;
                    font-weight:800;
                    color:#64748b;
                    margin-bottom:8px;
                ">

                    Academic Subject

                </label>

                <select
                    id="subject_id"
                    style="
                        width:100%;
                        height:50px;
                        border-radius:12px;
                        border:1px solid #e2e8f0;
                        padding:0 15px;
                    "
                >

                    <option value="">

                        -- Select Assigned Subject --

                    </option>

                    <?php foreach($my_subjects as $sub): ?>

                    <option
                        value="<?= $sub['subject_id'] ?>"
                    >

                        <?= htmlspecialchars($sub['subject_code']) ?>

                        -

                        <?= htmlspecialchars($sub['subject_name']) ?>

                    </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- SESSION -->

            <div>

                <label style="
                    display:block;
                    font-size:.65rem;
                    font-weight:800;
                    color:#64748b;
                    margin-bottom:8px;
                ">

                    Session Start

                </label>

                <input
                    type="time"
                    id="session_start_time"
                    value="<?= date('H:i') ?>"
                    style="
                        width:100%;
                        height:50px;
                        border-radius:12px;
                        border:1px solid #e2e8f0;
                        padding:0 15px;
                    "
                >

            </div>

            <!-- BUTTON -->

            <button
                onclick="fetchStudentList()"
                style="
                    height:50px;
                    width:160px;
                    border:none;
                    border-radius:12px;
                    background:#4f46e5;
                    color:#fff;
                    font-weight:700;
                    cursor:pointer;
                "
            >

                LOAD ROLL

            </button>

        </div>

    </div>

    <!-- STUDENT CONTAINER -->

    <div id="studentListContainer">

        <?php if(empty($my_subjects)): ?>

        <div style="
            text-align:center;
            padding:60px;
            color:#f59e0b;
            border:2px dashed #fcd34d;
            border-radius:20px;
            background:#fffbeb;
        ">

            No Subjects Assigned

        </div>

        <?php else: ?>

        <div style="
            text-align:center;
            padding:60px;
            color:#94a3b8;
            border:2px dashed #e2e8f0;
            border-radius:20px;
        ">

            Waiting For Subject Selection...

        </div>

        <?php endif; ?>

    </div>

</div>

<style>

.status-chip{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    width:55px;

    height:38px;

    border-radius:10px;

    background:#f1f5f9;

    color:#475569;

    font-size:.75rem;

    font-weight:800;

    cursor:pointer;

    transition:.25s;
}

.status-chip.active{

    color:#fff;

    transform:scale(1.05);

    box-shadow:
        0 0 18px rgba(79,70,229,.35);
}

</style>

<script>

/* ============================================================
FETCH STUDENTS
============================================================ */

function fetchStudentList(){

    /*
    =========================================================
    INPUTS
    =========================================================
    */

    const subjectId =
        document.getElementById(
            'subject_id'
        ).value;

    const sessionStart =
        document.getElementById(
            'session_start_time'
        ).value;

    /*
    =========================================================
    VALIDATION
    =========================================================
    */

    if(!subjectId){

        alert(
            'Please select subject.'
        );

        return;
    }

    /*
    =========================================================
    CONTAINER
    =========================================================
    */

    const container =
        document.getElementById(
            'studentListContainer'
        );

    /*
    =========================================================
    LOADING
    =========================================================
    */

    container.innerHTML = `

        <div style="
            text-align:center;
            padding:60px;
            color:#94a3b8;
        ">

            Loading Students...

        </div>

    `;

    /*
    =========================================================
    CORRECT ABSOLUTE PATH
    =========================================================
    */

    const endpoint =

        '/EdulyntrixCoreX/public/frontend/staff/modules/attendance_processor.php'

        +

        '?action=fetch_students'

        +

        '&subject_id='

        +

        encodeURIComponent(subjectId)

        +

        '&custom_start='

        +

        encodeURIComponent(sessionStart);

    /*
    =========================================================
    FETCH
    =========================================================
    */

    fetch(endpoint)

    .then(response => {

        if(!response.ok){

            throw new Error(
                'HTTP_' + response.status
            );
        }

        return response.text();
    })

    .then(data => {

        container.innerHTML = data;

        activateAttendanceChips();

    })

    .catch(error => {

        console.error(error);

        container.innerHTML = `

            <div style="
                text-align:center;
                padding:60px;
                color:#dc2626;
                background:#fff1f2;
                border-radius:20px;
                border:1px solid #fecaca;
                font-weight:700;
            ">

                ATTENDANCE_LOAD_FAILURE

                <br><br>

                ${error.message}

            </div>

        `;
    });
}

/* ============================================================
ACTIVATE STATUS BUTTONS
============================================================ */

function activateAttendanceChips(){

    const radios =

        document.querySelectorAll(
            '.att-radio-input'
        );

    radios.forEach(radio => {

        radio.addEventListener(
            'change',
            function(){

                const row =
                    this.closest('tr');

                row.querySelectorAll(
                    '.status-chip'
                ).forEach(chip => {

                    chip.classList.remove(
                        'active'
                    );

                    chip.style.background =
                        '#f1f5f9';
                });

                const chip =
                    this.nextElementSibling;

                chip.classList.add(
                    'active'
                );

                const val =
                    this.value;

                if(val === 'present'){
                    chip.style.background='#16a34a';
                }

                if(val === 'absent'){
                    chip.style.background='#dc2626';
                }

                if(val === 'late'){
                    chip.style.background='#f59e0b';
                }

                if(val === 'leave'){
                    chip.style.background='#0891b2';
                }
            }
        );

        if(radio.checked){

            radio.dispatchEvent(
                new Event('change')
            );
        }
    });
}

</script>