<?php
/**
 * EDULYNTRIX CORE X - UNIFIED GATEWAY
 * Version 2.0.4: Optimized Authority Routing
 * Path: /public/frontend/index.php
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. SUPREME REDIRECT: Bypass landing if session is active
if (isset($_SESSION['student_id']) || isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';
    
    // Check if the user is an HOD/Admin or a Student
    if ($role === 'hod' || $role === 'admin') {
        header("Location: staff/hod/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EdulyntrixCoreX | Unified Management System</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="common/css/home.css">
    <link rel="stylesheet" href="common/css/main.css">

    <style>
        /* Fallback Emergency Styles for Mesh Background */
        :root { --emerald: #10b981; --royal-blue: #3b82f6; }
        .admin-glow { filter: drop-shadow(0 0 15px rgba(16, 185, 129, 0.4)); }
        .student-glow { filter: drop-shadow(0 0 15px rgba(59, 130, 246, 0.4)); }
        .authority-tag { font-size: 0.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: var(--emerald); }
        .student-tag { font-size: 0.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: var(--royal-blue); }
    </style>
</head>
<body class="gateway-theme">

    <div class="mesh-bg"></div>
    <div class="bg-branding">Edulyntrix<span>CoreX</span></div>

    <main class="hero-container">
        <header data-reveal>
            <div class="status-badge">
                <div class="logo-wrapper">
                    <svg class="brand-logo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    <span style="font-family: 'JetBrains Mono';">EDULYNTRIX-COREX</span>
                </div>
            </div>
            <h1 class="main-title">Unified <span>Institutional</span> Intelligence.</h1>
            <p class="tagline">The professional bridge between administrative excellence and academic success.</p>
        </header>

        <div class="portal-grid" data-reveal>
            
            <a href="staff/login.php" class="portal-box staff-theme parallax">
                <div class="portal-visual staff-glow">
                    <i class="fa-solid fa-shield-halved fa-2xl" style="color: var(--emerald);"></i>
                </div>
                <div class="box-content">
                    <span class="authority-tag">Management Node</span>
                    <h3>Staff <b>Portal</b></h3>
                    <p>Faculty management, analytics, and departmental oversight.</p>
                    <span class="entry-link">Initialize Access →</span>
                </div>
            </a>

            <a href="student/student_login.php" class="portal-box student-theme parallax">
                <div class="portal-visual student-glow">
                    <i class="fa-solid fa-user-graduate fa-2xl" style="color: var(--royal-blue);"></i>
                </div>
                <div class="box-content">
                    <span class="student-tag">Academic Hub</span>
                    <h3>Student <b>Portal</b></h3>
                    <p>Access enrollment, course materials, and academic profiles.</p>
                    <span class="entry-link">Access Student Desk →</span>
                </div>
            </a>

        </div>
    </main>

    <section class="info-section">
        <div class="info-grid">
            <div class="info-item" data-reveal>
                <i class="fa-solid fa-microchip" style="color: var(--emerald); margin-bottom: 15px;"></i>
                <h4>PROTOCOL</h4>
                <p>Encrypted data transmission via secure institutional nodes.</p>
            </div>
            <div class="info-item" data-reveal>
                <i class="fa-solid fa-lock" style="color: var(--emerald); margin-bottom: 15px;"></i>
                <h4>SECURITY</h4>
                <p>AES-256 standard encryption for all sensitive user records.</p>
            </div>
            <div class="info-item" data-reveal>
                <i class="fa-solid fa-tower-broadcast" style="color: var(--emerald); margin-bottom: 15px;"></i>
                <h4>UPLINK</h4>
                <p>Real-time synchronization between HOD and Student registries.</p>
            </div>
        </div>
    </section>

    <footer class="footer-minimal">
        <div class="footer-left">
            &copy; 2026 Edulyntrix<span>CoreX</span>
        </div>
        <div class="footer-right">
            <a href="student/registration.php" style="color: var(--royal-blue); font-weight: 700;">Register Identity</a>
            <a href="#">Privacy Policy</a>
        </div>
    </footer>

    <script src="common/js/home.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Trigger visual reveal animations
            const elements = document.querySelectorAll('[data-reveal]');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.classList.add('active');
                }, index * 150);
            });
        });
    </script>
</body>
</html>