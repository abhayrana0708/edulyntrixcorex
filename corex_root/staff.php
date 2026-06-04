<?php
require_once('../includes/db_connect.php');

/**
 * EDULYNTRIX CORE X - PERSONNEL ARCHIVE
 * Final Build: Live Search + Phone/Date Sync + Glassmorphism
 */

try {
    $staff_query = "SELECT s.*, d.dept_name 
                    FROM staff s 
                    LEFT JOIN departments d ON s.dept_id = d.id 
                    ORDER BY s.id DESC";
    $staff = $pdo->query($staff_query)->fetchAll();
    $depts = $pdo->query("SELECT id, dept_name FROM departments WHERE status = 'Active'")->fetchAll();
} catch (PDOException $e) {
    echo "<div class='mgmt-card' style='color:#f87171;'>System Link Failure: " . $e->getMessage() . "</div>";
}
?>

<div class="supreme-container">
    <div class="archive-header">
        <div class="title-block">
            <h1>Personnel <span class="cyan-text">Archive</span></h1>
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="nodeSearch" placeholder="SEARCH IDENTITY OR UID..." onkeyup="filterNodes()">
            </div>
        </div>
        <button class="deploy-btn" onclick="window.location.href='layout.php?page=staff_provision'">
            + AUTHORIZE NEW IDENTITY
        </button>
    </div>

    <div class="table-viewport">
        <table class="supreme-table" id="staffTable">
            <thead>
                <tr>
                    <th>UID</th>
                    <th>Identity</th>
                    <th>Infrastructure</th>
                    <th>Access Key</th>
                    <th>Status</th>
                    <th style="text-align: right;">Override</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($staff) > 0): ?>
                    <?php foreach ($staff as $s): ?>
                    <tr class="node-row">
                        <td class="mono-font"><?php echo $s['staff_id']; ?></td>
                        <td>
                            <div class="name-text"><?php echo htmlspecialchars($s['full_name']); ?></div>
                            <div class="small-text cyan-text"><?php echo htmlspecialchars($s['designation']); ?></div>
                        </td>
                        <td>
                            <div class="small-text" style="color:#cbd5e1;"><?php echo htmlspecialchars($s['dept_name'] ?? 'ORPHAN'); ?></div>
                            <div class="extra-small-text"><?php echo htmlspecialchars($s['email']); ?></div>
                        </td>
                        <td>
                            <span class="key-capsule"><?php echo htmlspecialchars($s['password']); ?></span>
                        </td>
                        <td>
                            <div class="status-indicator <?php echo strtolower($s['status']); ?>">
                                <span class="dot"></span>
                                <span class="status-label"><?php echo strtoupper($s['status']); ?></span>
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div class="btn-group">
                                <button class="action-btn" onclick='viewDeepScan(<?php echo json_encode($s); ?>)' title="Scan">👁</button>
                                <button class="action-btn" onclick='editStaff(<?php echo json_encode($s); ?>)' title="Modify">⚙</button>
                                <button class="action-btn delete" onclick="if(confirm('PURGE IDENTITY?')) window.location.href='staff_manager.php?action=purge&id=<?php echo $s['id']; ?>'">✖</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="noData"><td colspan="6" class="empty-msg">NO ARCHIVED IDENTITIES DETECTED</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="deepScanModal" class="supreme-modal">
    <div class="modal-content glass-card" style="border-top: 4px solid var(--accent);">
        <h3 class="modal-title">IDENTITY DEEP SCAN</h3>
        <div class="scan-grid">
            <div class="scan-box"><label>System ID</label><div id="scan_id"></div></div>
            <div class="scan-box"><label>Legal Name</label><div id="scan_name"></div></div>
            <div class="scan-box"><label>Network Email</label><div id="scan_email"></div></div>
            <div class="scan-box"><label>Phone Registry</label><div id="scan_phone"></div></div>
            <div class="scan-box"><label>Joined Date</label><div id="scan_date"></div></div>
            <div class="scan-box full-width">
                <label style="color: #fbbf24;">CLEAR-TEXT ACCESS KEY</label>
                <div id="scan_pass" class="key-monitor"></div>
            </div>
        </div>
        <button class="abort-btn" onclick="closeScanModal()" style="width:100%">TERMINATE SCAN</button>
    </div>
</div>

<div id="editStaffModal" class="supreme-modal">
    <div class="modal-content glass-card" style="border-top: 4px solid #fbbf24;">
        <h3 class="modal-title" style="color: #fbbf24;">UPDATE IDENTITY</h3>
        <form action="staff_manager.php" method="POST" class="supreme-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Access Key (Plain)</label>
                    <input type="text" name="password" id="edit_password" required style="color: var(--accent); font-family: monospace;">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phone Registry</label>
                    <input type="text" name="phone" id="edit_phone">
                </div>
                <div class="form-group">
                    <label>Joined Date</label>
                    <input type="date" name="joined_date" id="edit_joined_date" style="color-scheme: dark;">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Dept. Node</label>
                    <select name="dept_id" id="edit_dept">
                        <?php foreach($depts as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo $d['dept_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Designation</label>
                    <input type="text" name="designation" id="edit_designation" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Access Status</label>
                    <select name="status" id="edit_status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">SYNC IDENTITY</button>
                <button type="button" class="abort-btn" onclick="closeEditModal()">ABORT</button>
            </div>
        </form>
    </div>
</div>

<style>
/* CSS remains consistent with previous Supreme Power styling */
:root { --accent: #00f2ff; --glass: rgba(15, 23, 42, 0.95); }
.supreme-container { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px); border: 1px solid rgba(0, 242, 255, 0.1); border-radius: 12px; padding: 25px; margin: 10px; }
.search-box { margin-top: 10px; position: relative; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 5px 15px; display: flex; align-items: center; }
.search-icon { font-size: 0.8rem; margin-right: 10px; opacity: 0.5; }
#nodeSearch { background: transparent; border: none; color: #fff; font-family: 'JetBrains Mono'; font-size: 0.75rem; outline: none; width: 250px; }
.archive-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
.title-block h1 { margin: 0; text-transform: uppercase; font-size: 1.4rem; color: #fff; letter-spacing: 1px; }
.subtitle { font-size: 0.7rem; color: #64748b; font-family: 'JetBrains Mono'; margin: 5px 0; }
.table-viewport { width: 100%; overflow-x: auto; }
.supreme-table { width: 100%; border-collapse: collapse; min-width: 900px; }
.supreme-table th { text-align: left; padding: 15px; font-size: 0.65rem; color: #64748b; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,0.05); }
.node-row { border-bottom: 1px solid rgba(255,255,255,0.02); transition: 0.2s; }
.node-row:hover { background: rgba(0, 242, 255, 0.04); }
.node-row td { padding: 15px; vertical-align: middle; color: #f1f5f9; }
.supreme-modal { display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(10px); z-index: 9999; align-items: center; justify-content: center; }
.modal-content { background: var(--glass); width: 100%; max-width: 600px; padding: 30px; border-radius: 8px; }
.scan-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
.scan-box label { font-size: 0.6rem; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 5px; }
.scan-box div { font-size: 0.9rem; color: #fff; }
.full-width { grid-column: span 2; }
.key-monitor { background: #000; padding: 15px; color: var(--accent); border: 1px solid #334155; text-align: center; font-family: monospace; font-size: 1.2rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.form-group label { display: block; font-size: 0.7rem; color: #64748b; margin-bottom: 8px; }
.form-group input, .form-group select { width: 100%; background: #1e293b; border: 1px solid #334155; padding: 10px; color: #fff; border-radius: 4px; }
.deploy-btn { background: var(--accent); color: #000; border: none; padding: 10px 20px; font-weight: 800; border-radius: 4px; cursor: pointer; }
.submit-btn { background: #fbbf24; color: #000; border: none; padding: 12px 25px; font-weight: 800; border-radius: 4px; cursor: pointer; }
.abort-btn { background: rgba(255,255,255,0.05); border: 1px solid #334155; color: #94a3b8; padding: 12px 25px; border-radius: 4px; cursor: pointer; }
.dot { width: 8px; height: 8px; border-radius: 50%; background: #64748b; display: inline-block; }
.active .dot { background: var(--accent); box-shadow: 0 0 10px var(--accent); }
.status-indicator { display: flex; align-items: center; gap: 8px; font-size: 0.65rem; font-weight: 900; }
</style>

<script>
function filterNodes() {
    let input = document.getElementById("nodeSearch").value.toUpperCase();
    let table = document.getElementById("staffTable");
    let tr = table.getElementsByClassName("node-row");
    for (let i = 0; i < tr.length; i++) {
        let name = tr[i].getElementsByClassName("name-text")[0];
        let uid = tr[i].getElementsByClassName("mono-font")[0];
        if (name || uid) {
            let txtValue = (name.textContent || name.innerText) + (uid.textContent || uid.innerText);
            tr[i].style.display = txtValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
        }
    }
}

function viewDeepScan(data) {
    document.getElementById('scan_id').innerText = data.staff_id;
    document.getElementById('scan_name').innerText = data.full_name;
    document.getElementById('scan_email').innerText = data.email;
    document.getElementById('scan_phone').innerText = data.phone || 'NO REGISTRY';
    document.getElementById('scan_date').innerText = data.joined_date || 'UNKNOWN';
    document.getElementById('scan_pass').innerText = data.password;
    document.getElementById('deepScanModal').style.display = 'flex';
}

function editStaff(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_name').value = data.full_name;
    document.getElementById('edit_dept').value = data.dept_id;
    document.getElementById('edit_designation').value = data.designation;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_status').value = data.status;
    document.getElementById('edit_password').value = data.password; 
    document.getElementById('edit_phone').value = data.phone || ''; 
    document.getElementById('edit_joined_date').value = data.joined_date || ''; 
    document.getElementById('editStaffModal').style.display = 'flex';
}

function closeScanModal() { document.getElementById('deepScanModal').style.display = 'none'; }
function closeEditModal() { document.getElementById('editStaffModal').style.display = 'none'; }

window.onclick = function(e) {
    if (e.target.className === 'supreme-modal') {
        closeScanModal();
        closeEditModal();
    }
}
</script>