<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X - ISSUE FINE MODULE
 * FINAL STABLE VERSION
 * HTML + CSS + JS + PHP
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
DATABASE CONNECTION
============================================================ */

$db_path =
    $_SERVER['DOCUMENT_ROOT']
    . '/EdulyntrixCoreX/includes/db_connect.php';

if (!file_exists($db_path)) {

    $db_path =
        'C:/xampp/htdocs/EdulyntrixCoreX/includes/db_connect.php';
}

if (!file_exists($db_path)) {

    die("DB_CONNECTION_FAILURE");
}

require_once $db_path;

/* ============================================================
SESSION VALIDATION
============================================================ */

$session_val =

    $_SESSION['staff_id']

    ?? $_SESSION['login_id']

    ?? $_SESSION['user_id']

    ?? '';

if (empty($session_val)) {

    die("SESSION_NOT_FOUND");
}

/* ============================================================
FACULTY LOOKUP
============================================================ */

try {

    $stmt = $pdo->prepare("

        SELECT
            id,
            staff_id,
            dept_id,
            department,
            role

        FROM staff

        WHERE

            id = ?
            OR staff_id = ?
            OR login_id = ?

        LIMIT 1

    ");

    $stmt->execute([

        $session_val,
        $session_val,
        $session_val

    ]);

    $faculty =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$faculty) {

        throw new Exception(
            'IDENTITY_SYNC_FAILURE'
        );
    }

    $staff_id =
        $faculty['staff_id'];

    $dept_id =
        $faculty['dept_id'];

    $role =
        strtolower(
            trim($faculty['role'])
        );

    /*
    =========================================================
    FETCH ACTIVE STUDENTS
    =========================================================
    */

    $st_stmt = $pdo->prepare("

        SELECT
            student_id,
            full_name

        FROM students

        WHERE
            dept_id = ?
            AND status = 'Active'

        ORDER BY full_name ASC

    ");

    $st_stmt->execute([
        $dept_id
    ]);

    $students =
        $st_stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    =========================================================
    FETCH HISTORY
    =========================================================
    */

    if ($role === 'hod') {

        $history_stmt = $pdo->prepare("

            SELECT

                df.total_amount,
                df.description,
                df.fine_type,
                df.student_id,
                df.created_at,

                s.full_name

            FROM disciplinary_fines df

            JOIN students s
            ON s.student_id = df.student_id

            WHERE
                s.dept_id = ?

            ORDER BY df.created_at DESC

        ");

        $history_stmt->execute([
            $dept_id
        ]);

    } else {

        $history_stmt = $pdo->prepare("

            SELECT

                df.total_amount,
                df.description,
                df.fine_type,
                df.student_id,
                df.created_at,

                s.full_name

            FROM disciplinary_fines df

            JOIN students s
            ON s.student_id = df.student_id

            WHERE
                df.staff_id = ?

            ORDER BY df.created_at DESC

        ");

        $history_stmt->execute([
            $staff_id
        ]);
    }

    $history =
        $history_stmt->fetchAll(PDO::FETCH_ASSOC);

}
catch(Exception $e){

    die("

        <div style='
            padding:25px;
            color:#ef4444;
            background:#fff1f2;
            border-radius:16px;
            border:1px solid #fecaca;
            font-weight:700;
        '>

            SYSTEM FAILURE:

            " . htmlspecialchars($e->getMessage()) . "

        </div>

    ");
}
?>

<style>

#fineModuleContainer{
    animation:slideUp .4s ease;
}

@keyframes slideUp{

    from{
        opacity:0;
        transform:translateY(10px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

.fine-card{

    background:#fff;

    padding:30px;

    border-radius:24px;

    border:1px solid #e2e8f0;

    margin-bottom:30px;
}

.input-grid{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:20px;
}

.input-group{
    margin-bottom:20px;
}

.label-text{

    display:block;

    font-size:.65rem;

    font-weight:800;

    color:#4f46e5;

    text-transform:uppercase;

    letter-spacing:1px;

    margin-bottom:8px;
}

.core-input{

    width:100%;

    padding:14px 16px;

    border-radius:14px;

    border:1px solid #e2e8f0;

    background:#f8fafc;

    outline:none;

    font-size:.92rem;
}

.core-input:focus{

    border-color:#4f46e5;

    background:#fff;

    box-shadow:0 0 0 4px #eef2ff;
}

.submit-btn{

    width:100%;

    padding:16px;

    border:none;

    border-radius:14px;

    background:#4f46e5;

    color:#fff;

    font-weight:800;

    cursor:pointer;

    transition:.3s;
}

.submit-btn:hover{

    background:#4338ca;

    transform:translateY(-2px);
}

.history-wrap{

    border-radius:24px;

    overflow:hidden;

    border:1px solid #e2e8f0;

    background:#fff;
}

.history-table{

    width:100%;

    border-collapse:collapse;
}

.history-table th{

    background:#f8fafc;

    padding:18px;

    text-align:left;

    font-size:.68rem;

    text-transform:uppercase;

    color:#64748b;

    border-bottom:1px solid #e2e8f0;
}

.history-table td{

    padding:18px;

    border-bottom:1px solid #f1f5f9;
}

.badge-amt{

    padding:6px 10px;

    border-radius:8px;

    background:#eef2ff;

    color:#4f46e5;

    font-weight:800;
}

.fine-type{

    padding:6px 10px;

    border-radius:8px;

    background:#f8fafc;

    border:1px solid #e2e8f0;

    font-size:.72rem;

    font-weight:700;
}

</style>

<div id="fineModuleContainer">

    <div style="margin-bottom:25px;">

        <h2 style="
            font-size:1.7rem;
            font-weight:800;
            color:#1e1b4b;
        ">

            Disciplinary

            <span style="color:#4f46e5;">
                Ledger
            </span>

        </h2>

        <p style="
            color:#64748b;
            margin-top:5px;
        ">

            AUTH_NODE:

            <b>
                <?= htmlspecialchars($staff_id) ?>
            </b>

        </p>

    </div>

    <!-- FORM -->

    <div class="fine-card">

        <!-- CRITICAL FIX -->
        <form
            id="fineIssuanceForm"
            onsubmit="submitFineLedger(event)"
        >

            <div class="input-grid">

                <div class="input-group">

                    <label class="label-text">

                        Student

                    </label>

                    <select
                        name="student_id"
                        class="core-input"
                        required
                    >

                        <option value="">
                            Select Student...
                        </option>

                        <?php foreach($students as $s): ?>

                        <option
                            value="<?= $s['student_id'] ?>"
                        >

                            <?= htmlspecialchars($s['full_name']) ?>

                        </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="input-group">

                    <label class="label-text">

                        Amount

                    </label>

                    <input
                        type="number"
                        name="amount"
                        class="core-input"
                        required
                        min="1"
                    >

                </div>

            </div>

            <div class="input-group">

                <label class="label-text">

                    Category

                </label>

                <select
                    name="fine_type"
                    class="core-input"
                    required
                >

                    <option value="discipline">
                        Discipline
                    </option>

                    <option value="attendance">
                        Attendance
                    </option>

                    <option value="damage">
                        Damage
                    </option>

                    <option value="library">
                        Library
                    </option>

                    <option value="misconduct">
                        Misconduct
                    </option>

                </select>

            </div>

            <div class="input-group">

                <label class="label-text">

                    Description

                </label>

                <textarea
                    name="reason"
                    rows="4"
                    class="core-input"
                    required
                    style="resize:none;"
                ></textarea>

            </div>

            <button
                type="submit"
                id="fineSubmitBtn"
                class="submit-btn"
            >

                <i class="fa-solid fa-receipt"></i>

                COMMIT TO SYSTEM LEDGER

            </button>

        </form>

    </div>

    <!-- HISTORY -->

    <div class="history-wrap">

        <table class="history-table">

            <thead>

                <tr>

                    <th>
                        Student
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Category
                    </th>

                    <th>
                        Description
                    </th>

                    <th>
                        Timestamp
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php foreach($history as $row): ?>

            <tr>

                <td>

                    <b>
                        <?= htmlspecialchars($row['full_name']) ?>
                    </b>

                </td>

                <td>

                    <span class="badge-amt">

                        ₹<?= number_format($row['total_amount'],2) ?>

                    </span>

                </td>

                <td>

                    <span class="fine-type">

                        <?= strtoupper(
                            htmlspecialchars(
                                $row['fine_type']
                                ?? 'general'
                            )
                        ) ?>

                    </span>

                </td>

                <td>

                    <?= htmlspecialchars($row['description']) ?>

                </td>

                <td>

                    <?= date(
                        'M d, Y H:i',
                        strtotime($row['created_at'])
                    ) ?>

                </td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<script>

/* ============================================================
FIXED AJAX SUBMITTER
============================================================ */

async function submitFineLedger(e){

    e.preventDefault();

    e.stopPropagation();

    const form =
        document.getElementById(
            'fineIssuanceForm'
        );

    const btn =
        document.getElementById(
            'fineSubmitBtn'
        );

    btn.disabled = true;

    btn.innerHTML = `

        <i class="fa-solid fa-spinner fa-spin"></i>

        SYNCING LEDGER...

    `;

    try {

        const formData =
            new FormData(form);

        const response =
            await fetch(

                '/EdulyntrixCoreX/public/frontend/staff/modules/process_fine.php',

                {
                    method:'POST',
                    body:formData
                }
            );

        if(!response.ok){

            throw new Error(
                'SERVER_FAILURE'
            );
        }

        const result =
            await response.json();

        if(result.success){

            alert(
                'SUCCESS: Fine recorded.'
            );

            /*
            =================================================
            MODULE RELOAD ONLY
            =================================================
            */

            if(
                typeof loadModule === 'function'
            ){

                loadModule(
                    'issue_fine',
                    document.getElementById(
                        'nav-issue_fine'
                    )
                );

            } else {

                location.reload();
            }

        } else {

            alert(
                result.message
                || 'PROCESS_FAILURE'
            );

            btn.disabled = false;

            btn.innerHTML = `

                COMMIT TO SYSTEM LEDGER

            `;
        }

    }
    catch(error){

        console.error(error);

        alert(
            'NETWORK_FAILURE'
        );

        btn.disabled = false;

        btn.innerHTML = `

            COMMIT TO SYSTEM LEDGER

        `;
    }

    return false;
}

</script>