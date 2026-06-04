<?php
/**
 * EDULYNTRIX CORE X - FACULTY DEPLOYMENT MODULE
 * Path: /public/frontend/staff/hod/modules/faculty_deployment.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| DATABASE PATH RESOLUTION
|--------------------------------------------------------------------------
*/

$db_path = dirname(__DIR__, 5) . '/includes/db_connect.php';

if (!file_exists($db_path)) {

    $db_path =
        $_SERVER['DOCUMENT_ROOT']
        . '/edulyntrixcorex/includes/db_connect.php';
}

if (file_exists($db_path)) {

    require_once $db_path;

} else {

    die("
        <div style='color:#f87171;
                    background:#020617;
                    padding:20px;
                    border:1px solid #f87171;
                    border-radius:10px;'>

            [CRITICAL_FAILURE]:
            DATABASE_LINK_OFFLINE

            <br><br>

            CHECK_PATH:
            {$db_path}

        </div>
    ");
}

/*
|--------------------------------------------------------------------------
| SESSION SECURITY
|--------------------------------------------------------------------------
*/

$s_dept_id = $_SESSION['dept_id'] ?? 1;

try {

    /*
    |--------------------------------------------------------------------------
    | FIXED QUERY
    |--------------------------------------------------------------------------
    |
    | REMOVED:
    | LEFT JOIN timetable
    |
    | Because timetable creates duplicate faculty rows.
    |
    | Faculty Deployment only needs:
    | faculty ↔ assigned subject
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT

            st.id AS staff_db_id,
            st.staff_id,
            st.full_name,
            st.designation,
            st.status,

            sub.subject_name,
            sub.subject_code,
            sub.subject_id

        FROM staff st

        LEFT JOIN subjects sub
            ON st.staff_id = sub.assigned_faculty_id

        WHERE st.dept_id = ?
        AND st.role = 'faculty'

        ORDER BY st.full_name ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$s_dept_id]);

    $deployments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | AVAILABLE SUBJECTS
    |--------------------------------------------------------------------------
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

    $available_subjects =
        $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("
        <div style='color:#f87171;
                    font-family:monospace;
                    padding:20px;'>

            [CORE_ERROR]

            <br><br>

            {$e->getMessage()}

        </div>
    ");
}
?>

<div style="padding: 25px;
            background: #020617;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.05);
            color: #fff;">

    <div style="display:flex;
                justify-content:space-between;
                align-items:center;
                margin-bottom:30px;">

        <h3 style="margin:0;
                   font-weight:800;
                   letter-spacing:-0.5px;">

            Faculty
            <span style="color:#10b981;">

                Deployment

            </span>

        </h3>

        <span style="font-family:'JetBrains Mono';
                     font-size:0.65rem;
                     color:#475569;">

            API_PATH:
/corex_root/api/

        </span>

    </div>

    <div style="display:grid;
                grid-template-columns:
                repeat(auto-fill, minmax(300px, 1fr));
                gap:20px;">

        <?php foreach ($deployments as $f): ?>

        <div style="background:rgba(15,23,42,0.8);
                    border:1px solid rgba(255,255,255,0.05);
                    border-radius:12px;
                    padding:20px;
                    transition:0.3s;">

            <div style="display:flex;
                        align-items:center;
                        gap:12px;
                        margin-bottom:15px;">

                <div style="width:45px;
                            height:45px;
                            background:#10b981;
                            border-radius:10px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            color:#000;
                            font-weight:900;">

                    <?= strtoupper(substr($f['full_name'], 0, 1)) ?>

                </div>

                <div>

                    <h4 style="margin:0;
                               font-size:0.95rem;">

                        <?= htmlspecialchars($f['full_name']) ?>

                    </h4>

                    <p style="color:#64748b;
                              font-size:0.65rem;
                              margin:0;">

                        <?= htmlspecialchars($f['staff_id']) ?>

                    </p>

                </div>

            </div>

            <div style="background:#000;
                        padding:12px;
                        border-radius:8px;
                        border-left:3px solid #10b981;
                        margin-bottom:15px;">

                <p style="color:#475569;
                          font-size:0.55rem;
                          text-transform:uppercase;
                          margin:0 0 5px 0;">

                    Current Assignment

                </p>

                <p style="color:#fff;
                          font-weight:700;
                          font-size:0.8rem;
                          margin:0;">

                    <?= htmlspecialchars(
                        $f['subject_name'] ?? 'OFF_DUTY'
                    ) ?>

                </p>

            </div>

            <select
                id="sub_select_<?= $f['staff_id'] ?>"
                style="width:100%;
                       background:#0f172a;
                       color:#fff;
                       border:1px solid rgba(255,255,255,0.1);
                       padding:10px;
                       border-radius:8px;
                       font-size:0.75rem;
                       margin-bottom:10px;"
            >

                <option value="">

                    -- REMOVE ASSIGNMENT --

                </option>

                <?php foreach($available_subjects as $as): ?>

                    <option
                        value="<?= $as['subject_id'] ?>"

                        <?= (
                            $f['subject_id']
                            ==
                            $as['subject_id']
                        )
                        ? 'selected'
                        : '' ?>
                    >

                        <?= htmlspecialchars(
                            $as['subject_code']
                        ) ?>

                        :

                        <?= htmlspecialchars(
                            $as['subject_name']
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <button
                onclick="commitAssignment('<?= $f['staff_id'] ?>')"

                style="width:100%;
                       background:#10b981;
                       color:#000;
                       border:none;
                       padding:10px;
                       border-radius:8px;
                       font-size:0.7rem;
                       font-weight:800;
                       cursor:pointer;
                       text-transform:uppercase;"
            >

                Commit Deployment

            </button>

        </div>

        <?php endforeach; ?>

    </div>

</div>

<script>

function commitAssignment(facultyId) {

    const subjectId =
        document.getElementById(
            'sub_select_' + facultyId
        ).value;

    const formData = new FormData();

    formData.append(
        'faculty_id',
        facultyId
    );

    formData.append(
        'subject_id',
        subjectId
    );

    fetch(
        '/edulyntrixcorex/corex_root/api/process_assignment.php',
        {
            method: 'POST',
            body: formData
        }
    )

    .then(response => response.json())

    .then(data => {

        if (data.status === 'success') {

            alert("✓ DEPLOYMENT UPDATED");

            const navBtn =
                document.getElementById(
                    'nav-faculty_deployment'
                );

            if (
                typeof loadModule === "function"
            ) {

                loadModule(
                    'faculty_deployment',
                    navBtn
                );

            } else {

                location.reload();
            }

        } else {

            alert(
                "❌ ERROR: "
                + data.message
            );
        }
    })

    .catch(error => {

        console.error(error);

        alert(
            "CRITICAL: API_NODE_UNREACHABLE"
        );
    });
}

</script>