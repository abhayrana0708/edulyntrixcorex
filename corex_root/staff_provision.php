<?php
require_once('../includes/db_connect.php');

/**
 * EDULYNTRIX CORE X - PERSONNEL PROVISIONING
 * Style: Supreme Power (Interactive Glass)
 * Rule: Single HOD Enforcement Integrated
 */

try {
    $dept_stmt = $pdo->query("SELECT id, dept_name FROM departments WHERE status = 'Active' ORDER BY dept_name ASC");
    $active_depts = $dept_stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div class='mgmt-card' style='color:#f87171;'>Infrastructure Link Failure: " . $e->getMessage() . "</div>";
}
?>

<div class="supreme-provision-container">
    <div class="provision-header">
        <div class="header-text">
            <h2>Personnel <span class="cyan-text">Provisioning</span></h2>
            <p class="subtitle">AUTHORIZE NEW ACADEMIC IDENTITIES // ENFORCING SINGLE HOD PROTOCOL</p>
        </div>
        <div class="status-badge">
            <span class="badge-label">Governance Mode</span>
            <span class="badge-value">1_DEPT_1_HOD</span>
        </div>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'hod_conflict'): ?>
        <div class="error-alert">
            <span class="alert-icon">⚠️</span>
            <div class="alert-text">
                <strong>GOVERNANCE CONFLICT:</strong>
                The selected Department Node already has an assigned HOD. Only one HOD is permitted per department.
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'email_conflict'): ?>
        <div class="error-alert">
            <span class="alert-icon">⚠️</span>
            <div class="alert-text">
                <strong>IDENTITY CONFLICT:</strong>
                This email address is already registered in the system. Use a unique institutional email.
            </div>
        </div>
    <?php endif; ?>

    <form action="staff_manager.php" method="POST" class="provision-form" id="provisionForm">
        <input type="hidden" name="action"          value="add">
        <!-- FIX 1: role hidden field — synced by JS from designation -->
        <input type="hidden" name="role"            id="roleField"     value="faculty">
        <!-- FIX 2: department name string — synced by JS from dept select -->
        <input type="hidden" name="department_name" id="deptNameField" value="">

        <div class="form-grid">
            <div class="form-group animate-focus">
                <label class="clean-label">Full Legal Name</label>
                <input type="text" name="full_name" placeholder="Ex: Sandeep Kumar"
                       required class="clean-input" maxlength="100">
            </div>
            <div class="form-group animate-focus">
                <label class="clean-label">Institutional Email (Network ID)</label>
                <input type="email" name="email" placeholder="s.kumar@edulyntrix.com"
                       required class="clean-input">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group animate-focus">
                <label class="clean-label">Institutional Role</label>
                <select name="designation" id="roleSelector" class="clean-input"
                        required onchange="checkHODRole()">
                    <option value="" disabled selected>Select Role...</option>
                    <option value="HOD" style="color:#fbbf24;font-weight:bold;">◈ Head of Department (HOD)</option>
                    <option value="Professor">Professor</option>
                    <option value="Assistant Professor">Assistant Professor</option>
                    <option value="Lecturer">Lecturer / Teacher</option>
                    <option value="Admin">Administrative Staff</option>
                </select>
                <small id="hodWarning" class="helper-text" style="color:#fbbf24;display:none;">
                    Note: System will verify department availability for HOD seat.
                </small>
            </div>
            <div class="form-group animate-focus">
                <label class="clean-label">Department Node</label>
                <!-- FIX 2: data-name carries the string; onchange syncs hidden field -->
                <select name="dept_id" id="deptSelector" class="clean-input"
                        required onchange="syncDeptName()">
                    <option value="" disabled selected>Link to Infrastructure...</option>
                    <?php foreach ($active_depts as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"
                                data-name="<?php echo htmlspecialchars($dept['dept_name']); ?>">
                            <?php echo htmlspecialchars($dept['dept_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="security-divider">
            <div class="form-grid">
                <div class="form-group animate-focus">
                    <label class="clean-label">Mobile Number Registry</label>
                    <input type="tel" name="phone" placeholder="+91 XXXXX XXXXX" class="clean-input">
                </div>
                <div class="form-group animate-focus">
                    <label class="clean-label">Authorized Joined Date</label>
                    <input type="date" name="joined_date" value="<?php echo date('Y-m-d'); ?>"
                           required class="clean-input" style="color-scheme:dark;">
                </div>
            </div>

            <div class="form-grid" style="margin-top:25px;">
                <div class="form-group animate-focus">
                    <label class="clean-label">Dedicated System Password</label>
                    <input type="password" name="password" placeholder="Assign Secure Password"
                           required class="clean-input">
                    <small class="helper-text">System ID will follow the sequence: STF-2026-XXX</small>
                </div>
            </div>
        </div>

        <div class="action-footer">
            <button type="submit" class="prime-btn glow-trigger">Authorize Identity</button>
            <button type="button" class="prime-btn alt-btn"
                    onclick="window.location.href='layout.php?page=staff'">Return to Directory</button>
        </div>
    </form>
</div>

<style>
:root { --accent:#00f2ff; --border:rgba(255,255,255,0.1); --bg-glass:rgba(15,23,42,0.8); }

.supreme-provision-container {
    background:var(--bg-glass); backdrop-filter:blur(20px); border:1px solid var(--border);
    padding:35px; border-radius:12px; animation:fadeIn 0.5s ease-out;
}
.error-alert {
    background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.3);
    padding:15px; border-radius:8px; margin-bottom:25px; display:flex; align-items:center; gap:15px;
}
.alert-icon { font-size:1.2rem; }
.alert-text  { font-size:0.8rem; color:#fecaca; }

.provision-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:35px; }
.cyan-text { color:var(--accent); text-shadow:0 0 10px rgba(0,242,255,0.3); }
.subtitle  { color:#64748b; font-size:0.7rem; letter-spacing:1px; margin-top:5px; font-family:'JetBrains Mono'; }

.status-badge {
    background:rgba(0,242,255,0.05); padding:10px 20px; border-radius:8px;
    border:1px solid rgba(0,242,255,0.2); text-align:right;
}
.badge-label { font-size:0.6rem; color:var(--accent); font-weight:800; display:block; text-transform:uppercase; }
.badge-value { font-family:'JetBrains Mono'; font-size:0.85rem; color:#fff; }

.form-grid  { display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-bottom:25px; }
.clean-label{ font-size:0.75rem; color:#94a3b8; margin-bottom:8px; display:block; font-weight:600; }
.clean-input{
    width:100%; background:rgba(0,0,0,0.2); border:1px solid var(--border);
    padding:12px 15px; color:#fff; border-radius:6px; transition:all 0.3s ease;
}
.clean-input:focus { border-color:var(--accent); background:rgba(0,242,255,0.05); outline:none; }

.security-divider { margin-top:35px; padding-top:35px; border-top:1px solid var(--border); }

.prime-btn {
    background:var(--accent); color:#000; border:none; padding:15px 35px;
    font-weight:800; border-radius:6px; cursor:pointer; transition:all 0.4s; font-size:0.85rem;
}
.prime-btn:hover { transform:scale(1.02); box-shadow:0 0 25px rgba(0,242,255,0.4); }
.alt-btn {
    background:transparent !important; border:1px solid var(--border) !important;
    color:#94a3b8 !important; margin-left:10px;
}
.helper-text { color:#64748b; font-size:0.7rem; margin-top:8px; display:block; }

@keyframes fadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
</style>

<script>
/**
 * EDULYNTRIX CORE X – INTERFACE LOGIC
 * FIX 5: IIFE so script works both on full page load AND when injected via fetch()/AJAX
 */
(function () {

    // ── FIX 1: HOD role warning + sync hidden role field ─────
    window.checkHODRole = function () {
        const designation = document.getElementById('roleSelector').value;
        const warning     = document.getElementById('hodWarning');
        const roleField   = document.getElementById('roleField');

        // Map designation → DB role value
        roleField.value = (designation === 'HOD') ? 'hod' : 'faculty';

        if (designation === 'HOD') {
            warning.style.display   = 'block';
            warning.style.animation = 'fadeIn 0.3s ease';
        } else {
            warning.style.display = 'none';
        }
    };

    // ── FIX 2: Keep department name hidden field in sync ─────
    window.syncDeptName = function () {
        const sel = document.getElementById('deptSelector');
        const opt = sel.options[sel.selectedIndex];
        document.getElementById('deptNameField').value =
            opt ? (opt.dataset.name || opt.text) : '';
    };

    // ── Animate-Focus: group lifts on focus ──────────────────
    document.querySelectorAll('.animate-focus .clean-input').forEach(input => {
        input.addEventListener('focus', () => {
            input.closest('.animate-focus').style.transform  = 'translateY(-2px)';
            input.closest('.animate-focus').style.transition = 'transform 0.25s ease';
        });
        input.addEventListener('blur', () => {
            input.closest('.animate-focus').style.transform = 'translateY(0)';
        });
    });

    // ── Real-time Email Validation ────────────────────────────
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
            this.style.borderColor = this.value.length === 0
                ? 'rgba(255,255,255,0.1)'
                : valid ? '#22c55e' : '#f87171';
        });
    }

    // ── Phone Auto-Format (+91 XXXXX XXXXX) ──────────────────
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            let d = this.value.replace(/\D/g, '');
            if (d.startsWith('91')) d = d.slice(2);
            d = d.slice(0, 10);
            this.value = d.length > 5
                ? '+91 ' + d.slice(0, 5) + ' ' + d.slice(5)
                : d.length > 0 ? '+91 ' + d : '';
        });
    }

    // ── Password Strength Bar + Eye Toggle ───────────────────
    const passInput = document.querySelector('input[name="password"]');
    if (passInput) {
        const helperText = passInput.nextElementSibling;

        // 4-segment strength bar
        const barWrapper = document.createElement('div');
        barWrapper.style.cssText = 'display:flex;gap:4px;margin-top:8px;';
        for (let i = 0; i < 4; i++) {
            const seg = document.createElement('div');
            seg.className = 'pwr-seg';
            seg.style.cssText =
                'height:3px;flex:1;border-radius:2px;' +
                'background:rgba(255,255,255,0.1);transition:background 0.3s;';
            barWrapper.appendChild(seg);
        }

        // Strength label
        const strengthLabel = document.createElement('small');
        strengthLabel.style.cssText =
            'font-size:0.65rem;color:#64748b;margin-top:4px;' +
            'display:block;font-family:"JetBrains Mono";';

        // Eye toggle
        const toggleBtn = document.createElement('span');
        toggleBtn.textContent = '👁';
        toggleBtn.title = 'Toggle visibility';
        toggleBtn.style.cssText =
            'position:absolute;right:14px;top:38px;cursor:pointer;' +
            'font-size:0.9rem;opacity:0.5;transition:opacity 0.2s;user-select:none;';
        toggleBtn.addEventListener('mouseenter', () => toggleBtn.style.opacity = '1');
        toggleBtn.addEventListener('mouseleave', () => toggleBtn.style.opacity = '0.5');
        toggleBtn.addEventListener('click', () => {
            passInput.type = passInput.type === 'password' ? 'text' : 'password';
        });

        passInput.parentNode.style.position = 'relative';
        // Insert order: after helper → bar → label → toggle
        helperText.after(barWrapper);
        barWrapper.after(strengthLabel);
        passInput.after(toggleBtn);

        const labels = ['Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['#f87171', '#fbbf24', '#60a5fa', '#22c55e'];

        passInput.addEventListener('input', function () {
            const v = this.value;
            let score = 0;
            if (v.length >= 8)           score++;
            if (/[A-Z]/.test(v))         score++;
            if (/[0-9]/.test(v))         score++;
            if (/[^A-Za-z0-9]/.test(v))  score++;

            barWrapper.querySelectorAll('.pwr-seg').forEach((seg, i) => {
                seg.style.background =
                    i < score ? colors[score - 1] : 'rgba(255,255,255,0.1)';
            });
            strengthLabel.textContent = v.length === 0 ? '' : (labels[score - 1] || 'Weak');
            strengthLabel.style.color = v.length === 0 ? '#64748b' : colors[score - 1];
        });
    }

    // ── Character Counter for Full Name ──────────────────────
    const nameInput = document.querySelector('input[name="full_name"]');
    if (nameInput) {
        const counter = document.createElement('small');
        counter.style.cssText =
            'font-size:0.65rem;color:#64748b;margin-top:4px;' +
            'display:block;text-align:right;font-family:"JetBrains Mono";';
        counter.textContent = '0 / 100';
        nameInput.parentNode.appendChild(counter);
        nameInput.addEventListener('input', function () {
            const len = this.value.length;
            counter.textContent = `${len} / 100`;
            counter.style.color = len > 80 ? '#fbbf24' : '#64748b';
        });
    }

    // ── Submit: validate → confirm → loading state ────────────
    const provisionForm = document.getElementById('provisionForm');
    if (provisionForm) {
        provisionForm.addEventListener('submit', function (e) {

            // Always sync hidden fields right before submit
            checkHODRole();
            syncDeptName();

            const roleVal  = document.getElementById('roleSelector').value;
            const deptVal  = document.getElementById('deptSelector').value;
            const name     = provisionForm.querySelector('input[name="full_name"]').value.trim();
            const deptSel  = document.getElementById('deptSelector');
            const deptName = deptSel.options[deptSel.selectedIndex]?.dataset.name || '—';

            // Client-side guard: catch empty selects before PHP sees them
            if (!roleVal) {
                e.preventDefault();
                alert('Please select an Institutional Role before submitting.');
                return;
            }
            if (!deptVal) {
                e.preventDefault();
                alert('Please select a Department Node before submitting.');
                return;
            }

            const confirmed = confirm(
                `Authorize Identity?\n\nName  :  ${name}\nRole   :  ${roleVal}\nDept  :  ${deptName}\n\nThis will register the personnel in the system.`
            );
            if (!confirmed) { e.preventDefault(); return; }

            // Loading state
            const btn        = provisionForm.querySelector('button[type="submit"]');
            btn.textContent  = 'Authorizing...';
            btn.disabled     = true;
            btn.style.opacity = '0.7';
            btn.style.cursor  = 'not-allowed';
        });
    }

})(); // end IIFE — safe for both full-page load and fetch() injection
</script>