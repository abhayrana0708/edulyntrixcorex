<?php
// Ensure session is active to fetch student name if needed
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<div class="nexus-sidebar" style="width: 280px; height: 100vh; background: #1e293b; color: white; display: flex; flex-direction: column; position: fixed; left: 0; top: 0; border-right: 1px solid rgba(255,255,255,0.1);">
    
    <div style="padding: 40px 30px; text-align: left;">
        <h1 style="margin: 0; font-size: 1.5rem; font-weight: 900; letter-spacing: -1px;">CORE<b>X</b></h1>
        <small style="color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.6rem; letter-spacing: 2px;">Nexus Management</small>
    </div>

    <nav style="flex-grow: 1; padding: 0 20px;">
        <ul style="list-style: none; padding: 0; margin: 0;">
            
            <li class="nav-item" style="margin-bottom: 5px;">
                <a href="javascript:void(0)" 
                   onclick="loadModule('overview', this)" 
                   class="nav-link active"
                   style="display: flex; align-items: center; gap: 15px; padding: 15px 20px; border-radius: 12px; color: #cbd5e1; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.2s;">
                    <i class="fas fa-th-large" style="width: 20px;"></i> Overview
                </a>
            </li>

            <li class="nav-item" style="margin-bottom: 5px;">
                <a href="javascript:void(0)" 
                   onclick="loadModule('finance', this)" 
                   class="nav-link"
                   style="display: flex; align-items: center; gap: 15px; padding: 15px 20px; border-radius: 12px; color: #cbd5e1; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.2s;">
                    <i class="fas fa-wallet" style="width: 20px;"></i> Finance
                </a>
            </li>

            <li class="nav-item" style="margin-bottom: 5px;">
                <a href="javascript:void(0)" 
                   onclick="loadModule('profile', this)" 
                   class="nav-link"
                   style="display: flex; align-items: center; gap: 15px; padding: 15px 20px; border-radius: 12px; color: #cbd5e1; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.2s;">
                    <i class="fas fa-user-gear" style="width: 20px;"></i> Profile Settings
                </a>
            </li>

        </ul>
    </nav>

    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
        <a href="logout.php" style="display: flex; align-items: center; gap: 15px; padding: 15px 20px; border-radius: 12px; color: #f87171; text-decoration: none; font-weight: 700; font-size: 0.85rem; background: rgba(239, 68, 68, 0.05);">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<style>
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.05);
        color: white !important;
    }
    .nav-link.active {
        background: #3b82f6 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
</style>