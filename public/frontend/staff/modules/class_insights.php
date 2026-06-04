<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * FACULTY CLASS INSIGHTS MODULE
 * FINAL FIXED VERSION
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

require_once $db_path;

/* ============================================================
SESSION
============================================================ */

$dept_id =
    $_SESSION['dept_id']
    ?? 1;

$faculty_id =
    $_SESSION['staff_id']
    ?? $_SESSION['login_id']
    ?? '';

/* ============================================================
FETCH SUBJECTS ASSIGNED TO FACULTY
============================================================ */

$subjects = [];

try {

    $subject_stmt = $pdo->prepare("

        SELECT
            subject_id,
            subject_name,
            subject_code

        FROM subjects

        WHERE
            assigned_faculty_id = ?
            AND dept_id = ?

        ORDER BY subject_name ASC

    ");

    $subject_stmt->execute([
        $faculty_id,
        $dept_id
    ]);

    $subjects =
        $subject_stmt->fetchAll(PDO::FETCH_ASSOC);

}
catch(Exception $e){

    $subjects = [];
}

/* ============================================================
SELECT SUBJECT
============================================================ */

$selected_subject =
    $_GET['subject_id']
    ?? ($subjects[0]['subject_id'] ?? 0);

/* ============================================================
ATTENDANCE ANALYTICS
============================================================ */

$total_count   = 0;
$present_count = 0;
$absent_count  = 0;
$late_count    = 0;
$attendance_percentage = 0;

try {

    $analytics_stmt = $pdo->prepare("

        SELECT
            status,
            COUNT(*) as total

        FROM attendance

        WHERE subject_id = ?

        GROUP BY status

    ");

    $analytics_stmt->execute([
        $selected_subject
    ]);

    $analytics =
        $analytics_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($analytics as $a){

        $status =
            strtolower(
                trim($a['status'])
            );

        $count =
            (int)$a['total'];

        $total_count += $count;

        if($status === 'present'){

            $present_count += $count;

        } elseif($status === 'absent'){

            $absent_count += $count;

        } elseif($status === 'late'){

            $late_count += $count;
        }
    }

    if($total_count > 0){

        $attendance_percentage =
            round(
                (
                    ($present_count + $late_count)
                    /
                    $total_count
                ) * 100,
                1
            );
    }

}
catch(Exception $e){

    $attendance_percentage = 0;
}

/* ============================================================
CHART DATA
============================================================ */

$chart_labels = json_encode([
    "Present",
    "Absent",
    "Late"
]);

$chart_values = json_encode([
    $present_count,
    $absent_count,
    $late_count
]);

?>

<!-- =========================================================
CHART ENGINE
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div
    class="insights-shell"
    style="
        animation:fadeIn .4s ease;
    "
>

<style>

/* ============================================================
ANIMATION
============================================================ */

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

/* ============================================================
ROOT
============================================================ */

.insights-shell{

    font-family:'Plus Jakarta Sans',sans-serif;
}

/* ============================================================
HEADER
============================================================ */

.insights-header{

    display:flex;

    justify-content:space-between;

    align-items:flex-end;

    margin-bottom:25px;
}

.insights-title{

    font-size:2rem;

    font-weight:800;

    color:#1e1b4b;
}

.insights-title span{

    color:#10b981;
}

.insights-sub{

    margin-top:8px;

    color:#64748b;

    font-size:.9rem;
}

/* ============================================================
SUBJECT SELECTOR
============================================================ */

.subject-box{

    background:#fff;

    padding:18px;

    border-radius:18px;

    border:1px solid #e2e8f0;

    margin-bottom:25px;
}

.subject-label{

    font-size:.72rem;

    font-weight:800;

    color:#64748b;

    text-transform:uppercase;

    margin-bottom:10px;
}

.subject-select{

    width:100%;

    padding:15px;

    border-radius:14px;

    border:1px solid #dbeafe;

    background:#fff;

    font-weight:700;

    outline:none;
}

/* ============================================================
STATS
============================================================ */

.stats-grid{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(250px,1fr));

    gap:20px;

    margin-bottom:30px;
}

.stat-card{

    background:#fff;

    border-radius:24px;

    padding:25px;

    border:1px solid #e2e8f0;

    position:relative;

    overflow:hidden;
}

.stat-card::before{

    content:'';

    position:absolute;

    left:0;
    top:0;

    width:5px;
    height:100%;
}

.green::before{
    background:#10b981;
}

.red::before{
    background:#ef4444;
}

.orange::before{
    background:#f59e0b;
}

.blue::before{
    background:#3b82f6;
}

.stat-label{

    font-size:.72rem;

    text-transform:uppercase;

    letter-spacing:1px;

    font-weight:800;

    color:#64748b;
}

.stat-value{

    margin-top:10px;

    font-size:2.3rem;

    font-weight:800;

    color:#0f172a;
}

/* ============================================================
CHART BOX
============================================================ */

.chart-box{

    background:#fff;

    border:1px solid #e2e8f0;

    border-radius:28px;

    padding:30px;

    margin-bottom:30px;
}

.chart-title{

    font-size:.9rem;

    font-weight:800;

    color:#475569;

    margin-bottom:25px;

    text-transform:uppercase;
}

/* ============================================================
TABLE
============================================================ */

.table-box{

    background:#fff;

    border-radius:28px;

    border:1px solid #e2e8f0;

    padding:30px;
}

.table-title{

    font-size:.9rem;

    font-weight:800;

    text-transform:uppercase;

    color:#475569;

    margin-bottom:20px;
}

.analytics-table{

    width:100%;

    border-collapse:collapse;
}

.analytics-table th{

    text-align:left;

    padding:15px;

    color:#94a3b8;

    font-size:.72rem;

    text-transform:uppercase;

    border-bottom:2px solid #f1f5f9;
}

.analytics-table td{

    padding:18px 15px;

    border-bottom:1px solid #f8fafc;

    font-weight:700;

    color:#334155;
}

/* ============================================================
BADGES
============================================================ */

.badge{

    display:inline-block;

    padding:7px 14px;

    border-radius:10px;

    font-size:.78rem;

    font-weight:800;
}

.bg-green{

    background:#ecfdf5;

    color:#059669;
}

.bg-red{

    background:#fef2f2;

    color:#dc2626;
}

.bg-orange{

    background:#fff7ed;

    color:#ea580c;
}

.bg-blue{

    background:#eff6ff;

    color:#2563eb;
}

</style>

<!-- =========================================================
HEADER
========================================================= -->

<div class="insights-header">

    <div>

        <div class="insights-title">

            Class Insights
            <span>

                & Analytics

            </span>

        </div>

        <div class="insights-sub">

            Class attendance analytics per subject.

        </div>

    </div>

</div>

<!-- =========================================================
SUBJECT SELECTOR
========================================================= -->

<div class="subject-box">

    <div class="subject-label">

        Subject Selector

    </div>

    <select
        class="subject-select"
        id="subjectSelector"
    >

        <?php foreach($subjects as $subject): ?>

        <option
            value="<?= $subject['subject_id'] ?>"
            <?= ($selected_subject == $subject['subject_id']) ? 'selected' : '' ?>
        >

            <?= htmlspecialchars(
                $subject['subject_code']
            ) ?>

            -

            <?= htmlspecialchars(
                $subject['subject_name']
            ) ?>

        </option>

        <?php endforeach; ?>

    </select>

</div>

<!-- =========================================================
STATS
========================================================= -->

<div class="stats-grid">

    <div class="stat-card green">

        <div class="stat-label">

            Total Attendance Logs

        </div>

        <div class="stat-value">

            <?= $total_count ?>

        </div>

    </div>

    <div class="stat-card blue">

        <div class="stat-label">

            Present Students

        </div>

        <div class="stat-value">

            <?= $present_count ?>

        </div>

    </div>

    <div class="stat-card red">

        <div class="stat-label">

            Absent Students

        </div>

        <div class="stat-value">

            <?= $absent_count ?>

        </div>

    </div>

    <div class="stat-card orange">

        <div class="stat-label">

            Attendance Percentage

        </div>

        <div class="stat-value">

            <?= $attendance_percentage ?>%

        </div>

    </div>

</div>

<!-- =========================================================
CHART
========================================================= -->

<div class="chart-box">

    <div class="chart-title">

        Performance Distribution (Normalized %)

    </div>

    <div style="height:350px;">

        <canvas id="attendanceChart"></canvas>

    </div>

</div>

<!-- =========================================================
LEDGER
========================================================= -->

<div class="table-box">

    <div class="table-title">

        Subject Attendance Ledger

    </div>

    <table class="analytics-table">

        <thead>

            <tr>

                <th>Status</th>
                <th>Count</th>
                <th>Analytics</th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td>

                    Present

                </td>

                <td>

                    <?= $present_count ?>

                </td>

                <td>

                    <span class="badge bg-green">

                        ACTIVE

                    </span>

                </td>

            </tr>

            <tr>

                <td>

                    Absent

                </td>

                <td>

                    <?= $absent_count ?>

                </td>

                <td>

                    <span class="badge bg-red">

                        CRITICAL

                    </span>

                </td>

            </tr>

            <tr>

                <td>

                    Late

                </td>

                <td>

                    <?= $late_count ?>

                </td>

                <td>

                    <span class="badge bg-orange">

                        DELAYED

                    </span>

                </td>

            </tr>

            <tr>

                <td>

                    Overall Attendance

                </td>

                <td>

                    <?= $attendance_percentage ?>%

                </td>

                <td>

                    <span class="badge bg-blue">

                        CONSOLIDATED

                    </span>

                </td>

            </tr>

        </tbody>

    </table>

</div>

</div>

<script>

/* ============================================================
SUBJECT SWITCH
============================================================ */

document
.getElementById(
    'subjectSelector'
)

.addEventListener(
    'change',
    function(){

        const subjectId =
            this.value;

        loadNode(
            'class_insights?subject_id='
            +
            subjectId
        );
    }
);

/* ============================================================
CHART
============================================================ */

function renderAttendanceChart(){

    const canvas =
        document.getElementById(
            'attendanceChart'
        );

    if(!canvas){
        return;
    }

    if(window.classAnalyticsChart){

        window.classAnalyticsChart.destroy();
    }

    const ctx =
        canvas.getContext('2d');

    window.classAnalyticsChart =
        new Chart(ctx, {

        type:'doughnut',

        data:{

            labels:
                <?= $chart_labels ?>,

            datasets:[{

                data:
                    <?= $chart_values ?>,

                backgroundColor:[

                    '#10b981',
                    '#ef4444',
                    '#f59e0b'
                ],

                borderWidth:0
            }]
        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            plugins:{

                legend:{
                    position:'bottom'
                }
            }
        }
    });
}

renderAttendanceChart();

</script>