<?php
// Core logic to handle active state - Set default to 'dashboard'
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>EdulyntrixCoreX | Supreme Power Oversight</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <style>
    /* ═══════════════════════════════════════════════════════════
       EDULYNTRIX COREX — SUPREME POWER OVERSIGHT SHELL
       Dark cyberpunk administrative interface
    ═══════════════════════════════════════════════════════════ */

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --accent:       #00f2ff;
        --accent-dim:   rgba(0, 242, 255, 0.12);
        --accent-glow:  rgba(0, 242, 255, 0.35);
        --bg-base:      #060b14;
        --bg-surface:   #0c1524;
        --bg-elevated:  #111d30;
        --bg-glass:     rgba(12, 21, 36, 0.85);
        --border:       rgba(255, 255, 255, 0.07);
        --border-accent:rgba(0, 242, 255, 0.25);
        --text-primary: #e8f0fe;
        --text-muted:   #4a6180;
        --text-dim:     #2a3d55;
        --danger:       #f87171;
        --warning:      #fbbf24;
        --success:      #34d399;
        --sidebar-w:    270px;
        --topbar-h:     68px;
        --radius:       10px;
        --font-ui:      'Space Grotesk', sans-serif;
        --font-mono:    'JetBrains Mono', monospace;
        --transition:   all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    html, body { height: 100%; overflow: hidden; }

    body {
        font-family: var(--font-ui);
        background: var(--bg-base);
        color: var(--text-primary);
        -webkit-font-smoothing: antialiased;
    }

    /* ── Scrollbar ──────────────────────────────────────────── */
    ::-webkit-scrollbar { width: 4px; height: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--accent-dim); border-radius: 99px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--border-accent); }

    /* ══════════════════════════════════════════════════════════
       APP SHELL
    ══════════════════════════════════════════════════════════ */
    .app-container {
        display: flex;
        height: 100vh;
        overflow: hidden;
        position: relative;
    }

    /* ── Ambient background grid ─────────────────────────── */
    .app-container::before {
        content: '';
        position: fixed; inset: 0;
        background-image:
            linear-gradient(rgba(0,242,255,0.02) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0,242,255,0.02) 1px, transparent 1px);
        background-size: 48px 48px;
        pointer-events: none; z-index: 0;
    }

    /* ══════════════════════════════════════════════════════════
       SIDEBAR
    ══════════════════════════════════════════════════════════ */
    .sidebar {
        width: var(--sidebar-w);
        min-width: var(--sidebar-w);
        height: 100vh;
        background: var(--bg-surface);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        position: relative;
        z-index: 100;
        overflow: hidden;
        flex-shrink: 0;
    }

    /* Vertical accent line */
    .sidebar::after {
        content: '';
        position: absolute; top: 0; right: 0;
        width: 1px; height: 100%;
        background: linear-gradient(
            to bottom,
            transparent 0%,
            var(--accent) 30%,
            var(--accent) 70%,
            transparent 100%
        );
        opacity: 0.15;
    }

    /* ── Logo ──────────────────────────────────────────────── */
    .sidebar-header {
        padding: 24px 22px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        border-bottom: 1px solid var(--border);
        flex-shrink: 0;
        transition: var(--transition);
    }
    .sidebar-header:hover { background: var(--accent-dim); }

    .logo {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, var(--accent), #0070ff);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-family: var(--font-mono);
        font-weight: 700; font-size: 13px;
        color: #000;
        letter-spacing: 0.5px;
        box-shadow: 0 0 20px var(--accent-glow);
        flex-shrink: 0;
    }

    .logo-text {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: 0.3px;
        line-height: 1.2;
    }
    .logo-text span { color: var(--accent); }
    .logo-subtext {
        font-family: var(--font-mono);
        font-size: 9px;
        color: var(--text-muted);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 2px;
        display: block;
    }

    /* ── Nav ───────────────────────────────────────────────── */
    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 16px 12px 20px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .section-tag {
        font-family: var(--font-mono);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 2.5px;
        color: var(--text-dim);
        text-transform: uppercase;
        padding: 14px 10px 6px;
        margin-top: 4px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 12px;
        border-radius: 8px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 500;
        transition: var(--transition);
        position: relative;
        white-space: nowrap;
        overflow: hidden;
    }

    .nav-link .nav-icon {
        font-size: 15px;
        width: 20px;
        text-align: center;
        flex-shrink: 0;
        opacity: 0.7;
        transition: var(--transition);
    }

    .nav-link:hover {
        color: var(--text-primary);
        background: var(--accent-dim);
    }
    .nav-link:hover .nav-icon { opacity: 1; }

    .nav-link.active {
        color: var(--accent);
        background: var(--accent-dim);
        font-weight: 600;
    }
    .nav-link.active .nav-icon { opacity: 1; color: var(--accent); }

    /* Active left bar */
    .nav-link.active::before {
        content: '';
        position: absolute; left: 0; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 60%;
        background: var(--accent);
        border-radius: 0 3px 3px 0;
        box-shadow: 0 0 8px var(--accent);
    }

    /* ── Sidebar footer ────────────────────────────────────── */
    .sidebar-footer {
        padding: 14px 14px;
        border-top: 1px solid var(--border);
        flex-shrink: 0;
    }

    .sys-status-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        background: rgba(52, 211, 153, 0.06);
        border: 1px solid rgba(52, 211, 153, 0.15);
        border-radius: 7px;
    }
    .sys-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 8px var(--success);
        animation: pulse-sys 2.5s infinite;
        flex-shrink: 0;
    }
    @keyframes pulse-sys {
        0%,100% { opacity: 1; transform: scale(1); }
        50%      { opacity: 0.5; transform: scale(0.85); }
    }
    .sys-status-text {
        font-family: var(--font-mono);
        font-size: 10px;
        color: var(--success);
        letter-spacing: 0.5px;
    }

    /* ══════════════════════════════════════════════════════════
       VIEWPORT (main area)
    ══════════════════════════════════════════════════════════ */
    .viewport {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    /* ══════════════════════════════════════════════════════════
       TOP NAV BAR
    ══════════════════════════════════════════════════════════ */
    .top-nav {
        height: var(--topbar-h);
        min-height: var(--topbar-h);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        background: var(--bg-glass);
        border-bottom: 1px solid var(--border);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        position: relative;
        z-index: 50;
        gap: 16px;
        flex-shrink: 0;
    }

    /* ── Left: breadcrumb + status ─────────────────────────── */
    .nav-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: var(--font-mono);
        font-size: 11px;
    }
    .breadcrumb-root { color: var(--text-dim); }
    .breadcrumb-sep  { color: var(--text-dim); }
    .breadcrumb-page {
        color: var(--accent);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .status-badge {
        display: flex;
        align-items: center;
        gap: 7px;
        background: rgba(52,211,153,0.06);
        border: 1px solid rgba(52,211,153,0.18);
        border-radius: 20px;
        padding: 5px 12px;
    }
    .pulse-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 6px var(--success);
        animation: pulse-dot 2s infinite;
        flex-shrink: 0;
    }
    @keyframes pulse-dot {
        0%,100% { opacity: 1; }
        50%      { opacity: 0.4; }
    }
    .status-label {
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 500;
        color: var(--success);
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    /* ── Right: clock + profile ────────────────────────────── */
    .nav-right {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-shrink: 0;
    }

    .system-time {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 1px;
    }
    #clock {
        font-family: var(--font-mono);
        font-size: 16px;
        font-weight: 700;
        color: var(--accent);
        letter-spacing: 1.5px;
        line-height: 1;
        text-shadow: 0 0 12px var(--accent-glow);
    }
    #date {
        font-family: var(--font-mono);
        font-size: 9px;
        color: var(--text-muted);
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* ── Profile Trigger ───────────────────────────────────── */
    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 7px 14px 7px 10px;
        border: 1px solid var(--border);
        border-radius: 40px;
        background: var(--bg-elevated);
        transition: var(--transition);
        position: relative;
        user-select: none;
    }
    .user-profile:hover {
        border-color: var(--border-accent);
        background: var(--accent-dim);
    }

    .avatar-wrapper {
        position: relative;
        flex-shrink: 0;
    }
    .avatar-wrapper img {
        width: 34px; height: 34px;
        border-radius: 50%;
        display: block;
        border: 2px solid var(--accent);
        box-shadow: 0 0 10px var(--accent-glow);
    }
    .status-indicator {
        position: absolute;
        bottom: 1px; right: 1px;
        width: 9px; height: 9px;
        border-radius: 50%;
        background: var(--success);
        border: 2px solid var(--bg-elevated);
        box-shadow: 0 0 6px var(--success);
    }

    .user-details { text-align: right; }
    .user-name  {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1.2;
    }
    .user-role  {
        font-family: var(--font-mono);
        font-size: 9px;
        color: var(--accent);
        letter-spacing: 1.5px;
        font-weight: 700;
        display: block;
        text-transform: uppercase;
    }
    .admin-id-badge {
        font-family: var(--font-mono);
        font-size: 8.5px;
        color: var(--text-dim);
        letter-spacing: 1px;
        display: block;
    }

    .chevron-icon {
        font-size: 10px;
        color: var(--text-muted);
        transition: var(--transition);
        flex-shrink: 0;
    }
    .user-profile:hover .chevron-icon { color: var(--accent); }

    /* ── Dropdown ──────────────────────────────────────────── */
    .dropdown-menu {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 200px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-accent);
        border-radius: var(--radius);
        padding: 6px;
        opacity: 0;
        transform: translateY(-8px) scale(0.97);
        pointer-events: none;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 200;
        box-shadow: 0 16px 48px rgba(0,0,0,0.6), 0 0 1px rgba(0,242,255,0.3);
    }
    .dropdown-menu.active {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: all;
    }
    .dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 12px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        border-radius: 6px;
        transition: var(--transition);
    }
    .dropdown-menu a:hover {
        background: var(--accent-dim);
        color: var(--text-primary);
    }
    .dropdown-menu .divider {
        height: 1px;
        background: var(--border);
        margin: 6px 0;
    }
    .dropdown-menu .logout-text { color: var(--danger) !important; }
    .dropdown-menu .logout-text:hover { background: rgba(248,113,113,0.1) !important; }

    /* ══════════════════════════════════════════════════════════
       CONTENT CANVAS
    ══════════════════════════════════════════════════════════ */
    .content-canvas {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 28px 28px 40px;
        position: relative;
    }

    .inner-canvas {
        max-width: 1400px;
        margin: 0 auto;
        animation: page-in 0.35s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes page-in {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0);    }
    }

    /* ── System log alert ──────────────────────────────────── */
    .system-log-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(0,242,255,0.05);
        border: 1px solid var(--border-accent);
        border-radius: var(--radius);
        padding: 11px 16px;
        margin-bottom: 22px;
        font-family: var(--font-mono);
        font-size: 11.5px;
        color: var(--accent);
        letter-spacing: 0.5px;
        animation: slide-in 0.3s ease;
    }
    @keyframes slide-in {
        from { opacity: 0; transform: translateX(-10px); }
        to   { opacity: 1; transform: translateX(0);     }
    }
    .log-prefix {
        font-weight: 700;
        color: var(--text-muted);
        flex-shrink: 0;
    }

    /* ── 404 Error State ───────────────────────────────────── */
    .error-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        text-align: center;
        gap: 10px;
    }
    .error-state h1 {
        font-family: var(--font-mono);
        font-size: 96px;
        font-weight: 700;
        line-height: 1;
        background: linear-gradient(135deg, var(--accent) 0%, rgba(0,242,255,0.2) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: none;
    }
    .error-state h2 {
        font-family: var(--font-mono);
        font-size: 18px;
        color: var(--text-muted);
        letter-spacing: 4px;
        font-weight: 500;
    }
    .error-state p {
        color: var(--text-muted);
        font-size: 13px;
        max-width: 340px;
        margin-top: 6px;
    }

    /* ── Prime button (shared) ─────────────────────────────── */
    .prime-btn {
        background: var(--accent);
        color: #000;
        font-weight: 700;
        font-family: var(--font-ui);
        font-size: 13px;
        border: none;
        padding: 11px 28px;
        border-radius: 7px;
        cursor: pointer;
        text-align: center;
        letter-spacing: 0.5px;
        transition: var(--transition);
        display: inline-block;
        text-decoration: none;
    }
    .prime-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px var(--accent-glow);
    }

    /* ── Toast notifications ───────────────────────────────── */
    #toast-container {
        position: fixed;
        bottom: 24px; right: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }
    .toast {
        background: var(--bg-elevated);
        border: 1px solid var(--border-accent);
        border-left: 3px solid var(--accent);
        border-radius: var(--radius);
        padding: 12px 18px;
        font-size: 12.5px;
        font-family: var(--font-mono);
        color: var(--text-primary);
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        animation: toast-in 0.3s ease forwards;
        max-width: 320px;
        pointer-events: all;
    }
    .toast.success { border-left-color: var(--success); color: var(--success); }
    .toast.error   { border-left-color: var(--danger);  color: var(--danger);  }
    .toast.warning { border-left-color: var(--warning); color: var(--warning); }
    @keyframes toast-in {
        from { opacity: 0; transform: translateX(20px); }
        to   { opacity: 1; transform: translateX(0);    }
    }
    </style>
</head>
<body class="admin-theme">

<div class="app-container">

    <!-- ════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════ -->
    <aside class="sidebar">

        <div class="sidebar-header" onclick="window.location.href='?page=dashboard'" title="Return to Command Center">
            <div class="logo">EX</div>
            <div class="logo-text">
                Edulyntrix<span>CoreX</span>
                <small class="logo-subtext">Supreme Power v2.0</small>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="section-tag">Oversight</div>
            <a href="?page=dashboard"
               class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                <span class="nav-icon">⬡</span> Command Center
            </a>

            <div class="section-tag">Infrastructure</div>
            <a href="?page=departments"
               class="nav-link <?php echo ($currentPage == 'departments') ? 'active' : ''; ?>">
                <span class="nav-icon">◫</span> Department Nodes
            </a>
            <a href="?page=sessions"
               class="nav-link <?php echo ($currentPage == 'sessions') ? 'active' : ''; ?>">
                <span class="nav-icon">◷</span> Academic Sessions
            </a>

            <div class="section-tag">Personnel</div>
            <a href="?page=staff"
               class="nav-link <?php echo ($currentPage == 'staff') ? 'active' : ''; ?>">
                <span class="nav-icon">◈</span> Personnel Archive
            </a>
            <a href="?page=staff_provision"
               class="nav-link <?php echo ($currentPage == 'staff_provision') ? 'active' : ''; ?>">
                <span class="nav-icon">⊕</span> Staff Provisioning
            </a>
            <a href="?page=student_registry"
               class="nav-link <?php echo ($currentPage == 'student_registry') ? 'active' : ''; ?>">
                <span class="nav-icon">◉</span> Global Registry
            </a>

            <div class="section-tag">Governance</div>
            <a href="?page=finance"
               class="nav-link <?php echo ($currentPage == 'finance') ? 'active' : ''; ?>">
                <span class="nav-icon">◎</span> Financial Monitor
            </a>
            <a href="?page=security"
               class="nav-link <?php echo ($currentPage == 'security') ? 'active' : ''; ?>">
                <span class="nav-icon">⬡</span> Security Logs
            </a>

        </nav>

        <div class="sidebar-footer">
            <div class="sys-status-bar">
                <span class="sys-dot"></span>
                <span class="sys-status-text">ALL SYSTEMS NOMINAL</span>
            </div>
        </div>

    </aside>

    <!-- ════════════════════════════════════
         MAIN VIEWPORT
    ════════════════════════════════════ -->
    <main class="viewport">

        <!-- Top Navigation Bar -->
        <header class="top-nav">

            <div class="nav-left">
                <div class="breadcrumb">
                    <span class="breadcrumb-root">COREX_ROOT</span>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-page"><?php echo strtoupper($currentPage); ?></span>
                </div>
                <div class="status-badge">
                    <span class="pulse-dot"></span>
                    <span class="status-label">ONLINE</span>
                </div>
            </div>

            <div class="nav-right">

                <div class="system-time">
                    <span id="clock">00:00:00</span>
                    <span id="date">LOADING...</span>
                </div>

                <div class="user-profile" id="profileTrigger">
                    <div class="avatar-wrapper">
                        <img src="https://ui-avatars.com/api/?name=Sandeep+Kumar&background=00f2ff&color=000&bold=true&size=80"
                             alt="Admin Avatar">
                        <div class="status-indicator"></div>
                    </div>
                    <div class="user-details">
                        <p class="user-name">Sandeep Kumar</p>
                        <span class="user-role">Root Administrator</span>
                        <span class="admin-id-badge">MASTER-UNIT-01</span>
                    </div>
                    <span class="chevron-icon">▾</span>

                    <div id="dropdown" class="dropdown-menu">
                        <a href="?page=profile">
                            <span>◈</span> My Profile
                        </a>
                        <a href="?page=settings">
                            <span>⚙</span> System Settings
                        </a>
                        <div class="divider"></div>
                        <a href="../includes/logout.php" class="logout-text">
                            <span>⏻</span> Logout Session
                        </a>
                    </div>
                </div>

            </div>
        </header>

        <!-- Content Canvas -->
        <section class="content-canvas">
            <div class="inner-canvas">

                <?php if (isset($_GET['status'])): ?>
                    <div class="system-log-alert">
                        <span class="log-prefix">[ SYS ]</span>
                        <?php
                            $statusMap = [
                                'initialized'          => '✓ Department node created successfully.',
                                'updated'              => '✓ Record updated and synchronized.',
                                'purged'               => '✓ Record permanently removed from registry.',
                                'timeline_synced'      => '✓ Academic session timeline synchronized.',
                                'master_clock_synced'  => '✓ Active master session updated.',
                                'fragment_purged'      => '✓ Session fragment purged.',
                                'purge_denied_active_master' => '⚠ Cannot purge the active master session.',
                                'hod_conflict'         => '⚠ Department already has an assigned HOD.',
                                'email_conflict'       => '⚠ Email address already registered.',
                                'personnel_authorized' => '✓ Personnel identity authorized and registered.',
                                'personnel_purged'     => '✓ Personnel record purged from archive.',
                                'record_updated'       => '✓ Personnel record updated.',
                                'synced'               => '✓ Queue synchronized.',
                            ];
                            $rawStatus = htmlspecialchars($_GET['status']);
                            echo $statusMap[$_GET['status']]
                                ?? strtoupper(str_replace('_', ' ', $rawStatus)) . ' ... OK';
                        ?>
                    </div>
                <?php endif; ?>

                <?php
                    $file = basename($currentPage) . ".php";
                    if (file_exists($file)) {
                        include($file);
                    } else {
                        echo "
                        <div class='error-state'>
                            <h1>404</h1>
                            <h2>NODE OFFLINE</h2>
                            <p>Module <strong style='color:var(--accent)'>[" . htmlspecialchars($currentPage) . "]</strong>
                               is not detected in corex_root.</p>
                            <a href='?page=dashboard' class='prime-btn'
                               style='margin-top:24px;'>↩ Return to Command Center</a>
                        </div>";
                    }
                ?>
            </div>
        </section>

    </main>
</div>

<!-- Toast container -->
<div id="toast-container"></div>

<script>
// ── Precision System Clock ────────────────────────────────────
(function clockLoop() {
    const now   = new Date();
    const clock = document.getElementById('clock');
    const date  = document.getElementById('date');
    if (clock) clock.textContent = now.toLocaleTimeString('en-GB', { hour12: false });
    if (date)  date.textContent  = now.toLocaleDateString('en-GB', {
        weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
    }).toUpperCase();
    setTimeout(clockLoop, 1000);
})();

// ── Authority Dropdown Toggle ─────────────────────────────────
(function () {
    const trigger  = document.getElementById('profileTrigger');
    const menu     = document.getElementById('dropdown');
    const chevron  = trigger?.querySelector('.chevron-icon');

    if (trigger && menu) {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = menu.classList.toggle('active');
            if (chevron) chevron.textContent = open ? '▴' : '▾';
        });
        document.addEventListener('click', function () {
            menu.classList.remove('active');
            if (chevron) chevron.textContent = '▾';
        });
    }
})();

// ── Toast notification helper ─────────────────────────────────
// Usage from any included module:  showToast('Record saved.', 'success')
window.showToast = function (message, type = 'info', duration = 3500) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'none';
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
};

// ── Auto-show toast from URL status if recognised ─────────────
(function () {
    const params = new URLSearchParams(window.location.search);
    const s = params.get('status');
    if (!s) return;
    const toastTypes = {
        initialized: 'success', updated: 'success', purged: 'success',
        timeline_synced: 'success', master_clock_synced: 'success',
        fragment_purged: 'success', personnel_authorized: 'success',
        personnel_purged: 'success', record_updated: 'success',
        hod_conflict: 'error', email_conflict: 'error',
        purge_denied_active_master: 'warning',
    };
    if (toastTypes[s]) showToast(s.replace(/_/g,' ').toUpperCase(), toastTypes[s]);
})();

// ── Active nav highlight guard (SPA navigation) ───────────────
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function () {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

</body>
</html>