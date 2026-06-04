<?php
/**
 * EDULYNTRIX CORE X - UNIFIED SESSION TERMINATOR
 * Location: /public/frontend/staff/logout.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CAPTURE IDENTITY BEFORE PURGE
$role = $_SESSION['role'] ?? 'staff';

// 2. WIPE SESSION DATA
$_SESSION = array();

// 3. EXPIRE BROWSER COOKIE
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. DESTROY SERVER DATA
session_destroy();

/** 5. THE ABSOLUTE REDIRECT FIX **/
// We build the path from the root to avoid "Object Not Found" errors
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$projectRoot = "/EdulyntrixCoreX/public/frontend/";

if ($role === 'student') {
    // Force redirect to Student Login
    header("Location: " . $protocol . $host . $projectRoot . "student/login.php?status=terminated");
} else {
    // Force redirect to Staff Gateway
    header("Location: " . $protocol . $host . $projectRoot . "staff/login.php?status=terminated");
}
exit();