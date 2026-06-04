<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X
 * SUPREME AUTHORITY GATEWAY
 * FINAL STABLE VERSION
 * SESSION + DEPARTMENT + SECURITY FIXED
 * ============================================================
 */

ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
RESET OLD SESSION
============================================================ */

if (
    isset($_SESSION['logged_in'])
    &&
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    session_unset();
}

/* ============================================================
DATABASE
============================================================ */

$db_path =
    $_SERVER['DOCUMENT_ROOT']
    . '/EdulyntrixCoreX/includes/db_connect.php';

if (!file_exists($db_path)) {

    die("CRITICAL_SYSTEM_FAILURE");
}

require_once $db_path;

/* ============================================================
STATE
============================================================ */

$error = '';

/* ============================================================
LOGIN ENGINE
============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userInput =
        trim($_POST['staffId'] ?? '');

    $inputPass =
        trim($_POST['password'] ?? '');

    /*
    =========================================================
    VALIDATION
    =========================================================
    */

    if (
        empty($userInput)
        ||
        empty($inputPass)
    ) {

        $error =
            'SEC_ERR: EMPTY_CREDENTIALS';
    }

    else {

        try {

            /*
            =================================================
            STAFF LOOKUP
            =================================================
            */

            $stmt = $pdo->prepare("

                SELECT *

                FROM staff

                WHERE

                    (
                        login_id = ?
                        OR staff_id = ?
                    )

                    AND LOWER(status) = 'active'

                LIMIT 1

            ");

            $stmt->execute([
                $userInput,
                $userInput
            ]);

            $user =
                $stmt->fetch(PDO::FETCH_ASSOC);

            /*
            =================================================
            USER NOT FOUND
            =================================================
            */

            if (!$user) {

                $error =
                    'SEC_ERR: IDENTITY_UNKNOWN';
            }

            else {

                /*
                =============================================
                PASSWORD SUPPORT
                =============================================
                */

                $password_valid = false;

                /*
                =============================================
                HASHED PASSWORD
                =============================================
                */

                if (

                    password_verify(
                        $inputPass,
                        $user['password']
                    )

                ) {

                    $password_valid = true;
                }

                /*
                =============================================
                PLAIN PASSWORD
                =============================================
                */

                elseif (

                    trim($inputPass)
                    === trim($user['password'])

                ) {

                    $password_valid = true;
                }

                /*
                =============================================
                INVALID PASSWORD
                =============================================
                */

                if (!$password_valid) {

                    $error =
                        'SEC_ERR: INVALID_KEY';
                }

                else {

                    /*
                    =========================================
                    SESSION REGENERATION
                    =========================================
                    */

                    session_unset();

                    session_regenerate_id(true);

                    /*
                    =========================================
                    NORMALIZATION
                    =========================================
                    */

                    $role =
                        strtolower(
                            trim($user['role'])
                        );

                    /*
                    =========================================
                    CORE SESSION
                    =========================================
                    */

                    $_SESSION['logged_in'] = true;

                    $_SESSION['role'] =
                        $role;

                    $_SESSION['user_role'] =
                        $role;

                    /*
                    =========================================
                    USER DATA
                    =========================================
                    */

                    $_SESSION['user_id'] =
                        $user['id'];

                    $_SESSION['staff_id'] =
                        trim($user['staff_id']);

                    $_SESSION['login_id'] =
                        trim($user['login_id']);

                    $_SESSION['full_name'] =
                        trim($user['full_name']);

                    /*
                    =========================================
                    DEPARTMENT FIX
                    =========================================
                    */

                    $_SESSION['dept_id'] =
                        $user['dept_id'];

                    $_SESSION['department'] =
                        trim(
                            $user['department']
                            ?? ''
                        );

                    /*
                    =========================================
                    CRITICAL FIX
                    =========================================
                    */

                    $_SESSION['dept_name'] =
                        trim(
                            $user['department']
                            ?? ''
                        );

                    /*
                    =========================================
                    PROFILE
                    =========================================
                    */

                    $_SESSION['profile_pic'] =
                        $user['profile_pic']
                        ?? '';

                    /*
                    =========================================
                    ROLE ROUTING
                    =========================================
                    */

                    if ($role === 'hod') {

                        $redirect =
                            '/EdulyntrixCoreX/public/frontend/staff/hod/dashboard.php';

                    }

                    elseif ($role === 'admin') {

                        $redirect =
                            '/EdulyntrixCoreX/corex_root/layout.php?page=dashboard';

                    }

                    else {

                        $redirect =
                            '/EdulyntrixCoreX/public/frontend/staff/faculty_dashboard.php';
                    }

                    header(
                        "Location: " . $redirect
                    );

                    exit;
                }
            }
        }

        catch(PDOException $e){

            error_log(
                'STAFF_LOGIN_ERROR: '
                . $e->getMessage()
            );

            $error =
                'SYS_ERR: AUTH_NODE_FAILURE';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Authority Gateway | EdulyntrixCoreX
</title>

<link
href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap"
rel="stylesheet"
>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<style>

:root{

    --primary:#10b981;
    --primaryGlow:rgba(16,185,129,.25);

    --bg:#020617;

    --glass:rgba(15,23,42,.82);

    --border:rgba(255,255,255,.08);

    --textDim:#94a3b8;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Plus Jakarta Sans',sans-serif;
}

body{

    height:100vh;

    overflow:hidden;

    background:var(--bg);

    display:flex;

    align-items:center;
    justify-content:center;

    color:#fff;
}

.mesh{

    position:fixed;
    inset:0;

    background:
    linear-gradient(
        135deg,
        #064e3b,
        #020617,
        #0f172a,
        #065f46
    );

    background-size:400% 400%;

    animation:meshMove 18s ease infinite;

    filter:blur(70px);

    z-index:-1;
}

@keyframes meshMove{

    0%{
        background-position:0% 50%;
    }

    50%{
        background-position:100% 50%;
    }

    100%{
        background-position:0% 50%;
    }
}

.branding{

    position:fixed;

    top:50%;
    left:50%;

    transform:translate(-50%,-50%);

    font-size:9vw;
    font-weight:900;

    color:rgba(255,255,255,.02);

    letter-spacing:-5px;

    pointer-events:none;
}

.branding span{
    color:rgba(16,185,129,.04);
}

.login-card{

    width:100%;
    max-width:430px;

    padding:3.5rem;

    border-radius:28px;

    background:var(--glass);

    backdrop-filter:blur(30px);

    border:1px solid var(--border);

    box-shadow:0 40px 100px rgba(0,0,0,.55);

    text-align:center;
}

.identity-badge{

    display:inline-flex;

    align-items:center;

    gap:8px;

    padding:8px 14px;

    border-radius:10px;

    background:rgba(255,255,255,.04);

    border:1px solid var(--border);

    font-size:.68rem;

    font-weight:700;

    margin-bottom:28px;

    color:var(--textDim);

    transition:.3s;
}

.login-header h1{

    font-size:2rem;

    font-weight:400;

    margin-bottom:6px;
}

.login-header h1 b{
    color:var(--primary);
    font-weight:800;
}

.login-header p{

    color:var(--textDim);

    font-size:.8rem;

    margin-bottom:35px;
}

.error-box{

    margin-bottom:20px;

    padding:14px;

    border-radius:12px;

    background:rgba(248,113,113,.08);

    border:1px solid rgba(248,113,113,.2);

    color:#f87171;

    font-size:.75rem;

    font-family:'JetBrains Mono';
}

.input-group{

    margin-bottom:20px;

    text-align:left;
}

.input-group label{

    display:block;

    margin-bottom:8px;

    font-size:.68rem;

    font-weight:800;

    text-transform:uppercase;

    color:var(--textDim);
}

.input-group input{

    width:100%;

    padding:16px;

    border-radius:14px;

    border:1px solid var(--border);

    background:rgba(0,0,0,.25);

    color:#fff;

    font-size:.92rem;

    outline:none;

    transition:.3s;
}

.input-group input:focus{

    border-color:var(--primary);

    box-shadow:0 0 20px var(--primaryGlow);
}

.auth-btn{

    width:100%;

    margin-top:10px;

    border:none;

    padding:16px;

    border-radius:14px;

    cursor:pointer;

    background:var(--primary);

    color:#02140d;

    font-weight:800;

    letter-spacing:1px;

    text-transform:uppercase;

    transition:.3s;
}

.auth-btn:hover{

    transform:translateY(-2px);

    box-shadow:0 15px 30px var(--primaryGlow);
}

.footer-links{

    margin-top:30px;

    padding-top:20px;

    border-top:1px solid var(--border);

    display:flex;

    justify-content:space-between;
}

.footer-links a{

    text-decoration:none;

    color:var(--textDim);

    font-size:.75rem;

    transition:.2s;
}

.footer-links a:hover{
    color:var(--primary);
}

.loader{

    display:none;

    margin-left:10px;

    animation:spin 1s linear infinite;
}

.loading .loader{
    display:inline-block;
}

@keyframes spin{

    from{
        transform:rotate(0deg);
    }

    to{
        transform:rotate(360deg);
    }
}

</style>

</head>

<body>

<div class="mesh"></div>

<div class="branding">

    EDULYNTRIX
    <span>COREX</span>

</div>

<main class="login-card">

<div class="identity-badge" id="identityBadge">

    <i class="fa-solid fa-fingerprint"></i>

    SYSTEM IDLE

</div>

<header class="login-header">

    <p>
        Secure Node Access
    </p>

    <h1>

        Authority
        <b>Gateway</b>

    </h1>

</header>

<?php if(!empty($error)): ?>

<div class="error-box">

    <i class="fa-solid fa-triangle-exclamation"></i>

    <?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>

<form method="POST" id="staffForm">

<div class="input-group">

<label>
    Institutional ID
</label>

<input
    type="text"
    name="staffId"
    id="staffId"
    placeholder="USER-ID"
    autocomplete="off"
    required
>

</div>

<div class="input-group">

<label>
    Security Key
</label>

<input
    type="password"
    name="password"
    placeholder="********"
    required
>

</div>

<button
    type="submit"
    class="auth-btn"
    id="submitBtn"
>

    <span class="btn-text">

        Login

    </span>

    <i class="fa-solid fa-circle-notch loader"></i>

</button>

</form>

<footer class="footer-links">

<a href="staff-reset.php">

    Forgot Credentials?

</a>

<a href="/EdulyntrixCoreX/public/frontend/index.php">

    Portal Home

</a>

</footer>

</main>

<script>

const idInput =
    document.getElementById('staffId');

const badge =
    document.getElementById('identityBadge');

const form =
    document.getElementById('staffForm');

const btn =
    document.getElementById('submitBtn');

idInput.addEventListener('input', e => {

    const val =
        e.target.value.toUpperCase();

    if(
        val.startsWith('HOD-')
        ||
        val.includes('HOD')
    ){

        badge.innerHTML =
            '<i class="fa-solid fa-user-shield"></i> EXECUTIVE AUTHORITY';

        badge.style.color =
            '#10b981';

        badge.style.borderColor =
            '#10b981';
    }

    else if(
        val.startsWith('ADM-')
    ){

        badge.innerHTML =
            '<i class="fa-solid fa-screwdriver-wrench"></i> SYSTEM ADMIN';

        badge.style.color =
            '#3b82f6';

        badge.style.borderColor =
            '#3b82f6';
    }

    else if(val.length > 3){

        badge.innerHTML =
            '<i class="fa-solid fa-user-tie"></i> FACULTY NODE';

        badge.style.color =
            '#a855f7';

        badge.style.borderColor =
            '#a855f7';
    }

    else {

        badge.innerHTML =
            '<i class="fa-solid fa-fingerprint"></i> SYSTEM IDLE';

        badge.style.color =
            '#94a3b8';

        badge.style.borderColor =
            'rgba(255,255,255,.08)';
    }
});

form.addEventListener('submit', () => {

    btn.classList.add('loading');

    btn.querySelector('.btn-text').innerText =
        'VERIFYING...';

    btn.disabled = true;
});

</script>

</body>
</html>