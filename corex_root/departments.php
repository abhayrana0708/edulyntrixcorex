<?php
require_once('../includes/db_connect.php');

// Fetch live data from the CoreX Infrastructure
try {
    // Keeping your exact sequence and sorting logic
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY id ASC");
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div class='system-log-alert'><span class='log-prefix'>[ CRITICAL_SYNC_ERROR ]</span> " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="mgmt-card animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="font-size: 1.4rem; letter-spacing: -0.5px; font-weight: 800; color: #fff;">Infrastructure <span style="color: var(--accent);">Data</span></h2>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; font-family: 'JetBrains Mono';">Live status of global department nodes.</p>
        </div>
        <button class="prime-btn" style="width: auto; padding: 12px 28px; font-size: 0.75rem; box-shadow: 0 0 20px var(--accent-glow);" onclick="openNodeModal()">+ INITIALIZE NEW NODE</button>
    </div>

    <table class="core-table" style="width: 100%; border-collapse: separate; border-spacing: 0 8px;">
        <thead>
            <tr style="text-align: left;">
                <th style="padding: 15px; font-size: 0.65rem; color: var(--text-muted); letter-spacing: 1px;">#</th> 
                <th style="padding: 15px; font-size: 0.65rem; color: var(--text-muted); letter-spacing: 1px;">DEPARTMENT NAME</th>
                <th style="padding: 15px; font-size: 0.65rem; color: var(--text-muted); letter-spacing: 1px;">HEAD OF DEPT.</th>
                <th style="padding: 15px; font-size: 0.65rem; color: var(--text-muted); letter-spacing: 1px;">CAPACITY</th>
                <th style="padding: 15px; font-size: 0.65rem; color: var(--text-muted); letter-spacing: 1px;">STATUS</th>
                <th style="text-align: right; padding: 15px; font-size: 0.65rem; color: var(--text-muted); letter-spacing: 1px;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($departments) > 0): ?>
                <?php 
                $count = 1; 
                foreach ($departments as $dept): 
                ?>
                <tr style="background: var(--glass); transition: 0.3s;">
                    <td style="padding: 15px; border-radius: 10px 0 0 10px;"><span class="mono-id" style="font-family: 'JetBrains Mono'; color: var(--accent);"><?php echo str_pad($count++, 2, "0", STR_PAD_LEFT); ?></span></td>
                    
                    <td style="padding: 15px; font-weight: 700; color: #fff;"><?php echo htmlspecialchars($dept['dept_name']); ?></td>
                    <td style="padding: 15px; color: var(--text-main);"><?php echo htmlspecialchars($dept['dept_head']); ?></td>
                    <td style="padding: 15px;"><span class="mono-id" style="font-size: 0.8rem; opacity: 0.8; font-family: 'JetBrains Mono';"><?php echo $dept['capacity']; ?> Units</span></td>
                    <td style="padding: 15px;">
                        <div class="status-badge" style="display: inline-flex; padding: 6px 14px; background: rgba(0, 242, 255, 0.05); border: 1px solid var(--border); border-radius: 20px;">
                            <span class="pulse-dot"></span>
                            <span class="status-label" style="font-size: 0.65rem; font-weight: 800; color: var(--accent);"><?php echo strtoupper($dept['status']); ?></span>
                        </div>
                    </td>
                    <td style="text-align: right; padding: 15px; border-radius: 0 10px 10px 0;">
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button class="nav-link" style="padding: 6px 14px; font-size: 0.7rem; border: 1px solid var(--border); cursor: pointer; background: transparent; color: var(--text-main); border-radius: 6px; transition: 0.2s;" 
                                    onclick='openEditModal(<?php echo htmlspecialchars(json_encode($dept), ENT_QUOTES, "UTF-8"); ?>)'>
                                Modify
                            </button>
                            
                            <a href="dept_manager.php?action=purge&id=<?php echo $dept['id']; ?>" 
                               onclick="return confirm('SYSTEM WARNING: Purge operation is irreversible. Proceed?')"
                               class="nav-link" style="padding: 6px 14px; font-size: 0.7rem; border: 1px solid var(--danger); color: var(--danger); text-decoration: none; border-radius: 6px; transition: 0.2s;">
                               Purge
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; padding: 60px; color: var(--text-muted); font-family: 'JetBrains Mono';">No infrastructure nodes detected in registry.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="nodeModal" style="display:none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.9); align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(12px);">
    <div class="mgmt-card" style="width: 480px; border: 1px solid var(--accent); box-shadow: 0 0 40px rgba(0, 242, 255, 0.1);">
        <h3 style="color: var(--accent); margin-bottom: 25px; font-family: 'JetBrains Mono'; font-weight: 800; letter-spacing: 1px;">INITIALIZE CORE NODE</h3>
        <form action="dept_manager.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <label class="clean-label">Select Department Node</label>
            <select name="dept_name" class="clean-input" required>
                <option value="" disabled selected>Select Designation...</option>
                <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                <option value="Information Technology">Information Technology</option>
                <option value="Mechanical Engineering">Mechanical Engineering</option>
                <option value="Electrical Engineering">Electrical Engineering</option>
            </select>
            
            <label class="clean-label">Authority Head</label>
            <input type="text" name="dept_head" class="clean-input" placeholder="Enter HOD Name" required>
            
            <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                <div style="flex: 1;"><label class="clean-label">Capacity</label><input type="number" name="capacity" class="clean-input" value="100"></div>
                <div style="flex: 1;"><label class="clean-label">Status</label>
                    <select name="status" class="clean-input">
                        <option value="Active">Active</option>
                        <option value="Standby">Standby</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="prime-btn">DEPLOY NODE</button>
            <button type="button" class="prime-btn" onclick="closeNodeModal()" style="background: rgba(255,255,255,0.03); margin-top: 12px; border: 1px solid var(--border); color: #fff;">ABORT MISSION</button>
        </form>
    </div>
</div>

<div id="editModal" style="display:none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.9); align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(12px);">
    <div class="mgmt-card" style="width: 480px; border: 1px solid var(--warning); box-shadow: 0 0 40px rgba(251, 191, 36, 0.1);">
        <h3 style="color: var(--warning); margin-bottom: 25px; font-family: 'JetBrains Mono'; font-weight: 800; letter-spacing: 1px;">MODIFY CORE NODE</h3>
        <form action="dept_manager.php" method="POST">
            <input type="hidden" name="action" value="edit"> 
            <input type="hidden" name="dept_id" id="edit_id"> 
            
            <label class="clean-label">Department Designation</label>
            <input type="text" name="dept_name" id="edit_name" class="clean-input" readonly style="opacity: 0.5; cursor: not-allowed; border-color: rgba(255,255,255,0.1);">
            
            <label class="clean-label">Authority Head</label>
            <input type="text" name="dept_head" id="edit_head" class="clean-input" required>
            
            <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                <div style="flex: 1;"><label class="clean-label">Capacity</label><input type="number" name="capacity" id="edit_capacity" class="clean-input"></div>
                <div style="flex: 1;"><label class="clean-label">Status</label>
                    <select name="status" id="edit_status" class="clean-input">
                        <option value="Active">Active</option>
                        <option value="Standby">Standby</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="prime-btn" style="background: var(--warning); color: #000;">SYNC CHANGES</button>
            <button type="button" class="prime-btn" onclick="closeEditModal()" style="background: rgba(255,255,255,0.03); margin-top: 12px; border: 1px solid var(--border); color: #fff;">DISCARD CHANGES</button>
        </form>
    </div>
</div>
<script>

function openNodeModal() { document.getElementById('nodeModal').style.display = 'flex'; }

function closeNodeModal() { document.getElementById('nodeModal').style.display = 'none'; }

function openEditModal(data) {

    document.getElementById('edit_id').value = data.id;

    document.getElementById('edit_name').value = data.dept_name;

    document.getElementById('edit_head').value = data.dept_head;

    document.getElementById('edit_capacity').value = data.capacity;

    document.getElementById('edit_status').value = data.status;

    document.getElementById('editModal').style.display = 'flex';

}

function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }

window.onclick = function(event) {

if (event.target == document.getElementById('nodeModal')) closeNodeModal();

if (event.target == document.getElementById('editModal')) closeEditModal();

}

</script>