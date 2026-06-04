<?php
/**
 * EDULYNTRIX CORE X - TIMETABLE SCHEDULER
 * FINAL CLEAN STABLE VERSION
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* -----------------------------------------------------------
DATABASE CONNECTION
----------------------------------------------------------- */

$db_path =
    $_SERVER['DOCUMENT_ROOT']
    . '/edulyntrixcorex/includes/db_connect.php';

require_once $db_path;

/* -----------------------------------------------------------
SESSION
----------------------------------------------------------- */

$s_dept_id = $_SESSION['dept_id'] ?? 1;

try {

    /*
    -----------------------------------------------------------
    TIMETABLE FETCH
    -----------------------------------------------------------
    FIX:
    assigned_faculty_id → staff.staff_id
    NOT → staff.id
    -----------------------------------------------------------
    */

    $tt_sql = "

        SELECT

            tt.*,

            s.subject_name,
            s.subject_code,

            st.full_name

        FROM timetable tt

        JOIN subjects s
            ON tt.subject_id = s.subject_id

        LEFT JOIN staff st
            ON s.assigned_faculty_id = st.staff_id

        WHERE tt.dept_id = ?

        ORDER BY FIELD(
            tt.day_of_week,
            'Mon',
            'Tue',
            'Wed',
            'Thu',
            'Fri',
            'Sat'
        ),

        tt.start_time
    ";

    $stmt = $pdo->prepare($tt_sql);

    $stmt->execute([$s_dept_id]);

    $schedule =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    -----------------------------------------------------------
    SUBJECT FETCH
    -----------------------------------------------------------
    */

    $sub_stmt = $pdo->prepare("

        SELECT

            subject_id,
            subject_name,
            subject_code

        FROM subjects

        WHERE dept_id = ?

        ORDER BY subject_code ASC

    ");

    $sub_stmt->execute([$s_dept_id]);

    $subjects =
        $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("
        <div style='
            color:#f87171;
            background:#020617;
            padding:20px;
            border-radius:10px;
            border:1px solid #f87171;
            font-family:monospace;
        '>

            DATABASE_SYNC_FAILED

        </div>
    ");
}
?>

<style>

/* -----------------------------------------------------------
PRINT STABILIZER
----------------------------------------------------------- */

@media print {

    @page {
        size: A4 landscape;
        margin: 12mm;
    }

    html,
    body {
        background: #fff !important;
        width: 100%;
        height: auto;
        overflow: visible !important;
    }

    body * {
        visibility: hidden;
    }

    #printableArea,
    #printableArea * {
        visibility: visible !important;
    }

    #printableArea{
        display:block !important;
    }

    #printableArea {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        background: #fff !important;
        padding: 0;
        margin: 0;
    }

    .pdf-header {
        display: block !important;
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }

    .pdf-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 11px;
    }

    .pdf-table th,
    .pdf-table td {
        border: 1px solid #000;
        padding: 8px;
        word-wrap: break-word;
        vertical-align: top;
    }

    .pdf-table th {
        background: #f2f2f2 !important;
        font-weight: bold;
    }

    .pdf-table td:nth-child(3) {
        width: 28%;
    }

    .no-print {
        display: none !important;
    }

    button,
    nav,
    .sidebar,
    .topbar {
        display: none !important;
    }
}

/* -----------------------------------------------------------
SCREEN MODE
----------------------------------------------------------- */

.pdf-header {
    display: none;
}

/* IMPORTANT FIX */

#printableArea{
    display:none;
}

</style>

<!-- =========================================================
MAIN SCREEN UI
========================================================= -->

<div class="no-print"
     style="display:grid;
            grid-template-columns:350px 1fr;
            gap:25px;
            animation:fadeIn 0.4s ease-out;">

    <!-- LEFT PANEL -->

    <div style="background: rgba(15, 23, 42, 0.95);
                padding: 25px;
                border-radius: 15px;
                border: 1px solid rgba(16, 185, 129, 0.3);
                height: fit-content;">

        <h3 style="color:#10b981;
                   font-size:0.85rem;
                   font-family:'JetBrains Mono';
                   text-transform:uppercase;">

            Inject_Node

        </h3>

        <form id="timetableForm"
              style="display:flex;
                     flex-direction:column;
                     gap:15px;
                     margin-top:20px;">

            <!-- SUBJECT -->

            <div>

                <label style="font-size:0.6rem;
                              color:#64748b;
                              text-transform:uppercase;">

                    Subject

                </label>

                <select name="subject_id"
                        required
                        style="width:100%;
                               background:#020617;
                               color:#fff;
                               border:1px solid rgba(255,255,255,0.1);
                               padding:12px;
                               border-radius:8px;">

                    <option value="" disabled selected>
                        -- SELECT --
                    </option>

                    <?php foreach($subjects as $s): ?>

                        <option value="<?= $s['subject_id'] ?>">

                            <?= $s['subject_code'] ?>
                            -
                            <?= htmlspecialchars($s['subject_name']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- DAY + ROOM -->

            <div style="display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:10px;">

                <div>

                    <label style="font-size:0.6rem;
                                  color:#64748b;
                                  text-transform:uppercase;">

                        Day

                    </label>

                    <select name="day"
                            required
                            style="width:100%;
                                   background:#020617;
                                   color:#fff;
                                   border:1px solid rgba(255,255,255,0.1);
                                   padding:10px;
                                   border-radius:8px;">

                        <option value="Mon">Monday</option>
                        <option value="Tue">Tuesday</option>
                        <option value="Wed">Wednesday</option>
                        <option value="Thu">Thursday</option>
                        <option value="Fri">Friday</option>
                        <option value="Sat">Saturday</option>

                    </select>

                </div>

                <div>

                    <label style="font-size:0.6rem;
                                  color:#64748b;
                                  text-transform:uppercase;">

                        Room

                    </label>

                    <input type="text"
                           name="room"
                           required
                           placeholder="101-A"
                           style="width:100%;
                                  background:#020617;
                                  color:#fff;
                                  border:1px solid rgba(255,255,255,0.1);
                                  padding:10px;
                                  border-radius:8px;">

                </div>

            </div>

            <!-- TYPE -->

            <div>

                <label style="font-size:0.6rem;
                              color:#64748b;
                              text-transform:uppercase;">

                    Type

                </label>

                <select name="lecture_type"
                        required
                        style="width:100%;
                               background:#020617;
                               color:#fff;
                               border:1px solid rgba(255,255,255,0.1);
                               padding:10px;
                               border-radius:8px;">

                    <option value="Theory">Theory</option>
                    <option value="Practical">Practical</option>
                    <option value="Major">Major</option>

                </select>

            </div>

            <!-- TIME -->

            <div style="display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:10px;">

                <input type="time"
                       name="start_time"
                       required
                       style="width:100%;
                              background:#020617;
                              color:#fff;
                              border:1px solid rgba(255,255,255,0.1);
                              padding:10px;
                              border-radius:8px;">

                <input type="time"
                       name="end_time"
                       required
                       style="width:100%;
                              background:#020617;
                              color:#fff;
                              border:1px solid rgba(255,255,255,0.1);
                              padding:10px;
                              border-radius:8px;">

            </div>

            <!-- BUTTON -->

            <button type="submit"
                    style="background:#10b981;
                           color:#000;
                           border:none;
                           padding:15px;
                           border-radius:8px;
                           font-weight:900;
                           cursor:pointer;">

                INJECT

            </button>

        </form>

    </div>

    <!-- RIGHT PANEL -->

    <div style="background: rgba(15, 23, 42, 0.4);
                border-radius: 15px;
                border: 1px solid rgba(255,255,255,0.05);
                padding: 25px;">

        <div style="display:flex;
                    justify-content:space-between;
                    align-items:center;
                    margin-bottom:25px;">

            <h3 style="color:#fff;
                       font-family:'JetBrains Mono';">

                Active
                <span style="color:#10b981;">
                    Nodes
                </span>

            </h3>

            <div style="display:flex; gap:10px;">

                <button onclick="window.print()"
                        style="background: rgba(255,255,255,0.05);
                               color:#fff;
                               border:1px solid rgba(255,255,255,0.1);
                               padding:8px 15px;
                               border-radius:6px;
                               cursor:pointer;
                               font-size:0.7rem;">

                    EXPORT PDF

                </button>

                <button onclick="purgeTimetable()"
                        style="background: rgba(248,113,113,0.1);
                               color:#f87171;
                               border:1px solid rgba(248,113,113,0.2);
                               padding:8px 15px;
                               border-radius:6px;
                               cursor:pointer;
                               font-size:0.7rem;">

                    PURGE WEEK

                </button>

            </div>

        </div>

        <!-- TABLE -->

        <div style="overflow-x:auto;">

            <table style="width:100%;
                          border-collapse:collapse;
                          font-size:0.8rem;
                          color:#e2e8f0;">

                <thead style="border-bottom:2px solid #10b981;
                              text-align:left;
                              color:#64748b;">

                    <tr>

                        <th style="padding:12px;">Day</th>
                        <th style="padding:12px;">Interval</th>
                        <th style="padding:12px;">Subject</th>
                        <th style="padding:12px;">Room</th>
                        <th style="padding:12px;">Action</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach($schedule as $row): ?>

                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">

                        <td style="padding:15px;
                                   color:#10b981;
                                   font-weight:700;">

                            <?= $row['day_of_week'] ?>

                        </td>

                        <td style="padding:15px;">

                            <?= date('H:i', strtotime($row['start_time'])) ?>
                            -
                            <?= date('H:i', strtotime($row['end_time'])) ?>

                        </td>

                        <td style="padding:15px;">

                            <?= htmlspecialchars($row['subject_name']) ?>

                        </td>

                        <td style="padding:15px;">

                            <?= htmlspecialchars($row['room_no']) ?>

                        </td>

                        <td style="padding:15px;">

                            <button onclick="deleteSlot(<?= $row['id'] ?>)"
                                    style="background:none;
                                           border:none;
                                           color:#f87171;
                                           cursor:pointer;">

                                <i class="fa-solid fa-trash"></i>

                            </button>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =========================================================
PRINT ONLY AREA
========================================================= -->

<div id="printableArea">

    <div class="pdf-header">

        <h1 style="margin:0;font-size:24pt;">

            EDULYNTRIX CORE X

        </h1>

        <h3 style="margin:5px 0;">

            Official Departmental Timetable

        </h3>

        <p style="font-size:10pt;">

            Department ID:
            <?= $s_dept_id ?>

            |

            Timestamp:
            <?= date('d-M-Y H:i') ?>

        </p>

    </div>

    <table class="pdf-table">

        <thead>

            <tr>

                <th>Day</th>
                <th>Time Slot</th>
                <th>Subject Details</th>
                <th>Type</th>
                <th>Faculty</th>
                <th>Room</th>

            </tr>

        </thead>

        <tbody>

            <?php foreach($schedule as $row): ?>

            <tr>

                <td><?= $row['day_of_week'] ?></td>

                <td>

                    <?= date('H:i', strtotime($row['start_time'])) ?>
                    -
                    <?= date('H:i', strtotime($row['end_time'])) ?>

                </td>

                <td>

                    <?= htmlspecialchars($row['subject_name']) ?>
                    (
                    <?= htmlspecialchars($row['subject_code']) ?>
                    )

                </td>

                <td>

                    <?= htmlspecialchars($row['lecture_type']) ?>

                </td>

                <td>

                    <?= htmlspecialchars($row['full_name'] ?? 'Unassigned') ?>

                </td>

                <td>

                    <?= htmlspecialchars($row['room_no']) ?>

                </td>

            </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>

<script>

/* -----------------------------------------------------------
FORM SUBMIT
----------------------------------------------------------- */

document
.getElementById('timetableForm')

.addEventListener('submit', function(e){

    e.preventDefault();

    fetch(
        '/edulyntrixcorex/corex_root/api/save_timetable.php',
        {
            method:'POST',
            body:new FormData(this)
        }
    )

    .then(r => r.json())

    .then(data => {

        if(data.status === 'success'){

            loadModule(
                'timetable',
                document.getElementById(
                    'nav-timetable'
                )
            );

        } else {

            alert(
                "SCHEDULING_CONFLICT: "
                + data.message
            );
        }
    });
});

/* -----------------------------------------------------------
DELETE SLOT
----------------------------------------------------------- */

function deleteSlot(id){

    if(!confirm(
        "DELETE NODE #" + id + "?"
    )) return;

    fetch(
        '/edulyntrixcorex/corex_root/api/delete_timetable.php',
        {
            method:'POST',

            headers:{
                'Content-Type':
                'application/x-www-form-urlencoded'
            },

            body:'id=' + id
        }
    )

    .then(() =>

        loadModule(
            'timetable',
            document.getElementById(
                'nav-timetable'
            )
        )
    );
}

/* -----------------------------------------------------------
PURGE
----------------------------------------------------------- */

function purgeTimetable(){

    if(!confirm(
        "DANGER: WIPE FULL TIMETABLE?"
    )) return;

    fetch(
        '/edulyntrixcorex/corex_root/api/purge_timetable.php',
        {
            method:'POST'
        }
    )

    .then(() =>

        loadModule(
            'timetable',
            document.getElementById(
                'nav-timetable'
            )
        )
    );
}

</script>

<?php // END FILE ?>