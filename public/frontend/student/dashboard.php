<?php
/**
 * EDULYNTRIX CORE X - STUDENT NEXUS HUB
 * Location: public/frontend/student/dashboard.php
 */
session_start();
require_once '../../../includes/db_connect.php';

// 1. Auth Guard
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

try {
    // 2. Sync Student Data
    $stmt = $pdo->prepare("SELECT s.*, d.dept_name FROM students s 
                           LEFT JOIN departments d ON s.dept_id = d.id 
                           WHERE s.student_id = :sid");
    $stmt->bindValue(':sid', (string)$_SESSION['student_id'], PDO::PARAM_STR);
    $stmt->execute();
    $student = $stmt->fetch();

    if (!$student) {
        session_destroy();
        header("Location: student_login.php");
        exit();
    }

    $student_name = $student['full_name'];
    $student_id   = $student['student_id'];
    $dept_label   = $student['dept_name'] ?? 'General Department';
    
    // Profile pic path logic
    $profile_pic = !empty($student['profile_pic']) ? $student['profile_pic'] : 'default.png';
    $avatar_url = "../../../uploads/profiles/" . $profile_pic;

    // Get current 3-letter day abbr for the initial load (e.g., Thu)
    $current_day_abbr = date('D'); 

} catch (PDOException $e) {
    error_log("Critical Error: " . $e->getMessage());
    die("Nexus System Offline.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Student Hub | <?= htmlspecialchars($student_name) ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/student_dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body onload="document.body.classList.add('loaded'); loadModule('schedule&day=<?= $current_day_abbr ?>', document.getElementById('nav-schedule'));">

    <div class="mesh-bg"></div>
    <div class="bg-branding">EDULYNTRIX<span>COREX</span></div>

    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand-wrapper">
                <div class="logo-square">SX</div>
                <div class="brand-text">CORE<b>X</b></div>
            </div>

            <nav class="nav-stack">
                <div class="nav-group-title">ACADEMICS</div>
                <button class="nav-link" id="nav-overview" onclick="loadModule('overview', this)">
                    <i class="fa-solid fa-chart-line"></i> My Progress
                </button>
                <button class="nav-link" id="nav-attendance" onclick="loadModule('attendance', this)">
                    <i class="fa-solid fa-calendar-check"></i> Attendance Audit
                </button>
                <button class="nav-link active" id="nav-schedule" onclick="loadModule('schedule&day=<?= $current_day_abbr ?>', this)">
                    <i class="fa-solid fa-clock"></i> Lecture Schedule
                </button>

                <div class="nav-group-title">SELF SERVICE</div>
                <button class="nav-link" onclick="loadModule('leave', this)">
                    <i class="fa-solid fa-paper-plane"></i> Leave Requests
                </button>
                <button class="nav-link" onclick="loadModule('fees', this)">
                    <i class="fa-solid fa-wallet"></i> Finance Monitor
                </button>

                <div class="nav-group-title">GOVERNANCE</div>
                <button class="nav-link" onclick="loadModule('profile', this)">
                    <i class="fa-solid fa-user-gear"></i> Profile Access
                </button>
                <button class="nav-link" onclick="loadModule('reports', this)">
                    <i class="fa-solid fa-file-invoice"></i> Academic Reports
                </button> 
            </nav>

            <div class="sidebar-footer">
                <button class="logout-trigger" onclick="terminateSession()">
                    <span class="trigger-icon"><i class="fa-solid fa-power-off"></i></span>
                    <span class="trigger-text">TERMINATE SESSION</span>
                </button>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-nav">
                <div class="search-bar-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="globalSearch" placeholder="Search academic records..." onkeyup="handleSearch()">
                </div>
                
                <div class="header-right-group">
                    <div class="clock-display-top">
                        <span id="liveDate">Loading Date...</span>
                        <h2 id="liveTime" style="font-family: 'JetBrains Mono', monospace;">00:00:00</h2>
                    </div>

                    <div class="notif-bell glass-effect" onclick="alert('No new notifications')" style="margin: 0 15px; cursor:pointer; position:relative;">
                        <i class="fa-regular fa-bell"></i>
                        <span style="position:absolute; top:-2px; right:-2px; background:var(--nexus-blue); width:8px; height:8px; border-radius:50%; border:2px solid #fff;"></span>
                    </div>

                    <div class="student-profile-node">
                        <div class="student-info">
                            <p id="greetingText">Welcome</p>
                            <small><?= htmlspecialchars($student_id) ?></small>
                        </div>
                        <div class="avatar-box">
                            <img id="headerAvatar" src="<?= $avatar_url ?>" alt="User" onerror="this.src='../../../uploads/profiles/default.png'">
                            <div class="active-status-dot"></div>
                        </div>
                    </div>
                </div>
            </header>

            <section class="content-canvas" id="mainContent">
                <div class="loader-placeholder">
                    <i class="fa-solid fa-spinner fa-spin"></i> Initializing Secure Node...
                </div>
            </section>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/student_logic.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        /**
         * REAL-TIME SYSTEM CLOCK
         */
        function updateNexusSystem() {
            const timeEl = document.getElementById('liveTime');
            const dateEl = document.getElementById('liveDate');
            const greetEl = document.getElementById('greetingText');
            const now = new Date();
            
            if(timeEl) timeEl.innerText = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            if(dateEl) dateEl.innerText = now.toLocaleDateString('en-US', { weekday: 'long', day: '2-digit', month: 'short' });
            if(greetEl) {
                const hour = now.getHours();
                const sName = "<?= explode(' ', $student_name)[0] ?>";
                greetEl.innerText = (hour < 12 ? "Good Morning, " : hour < 17 ? "Good Afternoon, " : "Good Evening, ") + sName;
            }
        }
        setInterval(updateNexusSystem, 1000);
        updateNexusSystem();

        /**
         * AJAX MODULE LOADER - FIXED FOR 404
         */
        function loadModule(moduleName, element) {
            if(element) {
                document.querySelectorAll('.nav-link').forEach(btn => btn.classList.remove('active'));
                element.classList.add('active');
            }
            
            const content = document.getElementById('mainContent');
            content.style.opacity = '0.5';
            
            // --- FIX: Logic to separate parameters from the filename ---
            let parts = moduleName.split('&');
            let file = parts[0]; // e.g., "schedule"
            let params = parts.length > 1 ? '?' + parts.slice(1).join('&') : ''; // e.g., "?day=Thu"
            
            // Final URL path: modules/schedule.php?day=Thu
            let targetPath = 'modules/' + file + '.php' + params;

            $('#mainContent').load(targetPath, function(response, status, xhr) {
                content.style.opacity = '1';
                if (status == "error") {
                    content.innerHTML = `
                        <div style="padding:40px; text-align:center; background:rgba(239, 68, 68, 0.1); border-radius:20px; border:1px solid rgba(239, 68, 68, 0.2);">
                            <i class="fa-solid fa-circle-exclamation" style="font-size:2rem; color:#ef4444;"></i>
                            <h3 style="margin-top:15px; color:#fff;">Sync Error: ${xhr.status}</h3>
                            <p style="color:#94a3b8; font-size:0.8rem;">Path Not Found: ${targetPath}</p>
                        </div>`;
                }
            });
        }

        function terminateSession() {
            if(confirm("Terminate secure connection?")) {
                document.body.style.opacity = '0';
                setTimeout(() => { window.location.href = '../../../includes/logout.php'; }, 400);
            }
        }
    </script>
</body>
</html>