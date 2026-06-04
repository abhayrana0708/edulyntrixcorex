<?php
require_once('../includes/db_connect.php');

// Fetch Sessions from the Timeline Node
$sessions = [];
try {
    $stmt = $pdo->query("SELECT * FROM academic_sessions ORDER BY start_date DESC");
    $sessions = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Timeline Sync Error: " . $e->getMessage();
}
?>

<div class="mgmt-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="font-size: 1.2rem; letter-spacing: -0.5px;">Temporal Control</h2>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Manage institutional timelines and active academic cycles.</p>
        </div>
        <button class="prime-btn" style="width: auto; padding: 10px 24px; font-size: 0.75rem;" onclick="openSessionModal()">+ INITIALIZE NEW SESSION</button>
    </div>

    <table class="core-table">
        <thead>
            <tr>
                <th>#</th> 
                <th>DESIGNATION</th>
                <th>START DATE</th>
                <th>END DATE</th>
                <th>CURRENT</th>
                <th>STATUS</th>
                <th style="text-align: right;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sessions) > 0): ?>
                <?php 
                $count = 1; 
                foreach ($sessions as $s): 
                ?>
                <tr style="<?php echo ($s['is_current']) ? 'background: rgba(251, 191, 36, 0.03);' : ''; ?>">
                    <td><span class="mono-id"><?php echo str_pad($count++, 2, "0", STR_PAD_LEFT); ?></span></td>
                    <td style="font-weight: 700; color: var(--accent);"><?php echo htmlspecialchars($s['session_name']); ?></td>
                    <td style="font-family: 'JetBrains Mono'; font-size: 0.8rem;"><?php echo date("d/m/Y", strtotime($s['start_date'])); ?></td>
                    <td style="font-family: 'JetBrains Mono'; font-size: 0.8rem;"><?php echo date("d/m/Y", strtotime($s['end_date'])); ?></td>
                    <td>
                        <?php if($s['is_current']): ?>
                            <span style="color: #fbbf24; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">[ ACTIVE MASTER ]</span>
                        <?php else: ?>
                            <span style="opacity: 0.3; font-size: 0.65rem;">STANDBY</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge" style="background: rgba(0, 242, 255, 0.05); color: var(--accent); border: 1px solid var(--border);">
                            <?php echo strtoupper($s['status']); ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                            <?php if(!$s['is_current']): ?>
                                <a href="session_manager.php?action=activate&id=<?php echo $s['id']; ?>" class="nav-link" style="padding: 5px 12px; font-size: 0.65rem; border: 1px solid var(--accent); color: var(--accent); text-decoration: none; font-weight: 700;">SET MASTER</a>
                            <?php endif; ?>
                            
                            <button class="nav-link" style="padding: 5px 12px; font-size: 0.65rem; border: 1px solid var(--border); cursor: pointer;"
                                    onclick='editSession(<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8"); ?>)'>Modify</button>
                            
                            <a href="session_manager.php?action=purge&id=<?php echo $s['id']; ?>" class="nav-link" style="padding: 5px 12px; font-size: 0.65rem; border: 1px solid var(--border); color: #f87171; text-decoration: none;" onclick="return confirm('WARNING: Purge this temporal fragment?')">Purge</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">No temporal data found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="sessionModal" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(5px);">
    <div class="mgmt-card" style="width: 450px; border: 1px solid var(--accent); box-shadow: 0 0 30px rgba(0, 242, 255, 0.1);">
        <h3 style="color: var(--accent); margin-bottom: 25px; font-family: 'JetBrains Mono';">INITIALIZE SESSION</h3>
        <form action="session_manager.php" method="POST">
            <input type="hidden" name="action" value="add">
            <label class="clean-label">Session Designation</label>
            <input type="text" name="session_name" class="clean-input" placeholder="e.g., 2026-2027" required style="margin-bottom: 15px;">
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;"><label class="clean-label">Start Date</label><input type="date" name="start_date" class="clean-input" required></div>
                <div style="flex: 1;"><label class="clean-label">End Date</label><input type="date" name="end_date" class="clean-input" required></div>
            </div>
            <label class="clean-label">Institutional Status</label>
            <select name="status" class="clean-input" style="margin-bottom: 25px;">
                <option value="Upcoming">Upcoming</option>
                <option value="Active">Active</option>
            </select>
            <button type="submit" class="prime-btn">AUTHORIZE TIMELINE</button>
            <button type="button" class="prime-btn" onclick="closeSessionModal()" style="background: rgba(255,255,255,0.05); color: #fff; margin-top: 10px; border: 1px solid var(--border);">ABORT</button>
        </form>
    </div>
</div>

<div id="editSessionModal" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(5px);">
    <div class="mgmt-card" style="width: 450px; border: 1px solid #fbbf24; box-shadow: 0 0 30px rgba(251, 191, 36, 0.1);">
        <h3 style="color: #fbbf24; margin-bottom: 25px; font-family: 'JetBrains Mono';">MODIFY TIMELINE</h3>
        <form action="session_manager.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="session_id" id="edit_id"> 
            <label class="clean-label">Session Designation</label>
            <input type="text" name="session_name" id="edit_name" class="clean-input" required style="margin-bottom: 15px;">
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;"><label class="clean-label">Start Date</label><input type="date" name="start_date" id="edit_start" class="clean-input" required></div>
                <div style="flex: 1;"><label class="clean-label">End Date</label><input type="date" name="end_date" id="edit_end" class="clean-input" required></div>
            </div>
            <label class="clean-label">Institutional Status</label>
            <select name="status" id="edit_status" class="clean-input" style="margin-bottom: 25px;">
                <option value="Upcoming">Upcoming</option>
                <option value="Active">Active</option>
                <option value="Concluded">Concluded</option>
            </select>
            <button type="submit" class="prime-btn" style="background: #fbbf24; color: #000;">SYNC TEMPORAL DATA</button>
            <button type="button" class="prime-btn" onclick="document.getElementById('editSessionModal').style.display='none'" style="background: rgba(255,255,255,0.05); color: #fff; margin-top: 10px; border: 1px solid var(--border);">CANCEL</button>
        </form>
    </div>
</div>

<script>
function openSessionModal() { document.getElementById('sessionModal').style.display = 'flex'; }
function closeSessionModal() { document.getElementById('sessionModal').style.display = 'none'; }

function editSession(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_name').value = data.session_name;
    document.getElementById('edit_start').value = data.start_date;
    document.getElementById('edit_end').value = data.end_date;
    document.getElementById('edit_status').value = data.status;
    document.getElementById('editSessionModal').style.display = 'flex';
}
</script>