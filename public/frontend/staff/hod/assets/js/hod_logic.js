/**
 * EDULYNTRIX CORE X - HOD LOGIC ENGINE
 * Version 6.8: Supreme Power Sync & Serial Re-indexing
 */

const API_ROOT = '/EdulyntrixCoreX/api'; 
let isProcessing = false;

document.addEventListener('DOMContentLoaded', () => {
    runClock();
    setInterval(runClock, 1000);

    // Initial Search Listener
    const searchBar = document.getElementById('globalSearch');
    if (searchBar) searchBar.addEventListener('input', handleSearch);
    
    // Initial Stat Sync
    updateExecutiveStats();
});

/** --- 1. CLOCK ENGINE --- **/
function runClock() {
    const timeNode = document.getElementById('liveTime');
    const dateNode = document.getElementById('liveDate');
    if(!timeNode || !dateNode) return;

    const now = new Date();
    timeNode.innerText = now.toLocaleTimeString('en-GB', { hour12: false });
    dateNode.innerText = now.toLocaleDateString('en-GB', { 
        weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' 
    }).toUpperCase();
}

/** --- 2. SUPREME MODULE LOADER (AJAX) --- **/
function loadModule(module, btn) {
    if(!btn) return;

    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    btn.classList.add('active');

    const mainContent = document.getElementById('mainContent');
    mainContent.innerHTML = `
        <div class="fade-in-up" style="color: var(--emerald); font-family: 'JetBrains Mono'; padding: 40px;">
            <i class="fa-solid fa-sync fa-spin"></i> [ ACCESSING NODE: ${module.toUpperCase()} ]<br>
            <span style="font-size: 0.7rem; opacity: 0.5;">Establishing secure handshake...</span>
        </div>`;

    fetch(`modules/${module}.php`)
        .then(response => {
            if (!response.ok) throw new Error('Node Offline');
            return response.text();
        })
        .then(html => {
            mainContent.innerHTML = `<div class="fade-in-up">${html}</div>`;
            if(module === 'enrollment') fetchEnrollmentData();
            if(module === 'leaves') fetchLeaveData();
            updateExecutiveStats();
        })
        .catch(err => {
            mainContent.innerHTML = `<div class="system-log-alert" style="color: var(--danger); border-color: var(--danger);">CRITICAL ERROR: Node ${module} unreachable.</div>`;
        });
}

/** --- 3. ENROLLMENT REGISTRY --- **/
async function fetchEnrollmentData() {
    const container = document.getElementById('enrollmentData');
    if (!container) return;

    try {
        const response = await fetch(`${API_ROOT}/fetch_enrollment.php`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map((student, index) => `
                <tr id="row-${student.id}" class="data-row fade-in-up" style="animation-delay: ${index * 0.05}s">
                    <td class="serial-num" style="color:var(--emerald); font-weight:800;">${(index + 1).toString().padStart(2, '0')}</td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar-mini">${student.student_name.charAt(0)}</div>
                            <div class="user-info">
                                <span class="u-name">${student.student_name}</span>
                                <span class="u-id">${student.student_id}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="branch-tag">${student.branch.toUpperCase()}</span></td>
                    <td style="text-align:right">
                        <button class="approve-btn" onclick="processEnrollment(${student.id}, 'approve')">VERIFY</button>
                        <button class="reject-btn" style="margin-left:5px;" onclick="processEnrollment(${student.id}, 'reject')">REJECT</button>
                    </td>
                </tr>
            `).join('');
            updateExecutiveStats(result.data.length);
        } else {
            container.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:60px; color:var(--text-muted);">Registry Clear.</td></tr>`;
            updateExecutiveStats(0);
        }
    } catch (e) { container.innerHTML = `<tr><td colspan="4" style="color:var(--danger); text-align:center;">Handshake Lost.</td></tr>`; }
}

async function processEnrollment(id, action) {
    if(isProcessing) return;
    if(!confirm(`EXECUTE ${action.toUpperCase()} PROTOCOL?`)) return;

    isProcessing = true;
    try {
        const response = await fetch(`${API_ROOT}/process_enrollment.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, action: action })
        });
        const result = await response.json();

        if(result.success) {
            const row = document.getElementById(`row-${id}`);
            row.style.transform = "translateX(50px) scale(0.95)";
            row.style.opacity = "0";
            setTimeout(() => {
                row.remove();
                reIndexTable('enrollmentData');
                updateExecutiveStats();
                isProcessing = false;
            }, 400);
        }
    } catch (err) { isProcessing = false; }
}

/** --- 4. LEAVE AUTHORIZATION --- **/
async function fetchLeaveData() {
    const grid = document.getElementById('leaveGrid');
    if (!grid) return;

    try {
        const response = await fetch(`${API_ROOT}/fetch_leaves.php`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            grid.innerHTML = result.data.map((leave, index) => `
                <div class="leave-card fade-in-up" id="leave-${leave.request_id}">
                    <div class="card-badge">#${(index + 1).toString().padStart(2, '0')} | ${leave.leave_type}</div>
                    <h3 style="margin-top:15px;">${leave.full_name}</h3>
                    <div class="mono-id" style="font-size:0.7rem; margin-bottom:15px; color:var(--accent);">${leave.student_id}</div>
                    <div class="leave-dates">
                        <span>${leave.start_date}</span> ➔ <span>${leave.end_date}</span>
                    </div>
                    <div class="card-actions">
                        <button class="approve-btn" onclick="updateLeave(${leave.request_id}, 'Approved')">AUTHORIZE</button>
                        <button class="reject-btn" onclick="updateLeave(${leave.request_id}, 'Rejected')">DECLINE</button>
                    </div>
                </div>
            `).join('');
            updateExecutiveStats(result.data.length);
        } else {
            grid.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text-muted);">No pending requests.</div>`;
            updateExecutiveStats(0);
        }
    } catch (e) { grid.innerHTML = `Sync Error.`; }
}

/** --- 5. UTILITIES & STATS --- **/
function updateExecutiveStats(count = null) {
    const statDisplay = document.querySelector('.stat-value');
    if (!statDisplay) return;

    if (count !== null) {
        statDisplay.innerText = count.toString().padStart(2, '0');
    } else {
        // Auto-detect based on current view
        const items = document.querySelectorAll('.data-row, .leave-card').length;
        statDisplay.innerText = items.toString().padStart(2, '0');
    }
}

function reIndexTable(containerId) {
    const rows = document.querySelectorAll(`#${containerId} tr`);
    rows.forEach((row, i) => {
        const serialCell = row.querySelector('.serial-num');
        if(serialCell) serialCell.innerText = (i + 1).toString().padStart(2, '0');
    });
}

function handleSearch() {
    const q = document.getElementById('globalSearch').value.toLowerCase();
    const items = document.querySelectorAll('.data-row, .leave-card');
    items.forEach(item => {
        item.style.display = item.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}

function confirmTermination() {
    if(confirm("SUPREME PROTOCOL: Terminate Executive Session?")) {
        window.location.href = '../../../../includes/logout.php';
    }
}