/**
 * EDULYNTRIX COREX - FULL HOD LOGIC ENGINE
 * Populated with Institutional Sample Data
 */

document.addEventListener('DOMContentLoaded', () => {
    updateClock();
    setInterval(updateClock, 1000);
    loadModule('enrollment');
});

function updateClock() {
    const now = new Date();
    document.getElementById('liveTime').innerText = now.toLocaleTimeString('en-US', { hour12: false });
    document.getElementById('liveDate').innerText = now.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }).toUpperCase();
}

function loadModule(type) {
    const stage = document.getElementById('mainContent');
    document.querySelectorAll('.nav-link').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`[onclick="loadModule('${type}')"]`);
    if (activeBtn) activeBtn.classList.add('active');

    switch(type) {
        case 'enrollment': renderEnrollment(stage); break;
        case 'leaves': renderLeaves(stage); break;
        case 'attendance': renderAttendance(stage); break;
        case 'faculty': renderFaculty(stage); break;
        case 'discipline': renderResolution(stage); break;
        case 'finance': renderFinance(stage); break;
        case 'reports': renderReports(stage); break;
        case 'access': renderAccess(stage); break;
    }
}

/** --- 1. MANAGEMENT: Enrollment Queue --- **/
function renderEnrollment(stage) {
    stage.innerHTML = `
        <h2>Enrollment <b>Queue</b></h2><p style="color:#64748b; margin-bottom:2rem;">Pending student verification.</p>
        <table class="clean-table">
            <thead><tr><th>Student</th><th>ID</th><th>Branch</th><th>Action</th></tr></thead>
            <tbody>
                <tr><td><b>Arjun Sharma</b></td><td>STU-2026-001</td><td>CS - AIML</td><td><button style="background:var(--emerald); border:none; color:white; padding:6px 15px; border-radius:8px; cursor:pointer;">Approve</button></td></tr>
                <tr><td><b>Sara Khan</b></td><td>STU-2026-042</td><td>CS - Core</td><td><button style="background:var(--emerald); border:none; color:white; padding:6px 15px; border-radius:8px; cursor:pointer;">Approve</button></td></tr>
            </tbody>
        </table>`;
}

/** --- 2. MANAGEMENT: Leave Requests --- **/
function renderLeaves(stage) {
    stage.innerHTML = `
        <h2>Leave <b>Requests</b></h2><p style="color:#64748b; margin-bottom:2rem;">Faculty and Student leave authorization.</p>
        <table class="clean-table">
            <thead><tr><th>Applicant</th><th>Type</th><th>Duration</th><th>Action</th></tr></thead>
            <tbody>
                <tr><td><b>Prof. Vikram Singh</b></td><td>Medical</td><td>3 Days</td><td><button style="background:var(--emerald); border:none; color:white; padding:6px 12px; border-radius:6px;">Authorize</button></td></tr>
                <tr><td><b>Rohan Verma (Student)</b></td><td>Personal</td><td>1 Day</td><td><button style="background:var(--emerald); border:none; color:white; padding:6px 12px; border-radius:6px;">Authorize</button></td></tr>
            </tbody>
        </table>`;
}

/** --- 3. MANAGEMENT: Attendance Audit --- **/
function renderAttendance(stage) {
    stage.innerHTML = `
        <h2>Attendance <b>Audit</b></h2><p style="color:#ef4444; font-weight:700; margin-bottom:2rem;">Alert: Students below 75% threshold.</p>
        <table class="clean-table">
            <thead><tr><th>Student</th><th>Current %</th><th>Shortage</th><th>Governance</th></tr></thead>
            <tbody>
                <tr><td><b>Rahul Kapoor</b></td><td>62.4%</td><td>12.6%</td><td><button style="color:#ef4444; border:1px solid #ef4444; background:none; padding:4px 10px; border-radius:6px;">Issue Warning</button></td></tr>
                <tr><td><b>Ishita Rao</b></td><td>71.0%</td><td>4.0%</td><td><button style="color:#ef4444; border:1px solid #ef4444; background:none; padding:4px 10px; border-radius:6px;">Send Mail</button></td></tr>
            </tbody>
        </table>`;
}

/** --- 4. ACADEMIC: Faculty Deployment --- **/
function renderFaculty(stage) {
    stage.innerHTML = `
        <h2>Faculty <b>Deployment</b></h2><p style="color:#64748b; margin-bottom:2rem;">Real-time classroom monitoring.</p>
        <table class="clean-table">
            <thead><tr><th>Faculty</th><th>Status</th><th>Subject</th><th>Room</th></tr></thead>
            <tbody>
                <tr><td><b>Dr. Ananya Rao</b></td><td><span style="color:var(--emerald); font-weight:800;">TEACHING</span></td><td>Algorithms</td><td>R-402</td></tr>
                <tr><td><b>Prof. Amit Jha</b></td><td><span style="color:#94a3b8;">OFF-DUTY</span></td><td>--</td><td>Staff Room</td></tr>
            </tbody>
        </table>`;
}

/** --- 5. ACADEMIC: Record Resolution --- **/
function renderResolution(stage) {
    stage.innerHTML = `
        <h2>Record <b>Resolution</b></h2><p style="color:#64748b; margin-bottom:2rem;">Correction of institutional data errors.</p>
        <table class="clean-table">
            <thead><tr><th>Student</th><th>Error Detail</th><th>Reported By</th><th>Action</th></tr></thead>
            <tbody>
                <tr><td><b>Nitin Das</b></td><td>Absent marked as Present</td><td>Self-Service</td><td><button style="background:var(--emerald); border:none; color:white; padding:6px 12px; border-radius:6px;">Override</button></td></tr>
                <tr><td><b>Priya M.</b></td><td>Mid-term marks mismatch</td><td>Prof. Mehta</td><td><button style="background:var(--emerald); border:none; color:white; padding:6px 12px; border-radius:6px;">Override</button></td></tr>
            </tbody>
        </table>`;
}

/** --- 6. GOVERNANCE: Finance Monitor --- **/
function renderFinance(stage) {
    stage.innerHTML = `
        <h2>Fee & Fine <b>Monitor</b></h2>
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:15px; margin:2rem 0;">
            <div style="background:var(--emerald-soft); padding:20px; border-radius:15px; border:1px solid var(--emerald);">
                <small style="color:var(--emerald); font-weight:800;">TOTAL PAID</small><h3>84%</h3>
            </div>
            <div style="background:rgba(251, 191, 36, 0.1); padding:20px; border-radius:15px; border:1px solid #fbbf24;">
                <small style="color:#fbbf24; font-weight:800;">PENDING</small><h3>16%</h3>
            </div>
            <div style="background:var(--red-soft); padding:20px; border-radius:15px; border:1px solid #ef4444;">
                <small style="color:#ef4444; font-weight:800;">FINES</small><h3>$4,200</h3>
            </div>
        </div>
        <table class="clean-table">
            <thead><tr><th>Student</th><th>Category</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                <tr><td><b>Kabir Singh</b></td><td>Exam Fee</td><td>$250</td><td><span style="color:#ef4444;">OVERDUE</span></td></tr>
                <tr><td><b>Sneha Pal</b></td><td>Tuition</td><td>$12,000</td><td><span style="color:var(--emerald);">PAID</span></td></tr>
            </tbody>
        </table>`;
}

/** --- 7. GOVERNANCE: Systems & Reports (Filter Logic) --- **/
function renderReports(stage) {
    stage.innerHTML = `
        <h2>Systems & <b>Reports</b></h2>
        <div style="display:flex; gap:15px; margin:2rem 0; background:rgba(255,255,255,0.03); padding:20px; border-radius:16px; border:1px solid var(--border-light);">
            <div style="flex:1;"><label style="font-size:0.7rem; color:var(--emerald); font-weight:800; display:block; margin-bottom:8px;">YEAR</label>
                <select id="yearSelect" onchange="syncSubjects()" style="width:100%; background:#020617; color:white; border:1px solid var(--border-light); padding:10px; border-radius:8px;"><option value="1">1st Year</option><option value="2">2nd Year</option><option value="3">3rd Year</option><option value="4">4th Year</option></select>
            </div>
            <div style="flex:2;"><label style="font-size:0.7rem; color:var(--emerald); font-weight:800; display:block; margin-bottom:8px;">SUBJECT</label>
                <select id="subjectSelect" style="width:100%; background:#020617; color:white; border:1px solid var(--border-light); padding:10px; border-radius:8px;"></select>
            </div>
            <button onclick="alert('Generating Departmental Report...')" style="align-self:flex-end; height:42px; background:var(--emerald); color:white; border:none; padding:0 25px; border-radius:8px; font-weight:700; cursor:pointer;">Generate</button>
        </div>
        <table class="clean-table">
            <thead><tr><th>Student Name</th><th>Roll No</th><th>Attendance</th><th>Performance</th></tr></thead>
            <tbody>
                <tr><td><b>Aman Gupta</b></td><td>CS2601</td><td>92%</td><td>Distinction</td></tr>
                <tr><td><b>Zoya Khan</b></td><td>CS2605</td><td>85%</td><td>First Class</td></tr>
            </tbody>
        </table>`;
    syncSubjects();
}

function syncSubjects() {
    const year = document.getElementById('yearSelect').value;
    const subDropdown = document.getElementById('subjectSelect');
    const mapping = {
        "1": ["Applied Physics", "Basic Electronics", "Engineering Math I"],
        "2": ["Data Structures", "Java Programming", "Discrete Mathematics"],
        "3": ["Operating Systems", "Computer Networks", "DBMS"],
        "4": ["Artificial Intelligence", "Information Security", "Machine Learning"]
    };
    subDropdown.innerHTML = mapping[year].map(s => `<option>${s}</option>`).join('');
}

/** --- 8. GOVERNANCE: Access Control --- **/
function renderAccess(stage) {
    stage.innerHTML = `
        <h2>Access <b>Control</b></h2><p style="color:#64748b; margin-bottom:2rem;">Management of system clearance levels.</p>
        <table class="clean-table">
            <thead><tr><th>User</th><th>Designation</th><th>Access Level</th><th>Action</th></tr></thead>
            <tbody>
                <tr><td><b>Dr. Sandeep Kumar</b></td><td>HOD</td><td>Level 4 (Full Admin)</td><td>--</td></tr>
                <tr><td><b>Prof. Vikram Singh</b></td><td>Asst. Prof</td><td>Level 2 (Academic)</td><td><button style="color:#ef4444; background:none; border:1px solid #ef4444; padding:4px 10px; border-radius:6px; cursor:pointer;">Revoke</button></td></tr>
            </tbody>
        </table>`;
}

function handleSearch() {
    const q = document.getElementById('globalSearch').value.toLowerCase();
    document.querySelectorAll('.clean-table tbody tr').forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}

function terminate() { if(confirm("Terminate Session?")) window.location.reload(); }