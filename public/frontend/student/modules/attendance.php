<?php

session_start();

require_once '../../../../includes/db_connect.php';

/**
 * ============================================================
 * EDULYNTRIX COREX - ATTENDANCE OVERSIGHT
 * FULL FIXED VERSION
 * ============================================================
 */

/* ============================================================
STUDENT SESSION
============================================================ */

$student_id =
    $_SESSION['student_id']
    ?? '2026CSE001';

/* ============================================================
YEAR FILTER
============================================================ */

$selected_year =
    isset($_GET['year'])
    ? (int)$_GET['year']
    : 1;

try {

    /* ========================================================
    ATTENDANCE QUERY
    CRITICAL FIX:
    USE LOWERCASE STATUS VALUES
    ======================================================== */

    $query = "

        SELECT

            sub.subject_name,
            sub.subject_code,

            s.full_name,

            COUNT(a.attendance_id) AS total_classes,

            SUM(
                CASE
                    WHEN LOWER(a.status) = 'present'
                    THEN 1
                    ELSE 0
                END
            ) AS attended_classes,

            SUM(
                CASE
                    WHEN LOWER(a.status) = 'absent'
                    THEN 1
                    ELSE 0
                END
            ) AS missed_classes,

            SUM(
                CASE
                    WHEN LOWER(a.status) = 'late'
                    THEN 1
                    ELSE 0
                END
            ) AS late_entries,

            SUM(
                CASE
                    WHEN LOWER(a.status) = 'leave'
                    THEN 1
                    ELSE 0
                END
            ) AS leave_entries

        FROM attendance a

        INNER JOIN students s
            ON a.student_id = s.student_id

        INNER JOIN subjects sub
            ON a.subject_id = sub.subject_id

        WHERE

            s.student_id = ?

            AND a.academic_year = ?

        GROUP BY

            sub.subject_id,
            sub.subject_name,
            sub.subject_code,
            s.full_name

        ORDER BY
            sub.subject_code ASC

    ";

    $stmt = $pdo->prepare($query);

    $stmt->execute([
        $student_id,
        $selected_year
    ]);

    $records =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ========================================================
    STUDENT META
    ======================================================== */

    $meta_stmt = $pdo->prepare("

        SELECT

            full_name,
            dept_id,
            current_semester

        FROM students

        WHERE student_id = ?

        LIMIT 1

    ");

    $meta_stmt->execute([
        $student_id
    ]);

    $student_meta =
        $meta_stmt->fetch(PDO::FETCH_ASSOC);

    /* ========================================================
    GLOBAL CALCULATIONS
    ======================================================== */

    $total_all = 0;

    $attended_all = 0;

    $missed_all = 0;

    $late_all = 0;

    $leave_all = 0;

    foreach ($records as $r) {

        $total_all +=
            (int)$r['total_classes'];

        $attended_all +=
            (int)$r['attended_classes'];

        $missed_all +=
            (int)$r['missed_classes'];

        $late_all +=
            (int)$r['late_entries'];

        $leave_all +=
            (int)$r['leave_entries'];
    }

    /*
    =========================================================
    PERCENTAGE
    =========================================================
    */

    $global_percent =
        ($total_all > 0)
        ? round(
            ($attended_all / $total_all) * 100
        )
        : 0;

}

catch (PDOException $e) {

    die("

        <div class='pill-low'
             style='
                padding:20px;
                border-radius:15px;
                background:#fff1f2;
                border:1px solid #fecdd3;
                color:#dc2626;
                font-weight:700;
             '>

            BACKEND_SYNC_ERROR

            <br><br>

            " . htmlspecialchars($e->getMessage()) . "

        </div>

    ");
}

?>

<style>

/* ============================================================
ANIMATIONS
============================================================ */

.attendance-container {

    animation:
        nexusScaleUp 0.6s
        cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes nexusScaleUp {

    from {

        opacity: 0;

        transform:
            scale(0.97)
            translateY(10px);
    }

    to {

        opacity: 1;

        transform:
            scale(1)
            translateY(0);
    }
}

@keyframes nodePulse {

    0%,100% {
        opacity:1;
    }

    50% {
        opacity:0.5;
    }
}

.node-pulse {

    animation:
        nodePulse 2s infinite ease-in-out;
}

/* ============================================================
TRIGGER
============================================================ */

.live-node-trigger {

    display:inline-flex;

    align-items:center;

    gap:6px;

    background:var(--nexus-gray);

    border:1px solid var(--nexus-border);

    padding:4px 12px;

    border-radius:50px;

    cursor:pointer;

    transition:all 0.3s ease;
}

.live-node-trigger:hover {

    background:#fff;

    border-color:var(--nexus-blue);

    box-shadow:
        0 4px 12px
        var(--nexus-blue-glow);
}

.status-dot {

    width:7px;

    height:7px;

    background:var(--nexus-success);

    border-radius:50%;

    box-shadow:
        0 0 6px
        var(--nexus-success);
}

/* ============================================================
LAYOUT
============================================================ */

.section-tag {

    font-size:0.65rem;

    font-weight:800;

    color:var(--nexus-text-muted);

    text-transform:uppercase;

    letter-spacing:0.5px;
}

.insights-ribbon {

    display:grid;

    grid-template-columns:
        repeat(5, 1fr);

    gap:20px;

    margin-bottom:35px;
}

.insight-card {

    background:var(--nexus-gray);

    padding:20px;

    border-radius:20px;

    border:1px solid var(--nexus-border);

    transition:all 0.3s ease;
}

/* ============================================================
TABLE
============================================================ */

.attendance-table {

    width:100%;

    border-collapse:separate;

    border-spacing:0 12px;
}

.attendance-table th {

    padding:0 20px 10px 20px;

    font-size:0.65rem;

    text-transform:uppercase;

    color:var(--nexus-text-muted);

    letter-spacing:1.2px;

    font-weight:800;

    text-align:left;
}

.attendance-table td {

    padding:18px 20px;

    background:var(--nexus-gray);

    border-top:1px solid var(--nexus-border);

    border-bottom:1px solid var(--nexus-border);
}

.attendance-table td:first-child {

    border-left:1px solid var(--nexus-border);

    border-radius:16px 0 0 16px;
}

.attendance-table td:last-child {

    border-right:1px solid var(--nexus-border);

    border-radius:0 16px 16px 0;
}

/* ============================================================
STATUS
============================================================ */

.status-pill {

    padding:6px 14px;

    border-radius:50px;

    font-size:0.65rem;

    font-weight:800;

    text-transform:uppercase;

    display:inline-block;
}

.pill-on-track {

    background:
        rgba(16,185,129,0.08);

    color:
        var(--nexus-success);
}

.pill-low {

    background:
        rgba(239,68,68,0.08);

    color:
        var(--nexus-danger);
}

</style>

<div class="attendance-container">

    <!-- =====================================================
    HEADER
    ====================================================== -->

    <div class="module-header"
         style="
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            margin-bottom:30px;
         ">

        <div>

            <small class="section-tag">

                Profile:

                <?= htmlspecialchars(
                    $student_meta['full_name']
                    ?? 'Guest Node'
                ) ?>

            </small>

            <h3 class="nexus-title"
                style="
                    color:var(--nexus-blue);
                    font-weight:800;
                    font-size:1.6rem;
                    letter-spacing:-1px;
                    margin-top:5px;
                ">

                Attendance Oversight

            </h3>

            <div class="live-node-trigger"
                 onclick="
                    loadModule(
                        'attendance',
                        this,
                        <?= $selected_year ?>
                    )
                 ">

                <div class="status-dot node-pulse"></div>

                <span style="
                    font-size:0.6rem;
                    font-weight:800;
                    color:var(--nexus-text-main);
                ">

                    REFRESH

                </span>

            </div>

        </div>

        <div style="text-align:right;">

            <small class="section-tag">

                Year 0<?= $selected_year ?> Score

            </small>

            <div style="
                font-size:2.2rem;
                font-weight:900;
                color:
                <?= $global_percent >= 75
                    ? 'var(--nexus-success)'
                    : 'var(--nexus-danger)' ?>
            ;">

                <?= $global_percent ?>%

            </div>

        </div>

    </div>

    <!-- =====================================================
    INSIGHTS
    ====================================================== -->

    <div class="insights-ribbon">

        <div class="insight-card">

            <small class="section-tag">

                Total Sessions

            </small>

            <div style="
                font-size:1.5rem;
                font-weight:800;
            ">

                <?= $total_all ?>

            </div>

        </div>

        <div class="insight-card">

            <small class="section-tag">

                Present

            </small>

            <div style="
                font-size:1.5rem;
                font-weight:800;
                color:var(--nexus-success);
            ">

                <?= $attended_all ?>

            </div>

        </div>

        <div class="insight-card">

            <small class="section-tag">

                Late

            </small>

            <div style="
                font-size:1.5rem;
                font-weight:800;
                color:#f59e0b;
            ">

                <?= $late_all ?>

            </div>

        </div>

        <div class="insight-card">

            <small class="section-tag">

                Leave

            </small>

            <div style="
                font-size:1.5rem;
                font-weight:800;
                color:#3b82f6;
            ">

                <?= $leave_all ?>

            </div>

        </div>

        <div class="insight-card">

            <small class="section-tag">

                Absent

            </small>

            <div style="
                font-size:1.5rem;
                font-weight:800;
                color:var(--nexus-danger);
            ">

                <?= $missed_all ?>

            </div>

        </div>

    </div>

    <!-- =====================================================
    TABLE
    ====================================================== -->

    <table class="attendance-table">

        <thead>

            <tr>

                <th>Subject Entity</th>

                <th>Sessions</th>

                <th>Present</th>

                <th>Late</th>

                <th>Leave</th>

                <th>Absent</th>

                <th>Standing</th>

            </tr>

        </thead>

        <tbody>

        <?php if (empty($records)): ?>

            <tr>

                <td colspan="7"
                    style="
                        text-align:center;
                        padding:50px;
                        color:var(--nexus-text-muted);
                    ">

                    No attendance data found.

                </td>

            </tr>

        <?php else: ?>

            <?php foreach ($records as $row):

                $percent =
                    ($row['total_classes'] > 0)
                    ? round(
                        (
                            $row['attended_classes']
                            /
                            $row['total_classes']
                        ) * 100
                    )
                    : 0;

            ?>

            <tr>

                <td style="
                    font-weight:700;
                    color:var(--nexus-text-main);
                ">

                    <?= htmlspecialchars(
                        $row['subject_name']
                    ) ?>

                    <span style="
                        font-size:0.75em;
                        color:#999;
                    ">

                        (
                        <?= htmlspecialchars(
                            $row['subject_code']
                        ) ?>
                        )

                    </span>

                </td>

                <td>

                    <?= $row['total_classes'] ?>

                </td>

                <td style="
                    color:var(--nexus-success);
                    font-weight:700;
                ">

                    <?= $row['attended_classes'] ?>

                </td>

                <td style="
                    color:#f59e0b;
                    font-weight:700;
                ">

                    <?= $row['late_entries'] ?>

                </td>

                <td style="
                    color:#3b82f6;
                    font-weight:700;
                ">

                    <?= $row['leave_entries'] ?>

                </td>

                <td style="
                    color:var(--nexus-danger);
                    font-weight:700;
                ">

                    <?= $row['missed_classes'] ?>

                </td>

                <td>

                    <span class="
                        status-pill
                        <?= $percent >= 75
                            ? 'pill-on-track'
                            : 'pill-low' ?>
                    ">

                        <?= $percent >= 75
                            ? 'On Track'
                            : 'Low' ?>

                        (<?= $percent ?>%)

                    </span>

                </td>

            </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>