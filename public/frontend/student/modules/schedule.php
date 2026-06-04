<?php
/**
 * EDULYNTRIX CORE X - STUDENT LECTURE TRACKER
 * FIX: Database Sync Hardening & Null Value Handling
 */
session_start();
require_once '../../../../includes/db_connect.php';

// 1. AUTH GUARD
if (!isset($_SESSION['student_id'])) {
    die("<div class='glass-effect' style='padding:20px; color: #ef4444;'>[AUTH_ERROR]: Session Expired.</div>");
}

$sid = $_SESSION['student_id'];

/** 2. CAPTURE INPUT & MAP DAYS **/
$today_abbr    = date('D');
$selected_day  = $_GET['day'] ?? $today_abbr; 

$day_full_names = [
    'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 
    'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday'
];
$display_day = $day_full_names[$selected_day] ?? 'Academic Day';

try {
    // 3. FETCH STUDENT CONTEXT
    $user_stmt = $pdo->prepare("SELECT dept_id, academic_year, semester FROM students WHERE student_id = ?");
    $user_stmt->execute([$sid]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) { throw new Exception("STUDENT_RECORD_NOT_FOUND"); }

    $dept_id = $user['dept_id'];
    $year    = $user['academic_year'];
    $sem     = $user['semester'];

    // 4. FETCH SCHEDULE NODES (Fixed with LEFT JOIN and NULL handling)
    // COALESCE ensures we never show a 'NULL' string in the UI
    $query = "SELECT t.*, 
              IFNULL(s.subject_name, 'Unmapped Subject') as subject_name, 
              IFNULL(s.subject_code, 'N/A') as subject_code,
              COALESCE(t.lecture_type, 'General') as display_type
              FROM timetable t
              LEFT JOIN subjects s ON t.subject_id = s.subject_id
              WHERE (t.dept_id = ? OR t.dept_id IS NULL) 
              AND t.day_of_week = ? 
              AND (s.academic_year = ? OR s.academic_year IS NULL) 
              AND (s.semester = ? OR s.semester IS NULL)
              ORDER BY t.start_time ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$dept_id, $selected_day, $year, $sem]);
    $schedule_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("COREX_SYNC_CRITICAL: " . $e->getMessage());
    die("<div class='glass-effect' style='padding:20px; color:#f87171;'>DATABASE_SYNC_FAILURE: Contact System Architect.</div>");
}
?>

<div class="schedule-wrapper module-entrance">
    <div class="schedule-header">
        <div class="header-left">
            <h2 class="module-title">Lecture <b class="glow-text">Schedule</b></h2>
            <p class="module-subtitle">Academic track for <b><?= $display_day ?></b></p>
        </div>
        
        <div class="week-selector glass-effect">
            <?php foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $d): ?>
                <button 
                    class="day-pill <?= ($d == $selected_day) ? 'active-day' : '' ?>" 
                    onclick="switchScheduleDay('<?= $d ?>')">
                    <?= $d ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="timeline-container" id="timelineList">
        <?php if(empty($schedule_items)): ?>
            <div class="empty-state glass-effect">
                <i class="fa-solid fa-calendar-xmark" style="font-size: 2.5rem; color: var(--nexus-blue); margin-bottom: 15px; opacity: 0.4;"></i>
                <p>No academic nodes registered for <b><?= $display_day ?></b>.</p>
                <small style="opacity:0.5;">Node Status: Offline</small>
            </div>
        <?php else: ?>
            <?php foreach($schedule_items as $item): 
                $now_time = date('H:i:s');
                $is_actually_today = ($selected_day == $today_abbr);
                $is_live = ($is_actually_today && $now_time >= $item['start_time'] && $now_time <= $item['end_time']);
                $is_past = ($is_actually_today && $now_time > $item['end_time']);
            ?>
                <div class="schedule-card glass-effect <?= $is_live ? 'live-highlight' : '' ?>">
                    <div class="time-block">
                        <span class="slot-time">
                            <?= date('h:i A', strtotime($item['start_time'])) ?>
                        </span>
                        <div class="status-bar <?= $is_live ? 'live' : ($is_past ? 'done' : 'upcoming') ?>"></div>
                    </div>
                    
                    <div class="subject-info">
                        <h4 class="subject-title">
                            <?= htmlspecialchars($item['subject_name']) ?>
                            <span class="type-pill"><?= htmlspecialchars($item['display_type']) ?></span>
                            <?php if($is_live): ?><span class="live-tag">LIVE</span><?php endif; ?>
                        </h4>
                        <div class="meta-row">
                            <span><i class="fa-solid fa-door-open"></i> Room <?= htmlspecialchars($item['room_no'] ?? 'Unassigned') ?></span>
                            <span><i class="fa-solid fa-code-branch"></i> <?= htmlspecialchars($item['subject_code']) ?></span>
                        </div>
                    </div>

                    <div class="action-block">
                        <?php if($is_live): ?>
                            <button class="node-btn active" onclick="alert('Syncing with live session...')">Join Node</button>
                        <?php elseif($is_past): ?>
                            <button class="node-btn done" disabled><i class="fa-solid fa-check-double"></i> Completed</button>
                        <?php else: ?>
                            <button class="node-btn locked" disabled><i class="fa-solid fa-lock"></i> Locked</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function switchScheduleDay(dayAbbr) {
    if (typeof loadModule === "function") {
        loadModule(`schedule&day=${dayAbbr}`, document.getElementById('nav-schedule'));
    } else {
        window.location.href = `?day=${dayAbbr}`;
    }
}
</script>

<style>
/* Nexus Dynamic Styling */
:root {
    --nexus-blue: #6366f1;
    --nexus-blue-glow: rgba(99, 102, 241, 0.4);
    --nexus-success: #10b981;
    --nexus-text-muted: #64748b;
    --nexus-border: rgba(255, 255, 255, 0.1);
}

.module-entrance { animation: slideUp 0.4s ease-out forwards; }
.schedule-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 20px; }
.module-title { font-size: 1.6rem; color: #1e293b; letter-spacing: -0.5px; }
.glow-text { color: var(--nexus-blue); text-shadow: 0 0 15px var(--nexus-blue-glow); }
.module-subtitle { color: var(--nexus-text-muted); font-size: 0.9rem; margin-top: 4px; }

.week-selector { display: flex; gap: 4px; padding: 6px; border-radius: 14px; background: rgba(255,255,255,0.7); border: 1px solid rgba(0,0,0,0.05); }
.day-pill { background: transparent; border: none; color: var(--nexus-text-muted); padding: 10px 18px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: 0.3s; }
.day-pill:hover { background: rgba(99, 102, 241, 0.08); color: var(--nexus-blue); }
.active-day { background: var(--nexus-blue) !important; color: #fff !important; box-shadow: 0 8px 15px var(--nexus-blue-glow); transform: translateY(-1px); }

.timeline-container { display: flex; flex-direction: column; gap: 14px; }
.schedule-card { display: grid; grid-template-columns: 130px 1fr 160px; padding: 24px; align-items: center; border-radius: 18px; border: 1px solid var(--nexus-border); background: #fff; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04); transition: transform 0.2s; }
.schedule-card:hover { transform: scale(1.01); }

.live-highlight { border-left: 6px solid var(--nexus-blue); background: linear-gradient(90deg, rgba(99, 102, 241, 0.03) 0%, #fff 100%); }
.slot-time { font-family: 'JetBrains Mono'; font-weight: 800; color: #1e293b; font-size: 1.1rem; }
.status-bar { width: 35px; height: 5px; border-radius: 10px; margin-top: 10px; background: #e2e8f0; }
.status-bar.live { background: var(--nexus-blue); box-shadow: 0 0 8px var(--nexus-blue); }
.status-bar.done { background: var(--nexus-success); }

.subject-title { font-size: 1.1rem; color: #1e293b; display: flex; align-items: center; gap: 12px; font-weight: 700; }
.type-pill { font-size: 0.65rem; background: #f1f5f9; color: #64748b; padding: 2px 8px; border-radius: 5px; text-transform: uppercase; font-weight: 800; border: 1px solid #e2e8f0; }
.live-tag { font-size: 0.6rem; background: var(--nexus-blue); color: #fff; padding: 3px 8px; border-radius: 6px; animation: nexusPulse 2s infinite; font-weight: 800; }

.meta-row { display: flex; gap: 18px; margin-top: 8px; font-size: 0.8rem; color: var(--nexus-text-muted); }
.meta-row i { color: var(--nexus-blue); opacity: 0.7; }

.node-btn { padding: 12px; border-radius: 12px; font-weight: 700; border: 1px solid #e2e8f0; cursor: pointer; font-size: 0.75rem; width: 100%; transition: all 0.2s; }
.node-btn.active { background: var(--nexus-blue); color: #fff; border: none; box-shadow: 0 4px 12px var(--nexus-blue-glow); }
.node-btn.done { color: var(--nexus-success); background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.2); }
.node-btn.locked { color: #94a3b8; background: #f8fafc; cursor: not-allowed; }

.empty-state { padding: 80px 20px; text-align: center; color: var(--nexus-text-muted); border: 2px dashed rgba(0,0,0,0.05); }

@keyframes nexusPulse { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.7; transform: scale(0.95); } 100% { opacity: 1; transform: scale(1); } }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
    .schedule-card { grid-template-columns: 1fr; gap: 20px; text-align: center; }
    .meta-row { justify-content: center; }
    .status-bar { margin: 10px auto; }
}
</style>