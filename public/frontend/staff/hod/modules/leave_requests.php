<?php
/**
 * EDULYNTRIX CORE X - HOD LEAVE MANAGEMENT
 * Version 10.1.1: Event Context & API Path Calibration
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/** 1. DATABASE CONNECTIVITY **/
// Adjusted path to accurately find db_connect.php based on your ROOT STRUCTURE
$db_path = __DIR__ . '/../../../../../includes/db_connect.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    echo "<div style='color:#f87171; padding:20px; font-family:monospace; background:rgba(0,0,0,0.5); border-radius:10px;'>
            [CRITICAL ERROR] Cannot find db_connect.php at: $db_path
          </div>";
    exit;
}

// Ensure we have a department identifier to filter results
$dept_id = $_SESSION['dept_id'] ?? '';
$dept_name = $_SESSION['dept_name'] ?? 'Department';

try {
    /** 2. THE SUPREME JOIN **/
    // Optimized to use dept_id for faster indexing and precise HOD filtering
    $stmt = $pdo->prepare("SELECT 
                                lr.*, 
                                s.full_name as student_name
                           FROM leave_requests lr
                           JOIN students s ON lr.student_id = s.student_id
                           WHERE lr.dept_id = ? 
                           AND lr.status = 'Pending'
                           ORDER BY lr.applied_on ASC");
    $stmt->execute([$dept_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Leave Fetch Error: " . $e->getMessage());
    $requests = [];
}
?>

<div class="fade-in-up" style="padding: 30px; position: relative; z-index: 1;">
    <div class="module-header" style="margin-bottom: 35px;">
        <h2 style="color: #fff; font-weight: 800; font-size: 1.8rem; margin: 0;">Leave <span style="color: #10b981;">Authority</span></h2>
        <p style="color: #94a3b8; font-size: 0.8rem;">Reviewing official documentation for <b><?= htmlspecialchars($dept_name) ?></b></p>
    </div>

    <div class="leave-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
        <?php if (empty($requests)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px; color: #64748b; background: rgba(15, 23, 42, 0.4); border-radius: 20px; border: 1px dashed rgba(255,255,255,0.1);">
                <i class="fa-solid fa-calendar-check" style="font-size: 3rem; opacity: 0.2; margin-bottom: 15px;"></i>
                <p>No pending leave applications in the registry for <?= htmlspecialchars($dept_name) ?>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($requests as $req): ?>
                <div id="leave-card-<?= $req['request_id'] ?>" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 20px; padding: 25px; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; z-index: 2;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <div>
                            <div style="color: #fff; font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($req['student_name'] ?? 'Unknown Student') ?></div>
                            <div style="color: #64748b; font-size: 0.7rem; font-family: 'JetBrains Mono';"><?= htmlspecialchars($req['student_id']) ?></div>
                        </div>
                        <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase;">
                            <?= htmlspecialchars($req['leave_type']) ?>
                        </div>
                    </div>

                    <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                        <div style="display: flex; gap: 20px; margin-bottom: 10px;">
                            <div>
                                <label style="display: block; color: #64748b; font-size: 0.6rem; text-transform: uppercase;">Duration</label>
                                <span style="color: #cbd5e1; font-size: 0.8rem; font-weight: 600;"><?= date('M d', strtotime($req['start_date'])) ?> - <?= date('M d', strtotime($req['end_date'])) ?></span>
                            </div>
                            <div>
                                <label style="display: block; color: #64748b; font-size: 0.6rem; text-transform: uppercase;">Applied</label>
                                <span style="color: #94a3b8; font-size: 0.8rem; font-weight: 600;"><?= date('M d', strtotime($req['applied_on'])) ?></span>
                            </div>
                        </div>
                        <p style="color: #94a3b8; font-size: 0.75rem; line-height: 1.5; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px;">
                            <i class="fa-solid fa-quote-left" style="font-size: 0.5rem; vertical-align: top; margin-right: 5px; color: #10b981;"></i>
                            <?= htmlspecialchars($req['reason']) ?>
                        </p>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button onclick="updateLeave(event, '<?= $req['request_id'] ?>', 'Approved')" style="flex: 2; background: #10b981; color: #020617; border: none; padding: 12px; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.7rem; text-transform: uppercase; transition: 0.3s; position: relative; z-index: 10;">Approve & Sync</button>
                        <button onclick="updateLeave(event, '<?= $req['request_id'] ?>', 'Rejected')" style="flex: 1; background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.2); padding: 12px; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.7rem; text-transform: uppercase; transition: 0.3s; position: relative; z-index: 10;">Reject</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
/**
 * EDULYNTRIX CORE X - LEAVE AUTHORITY PROCESSOR
 * NO PAGE REFRESH VERSION
 */

window.updateLeave = function(e, id, action) {

    if(e){
        e.preventDefault();
        e.stopPropagation();
    }

    const card =
        document.getElementById(
            `leave-card-${id}`
        );

    const apiPath =
        '/edulyntrixcorex/corex_root/api/process_leave.php';

    const btn =
        e ? e.currentTarget : null;

    if(btn){

        btn.disabled = true;

        btn.style.opacity = '0.6';

        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Syncing...';
    }

    fetch(apiPath, {

        method: 'POST',

        headers: {
            'Content-Type':
            'application/json'
        },

        body: JSON.stringify({

            id: id,

            status: action
        })

    })

    .then(response => response.json())

    .then(data => {

        if(data.success){

            card.style.transition =
                'all .45s ease';

            card.style.opacity = '0';

            card.style.transform =
                'translateY(40px) scale(.9)';

            setTimeout(() => {

                card.remove();

                const cards =
                    document.querySelectorAll(
                        '[id^="leave-card-"]'
                    );

                if(cards.length === 0){

                    const grid =
                        document.querySelector(
                            '.leave-grid'
                        );

                    grid.innerHTML = `

                        <div
                            style="
                                grid-column:1/-1;
                                text-align:center;
                                padding:100px;
                                color:#64748b;
                                background:rgba(15,23,42,.4);
                                border-radius:20px;
                                border:1px dashed rgba(255,255,255,.1);
                            "
                        >

                            <i
                                class="fa-solid fa-calendar-check"
                                style="
                                    font-size:3rem;
                                    opacity:.2;
                                    margin-bottom:15px;
                                "
                            ></i>

                            <p>

                                No pending leave applications
                                in the registry.

                            </p>

                        </div>

                    `;
                }

            }, 450);

        } else {

            alert(
                'Registry Error: ' +
                data.message
            );

            if(btn){

                btn.disabled = false;

                btn.style.opacity = '1';

                btn.innerHTML =
                    action === 'Approved'
                    ? 'Approve & Sync'
                    : 'Reject';
            }
        }

    })

    .catch(error => {

        console.error(error);

        alert(
            'CRITICAL ERROR: Governance Node link failed.'
        );

        if(btn){

            btn.disabled = false;

            btn.style.opacity = '1';

            btn.innerHTML =
                action === 'Approved'
                ? 'Approve & Sync'
                : 'Reject';
        }

    });

    return false;
};
</script>