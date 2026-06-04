<?php
/** fix the bud dont change the structure of code :<?php
 * EDULYNTRIX CORE X - ATTENDANCE AUDIT NODE
 * Version 11.1.1: Fixed Profile Image Pathing & UI Sync
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../../../../includes/db_connect.php';

// 1. RESOLVE HOD IDENTITY
$s_dept_id = $_SESSION['dept_id'] ?? null;
$s_dept_name = $_SESSION['department'] ?? $_SESSION['dept_name'] ?? 'Mechanical Engineering';

if (!$s_dept_id) {
    $map = ['Computer Science' => 1, 'Information Technology' => 2, 'Mechanical Engineering' => 3, 'Electrical Engineering' => 4];
    $s_dept_id = $map[$s_dept_name] ?? 3; 
    $_SESSION['dept_id'] = $s_dept_id;
}

try {
    $sql = "SELECT 
                s.full_name, s.student_id, s.profile_pic,
                COUNT(a.attendance_id) as total_classes,
                SUM(CASE WHEN LOWER(a.status) = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN LOWER(a.status) = 'late' THEN 1 ELSE 0 END) as late_count,
                ROUND((SUM(CASE WHEN LOWER(a.status) = 'present' OR LOWER(a.status) = 'late' THEN 1 ELSE 0 END) / NULLIF(COUNT(a.attendance_id), 0)) * 100, 1) as percentage
            FROM students s
            LEFT JOIN attendance a ON s.student_id = a.student_id
            WHERE s.dept_id = ? 
            GROUP BY s.student_id, s.full_name, s.profile_pic
            ORDER BY percentage ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$s_dept_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Audit Error: " . $e->getMessage());
    $records = [];
}
?>

<div id="auditModal" onclick="closeAuditModal(event)" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(2, 6, 23, 0.85); z-index:9999; backdrop-filter:blur(12px); align-items:center; justify-content:center; animation: fadeIn 0.3s ease;">
    <div id="modalContainer" style="background:#0f172a; width:90%; max-width:650px; border-radius:24px; border:1px solid rgba(255,255,255,0.1); padding:0; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); overflow:hidden;">
        
        <div style="padding:30px 35px 20px; border-bottom:1px solid rgba(255,255,255,0.05);">
            <button onclick="document.getElementById('auditModal').style.display='none'" style="position:absolute; top:25px; right:25px; background:rgba(255,255,255,0.05); border:none; color:#64748b; width:35px; height:35px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.3s;">&times;</button>
            <h3 style="color:#fff; margin:0 0 5px 0; font-weight:800;">Secure <span style="color:#10b981;">Log Audit</span></h3>
            <p id="modalStudentName" style="color:#475569; font-size:0.7rem; margin:0; font-family:'JetBrains Mono'; letter-spacing:1px;"></p>
        </div>
        
        <div id="modalBody" class="custom-scrollbar" style="max-height:400px; overflow-y:auto; padding:20px 35px;">
            <div style="padding:40px; text-align:center; color:#10b981; font-size:0.8rem;">INITIALIZING SECURE DATA STREAM...</div>
        </div>

        <div style="padding:20px 35px; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05); text-align: right;">
            <button onclick="document.getElementById('auditModal').style.display='none'" 
                    style="padding: 10px 25px; background: #10b981; color: #000; border: none; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 0.7rem; text-transform: uppercase; transition: 0.3s;">
                Return to Command
            </button>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #10b981 rgba(255,255,255,0.02); }
    #modalBody::-webkit-scrollbar { width: 4px; }
    #modalBody::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
    #modalBody::-webkit-scrollbar-thumb { background: #10b981; border-radius: 10px; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<div class="fade-in-up" style="padding: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="color: #fff; font-weight: 800; font-size: 1.8rem; margin: 0;">Attendance <span style="color: #10b981;">Audit</span></h2>
            <p style="color: #94a3b8; font-size: 0.8rem;">Command Node: <b style="color: #10b981;"><?= htmlspecialchars($s_dept_name) ?></b></p>
        </div>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 8px 15px; border-radius: 8px; text-align: right;">
            <span style="color: #64748b; font-size: 0.6rem; text-transform: uppercase; font-weight: 800; display: block;">Authorized ID</span>
            <span style="color: #10b981; font-family: 'JetBrains Mono'; font-weight: 800;">DEPT_00<?= $s_dept_id ?></span>
        </div>
    </div>

    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 20px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(0,0,0,0.3); border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <th style="padding: 20px; text-align: left; color: #64748b; font-size: 0.65rem; text-transform: uppercase;">Student Profile</th>
                    <th style="padding: 20px; text-align: left; color: #64748b; font-size: 0.65rem; text-transform: uppercase;">Engagement</th>
                    <th style="padding: 20px; text-align: left; color: #64748b; font-size: 0.65rem; text-transform: uppercase;">Presence Ratio</th>
                    <th style="padding: 20px; text-align: right; color: #64748b; font-size: 0.65rem; text-transform: uppercase;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="4" style="padding: 50px; text-align: center; color: #475569;">No student data available for this node.</td></tr>
                <?php else: ?>
                    <?php foreach ($records as $row): 
                        $p = $row['percentage'] ?? 0;
                        $color = ($p >= 75) ? '#10b981' : (($p >= 60) ? '#f59e0b' : '#f87171');
                        
                        // FIX: Using absolute web path to avoid broken links
                        $p_img = !empty($row['profile_pic']) 
                            ? "/EdulyntrixCoreX/assets/img/profiles/".$row['profile_pic'] 
                            : "https://ui-avatars.com/api/?background=0f172a&color=10b981&bold=true&name=".urlencode($row['full_name']);
                    ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); transition: 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 15px 20px; display: flex; align-items: center; gap: 15px;">
                            <img src="<?= $p_img ?>" style="width: 40px; height: 40px; border-radius: 10px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);" onerror="this.src='https://ui-avatars.com/api/?background=0f172a&color=f87171&name=Error';">
                            <div>
                                <div style="color: #fff; font-weight: 700; font-size: 0.9rem;"><?= htmlspecialchars($row['full_name']) ?></div>
                                <div style="color: #475569; font-family: 'JetBrains Mono'; font-size: 0.65rem;"><?= htmlspecialchars($row['student_id']) ?></div>
                            </div>
                        </td>
                        <td style="padding: 20px;">
                            <div style="color: #cbd5e1; font-size: 0.8rem;">
                                <span style="color: #10b981;"><?= (int)$row['present_count'] ?>P</span> / 
                                <span style="color: #f87171;"><?= (int)$row['total_classes'] - ((int)$row['present_count'] + (int)$row['late_count']) ?>A</span>
                                <span style="color: #475569; font-size: 0.65rem; margin-left: 5px;">of <?= $row['total_classes'] ?></span>
                            </div>
                        </td>
                        <td style="padding: 20px; width: 220px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="height: 6px; flex: 1; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden;">
                                    <div style="width: <?= $p ?>%; height: 100%; background: <?= $color ?>; box-shadow: 0 0 10px <?= $color ?>44;"></div>
                                </div>
                                <span style="color: <?= $color ?>; font-weight: 800; font-family: 'JetBrains Mono'; font-size: 0.75rem;"><?= $p ?>%</span>
                            </div>
                        </td>
                        <td style="padding: 20px; text-align: right;">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <button onclick="viewStudentAudit('<?= $row['student_id'] ?>', '<?= addslashes($row['full_name']) ?>')" 
                                        style="padding: 8px 15px; font-size: 0.6rem; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; cursor: pointer; text-transform: uppercase;">
                                    VIEW DETAILS
                                </button>
                                <?php if ($p < 75): ?>
                                    <button id="btn-<?= $row['student_id'] ?>" 
                                            onclick="issueStudentWarning('<?= $row['student_id'] ?>', '<?= addslashes($row['full_name']) ?>')" 
                                            style="padding: 8px 15px; font-size: 0.6rem; background: #f87171; color: #000; border: none; border-radius: 6px; cursor: pointer; font-weight: 800; text-transform: uppercase;">
                                        ISSUE WARNING
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function closeAuditModal(event) {
    if (event.target === document.getElementById('auditModal')) {
        document.getElementById('auditModal').style.display = 'none';
    }
}

function viewStudentAudit(sid, sname) {
    const modal = document.getElementById('auditModal');
    const body = document.getElementById('modalBody');
    const nameLabel = document.getElementById('modalStudentName');
    
    modal.style.display = 'flex';
    nameLabel.innerText = "ACCESSING AUDIT TRAIL FOR: " + sname + " [" + sid + "]";
    body.innerHTML = "<div style='padding:50px; text-align:center; color:#10b981; font-weight:800; font-family:\"JetBrains Mono\";'>FETCHING DATA NODES...</div>";

    fetch('modules/get_student_details.php?student_id=' + sid)
        .then(res => res.text())
        .then(data => {
            body.innerHTML = data;
        })
        .catch(err => {
            body.innerHTML = "<div style='color:#f87171; padding:20px;'>ENCRYPTION ERROR: Connection Lost.</div>";
        });
}

function issueStudentWarning(sid, sname) {
    if(!confirm("COMMAND CONFIRMATION: Issue official warning to " + sname + "?")) return;

    const targetBtn = document.getElementById('btn-' + sid);
    const originalText = targetBtn.innerHTML;
    targetBtn.disabled = true;
    targetBtn.innerHTML = "TRANSMITTING...";

    fetch('modules/process_warning.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'student_id=' + encodeURIComponent(sid)
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            targetBtn.innerHTML = "ISSUED";
            targetBtn.style.background = "#10b981";
            targetBtn.style.color = "#fff";
        } else {
            alert("ERROR: " + data.message);
            targetBtn.disabled = false;
            targetBtn.innerHTML = originalText;
        }
    });
}
</script>