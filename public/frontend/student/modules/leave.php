<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../../../includes/db_connect.php'; 

$sid = $_SESSION['student_id'] ?? 'Unknown';
$requests = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM leave_requests WHERE student_id = ? ORDER BY applied_on DESC");
    $stmt->execute([$sid]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $error = "Table Sync Error"; }
?>

<div class="leave-module-wrapper module-entrance">
    <div class="module-header" style="margin-bottom: 25px;">
        <h3 style="color: #00d2ff; font-weight:800;">Leave Application</h3>
        <p style="color: #666; font-size: 0.8rem;">ID: <?= htmlspecialchars($sid) ?></p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 25px;">
        <div class="glass-effect" style="padding: 25px; background: rgba(255,255,255,0.03); border-radius: 15px; border: 1px solid rgba(255,255,255,0.1);">
            <form id="nexusLeaveForm" onsubmit="return false;">
                <div style="margin-bottom: 15px;">
                    <label class="section-tag">Category</label>
                    <select name="leave_type" id="nx_type" style="width:100%; padding:12px; background:#111; color:#fff; border:1px solid #333; border-radius:8px;">
                        <option value="Medical">Medical</option>
                        <option value="Casual">Casual</option>
                        <option value="Duty">On Duty (OD)</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom: 15px;">
                    <input type="date" name="start_date" id="nx_start" required style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333; border-radius:8px;">
                    <input type="date" name="end_date" id="nx_end" required style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333; border-radius:8px;">
                </div>
                <textarea name="reason" id="nx_reason" rows="4" required style="width:100%; padding:12px; background:#111; color:#fff; border:1px solid #333; border-radius:8px; margin-bottom: 20px;" placeholder="Explain your reason..."></textarea>
                
                <button type="button" onclick="NexusLeaveSync()" id="nx_submit_btn" style="width:100%; padding:15px; background:#00d2ff; color:#000; font-weight:bold; border:none; border-radius:8px; cursor:pointer;">
                    TRANSMIT APPLICATION
                </button>
            </form>
        </div>

        <div class="glass-effect" style="padding: 25px; background: rgba(255,255,255,0.02); border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
            <small class="section-tag">Request History</small>
            <div style="margin-top: 20px; max-height: 350px; overflow-y: auto;">
                <?php if (empty($requests)): ?>
                    <p style="text-align:center; color:#444; margin-top:50px;">No leave logs found.</p>
                <?php else: ?>
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align:left; font-size:0.7rem; color:#888; border-bottom: 1px solid #222;">
                                <th style="padding: 10px 5px;">DATE</th>
                                <th style="padding: 10px 5px;">TYPE</th>
                                <th style="padding: 10px 5px;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($requests as $r): ?>
                            <tr style="border-bottom: 1px solid #111; font-size: 0.85rem; color: #ccc;">
                                <td style="padding:12px 5px;"><?= htmlspecialchars($r['start_date']) ?></td>
                                <td style="padding:12px 5px;"><?= htmlspecialchars($r['leave_type']) ?></td>
                                <td style="padding:12px 5px;"><span style="font-weight:bold; color: <?= $r['status'] == 'Approved' ? '#00ff88' : ($r['status'] == 'Rejected' ? '#ff4d4d' : '#f59e0b') ?>;"><?= strtoupper($r['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>