<?php
require_once('../includes/db_connect.php');

/**
 * EDULYNTRIX CORE X - COMMAND CENTER
 * Theme: Supreme Power (Real-time Analytics)
 * Version 2.1.1: Optimized for Responsive Flow
 */

try {
    // 1. Fetch Vital Statistics
    $staff_count = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
    $dept_count  = $pdo->query("SELECT COUNT(*) FROM departments WHERE status = 'Active'")->fetchColumn();
    
    $student_count = 0; 
    try { $student_count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(); } catch(Exception $e){}

    $pending_enrollments = 0;
    try { 
        $pending_enrollments = $pdo->query("SELECT COUNT(*) FROM enrollment_queue WHERE status = 'pending'")->fetchColumn(); 
    } catch(Exception $e){}

    // 2. Fetch Recent Personnel Activity
    $recent_staff = $pdo->query("SELECT full_name, designation, joined_date FROM staff ORDER BY id DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    echo "Analytics Link Failure: " . $e->getMessage();
}
?>

<div class="command-center animate-fade-in">
    <div class="dashboard-header">
        <h1 class="glow-text">Command <span class="cyan-text">Center</span></h1>
        <p class="system-status">SYSTEM FREQUENCY: <span class="pulse-text">60HZ // STABLE</span></p>
    </div>

    <div class="metrics-grid">
        <div class="metric-card animate-slide-up" style="--delay: 0.1s">
            <div class="metric-label">TOTAL PERSONNEL</div>
            <div class="metric-value"><?php echo number_format($staff_count); ?></div>
            <div class="metric-footer">ACTIVE IDENTITIES</div>
        </div>
        <div class="metric-card animate-slide-up" style="--delay: 0.2s">
            <div class="metric-label">INFRASTRUCTURE NODES</div>
            <div class="metric-value"><?php echo number_format($dept_count); ?></div>
            <div class="metric-footer">OPERATIONAL DEPTS</div>
        </div>
        <div class="metric-card animate-slide-up" style="--delay: 0.3s">
            <div class="metric-label">STUDENT REGISTRY</div>
            <div class="metric-value"><?php echo number_format($student_count); ?></div>
            <div class="metric-footer">NEXUS LIGHT NODES</div>
        </div>
        <div class="metric-card animate-slide-up pending-alert" style="--delay: 0.4s;">
            <div class="metric-label">PENDING ADMISSIONS</div>
            <div class="metric-value" style="color: #f59e0b;"><?php echo number_format($pending_enrollments); ?></div>
            <div class="metric-footer" style="color: #f59e0b;">AWAITING HOD APPROVAL</div>
        </div>
    </div>

    <div class="dashboard-lower-grid">
        <div class="activity-log glass-card animate-slide-up" style="--delay: 0.5s">
            <h3 class="card-title"><span class="cyan-text">◈</span> RECENT PERSONNEL DEPLOYMENTS</h3>
            <div class="log-entries">
                <?php foreach($recent_staff as $rs): ?>
                    <div class="log-entry">
                        <div class="entry-info">
                            <span class="entry-name"><?php echo htmlspecialchars($rs['full_name']); ?></span>
                            <span class="entry-role"><?php echo htmlspecialchars($rs['designation']); ?></span>
                        </div>
                        <span class="entry-date"><?php echo date('M d, Y', strtotime($rs['joined_date'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="view-all-btn" onclick="window.location.href='layout.php?page=staff'">ACCESS FULL ARCHIVE</button>
        </div>

        <div class="quick-actions glass-card animate-slide-up" style="--delay: 0.6s">
            <h3 class="card-title"><span class="cyan-text">◈</span> CORE OPERATIONS</h3>
            <div class="action-grid">
                <div class="action-node" onclick="window.location.href='layout.php?page=staff_provision'">
                    <span class="node-icon">👤</span>
                    <span class="node-text">PROVISION STAFF</span>
                </div>
                <div class="action-node" onclick="window.location.href='layout.php?page=departments'">
                    <span class="node-icon">🏢</span>
                    <span class="node-text">EXPAND INFRA</span>
                </div>
                <div class="action-node" onclick="window.location.href='layout.php?page=student_registry'">
                    <span class="node-icon">🎓</span>
                    <span class="node-text">STUDENT HUB</span>
                </div>
                <div class="action-node" onclick="window.location.href='layout.php?page=enrollment_queue'">
                    <span class="node-icon">📋</span>
                    <span class="node-text">PENDING QUEUE</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root { --accent: #00f2ff; --bg-glass: rgba(15, 23, 42, 0.8); --border: rgba(255, 255, 255, 0.08); }

.command-center { padding: 10px; }
.glow-text { text-shadow: 0 0 15px rgba(0, 242, 255, 0.4); color: #fff; font-size: 1.8rem; font-weight: 800; }
.cyan-text { color: var(--accent); }
.system-status { font-family: 'JetBrains Mono'; font-size: 0.65rem; color: #64748b; margin-top: 5px; }

/* METRICS - Added Responsive minmax */
.metrics-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
    gap: 20px; 
    margin: 30px 0; 
}
.metric-card { 
    background: var(--bg-glass); 
    border: 1px solid var(--border); 
    padding: 25px; 
    border-radius: 12px; 
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.metric-card:hover { 
    border-color: var(--accent); 
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 242, 255, 0.1);
}
.metric-card::before { content: ""; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--accent); opacity: 0.3; }

.pending-alert { border-color: rgba(245, 158, 11, 0.3); }
.pending-alert::before { background: #f59e0b; }

.metric-label { font-size: 0.6rem; color: #94a3b8; letter-spacing: 2px; font-weight: 800; }
.metric-value { font-size: 2.5rem; color: #fff; font-weight: 900; margin: 10px 0; font-family: 'JetBrains Mono'; }
.metric-footer { font-size: 0.6rem; color: var(--accent); opacity: 0.7; }

/* LOWER GRID - Responsive stack */
.dashboard-lower-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; }
@media (max-width: 1024px) {
    .dashboard-lower-grid { grid-template-columns: 1fr; }
}

.glass-card { background: var(--bg-glass); border: 1px solid var(--border); padding: 25px; border-radius: 12px; backdrop-filter: blur(10px); }
.card-title { font-size: 0.75rem; color: #fff; letter-spacing: 1.5px; margin-bottom: 20px; font-weight: 800; }

.log-entry { 
    display: flex; justify-content: space-between; align-items: center; 
    padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.03); 
}
.entry-name { display: block; color: #f1f5f9; font-size: 0.85rem; font-weight: 600; }
.entry-role { color: var(--accent); font-size: 0.65rem; font-family: 'JetBrains Mono'; }
.entry-date { color: #64748b; font-size: 0.7rem; }

/* QUICK ACTIONS */
.action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.action-node { 
    background: rgba(0,0,0,0.2); border: 1px solid var(--border); 
    padding: 20px; border-radius: 8px; text-align: center; cursor: pointer; transition: 0.3s;
}
.action-node:hover { background: rgba(0, 242, 255, 0.05); border-color: var(--accent); transform: scale(1.02); }
.node-icon { font-size: 1.5rem; display: block; margin-bottom: 10px; }
.node-text { font-size: 0.65rem; color: #94a3b8; font-weight: 800; }

.view-all-btn { 
    width: 100%; background: transparent; border: 1px solid var(--accent); 
    color: var(--accent); padding: 10px; margin-top: 20px; border-radius: 4px; 
    cursor: pointer; font-size: 0.7rem; font-weight: 800; transition: 0.3s;
}
.view-all-btn:hover { background: var(--accent); color: #000; }

/* ANIMATIONS */
.animate-slide-up { opacity: 0; animation: slideUp 0.6s ease-out forwards; animation-delay: var(--delay); }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.pulse-text { animation: pulse 2s infinite; color: #10b981; }
@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
</style>