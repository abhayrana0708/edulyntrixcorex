<?php
/**
 * ============================================================
 * EDULYNTRIX CORE X - STUDENT LOGIN PORTAL
 * FINAL FIXED VERSION
 * ============================================================
 */

session_start();

/*
============================================================
AUTO REDIRECT
============================================================
*/

if (isset($_SESSION['student_id'])) {

    header("Location: dashboard.php");
    exit();
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
        Student Desk | EdulyntrixCoreX
    </title>

    <!-- GOOGLE FONT -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- CSS -->

    <link
        rel="stylesheet"
        href="assets/css/student_auth.css"
    >

</head>

<body>

    <!-- BACKGROUND -->

    <div class="mesh-bg"></div>

    <div class="bg-branding">

        Edulyntrix<span>CoreX</span>

    </div>

    <!-- MAIN -->

    <main class="page-wrapper">

        <section
            class="login-card"
            id="studentReveal"
        >

            <!-- HEADER -->

            <header class="login-header">

                <p>
                    Academic Success Hub
                </p>

                <h1>

                    Student <b>Desk</b>

                </h1>

            </header>

            <!-- ERROR ALERT -->

            <?php if(isset($_GET['error'])): ?>

                <div class="auth-alert error">

                    <?php

                    switch($_GET['error']){

                        case 'invalid':

                            echo '❌ Invalid Enrollment ID or Password';

                            break;

                        case 'inactive':

                            echo '⚠️ Student Account Inactive';

                            break;

                        case 'pending':

                            echo '⚠️ HOD Approval Required';

                            break;

                        case 'empty':

                            echo '⚠️ Fill All Required Fields';

                            break;

                        default:

                            echo '❌ Authentication Failed';
                    }

                    ?>

                </div>

            <?php endif; ?>

            <!-- SUCCESS ALERT -->

            <?php if(isset($_GET['success'])): ?>

                <div class="auth-alert success">

                    ✅ Registration Successful

                    <?php if(isset($_GET['id'])): ?>

                        <br><br>

                        Enrollment ID:

                        <b>

                            <?= htmlspecialchars($_GET['id']) ?>

                        </b>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <!-- LOGIN FORM -->

            <form
                class="login-form"
                id="studentLoginForm"
                action="../../../includes/auth_handler.php"
                method="POST"
            >

                <!-- ROLE -->

                <input
                    type="hidden"
                    name="role"
                    value="student"
                >

                <!-- STUDENT ID -->

                <div class="input-group">

                    <input
                        type="text"
                        name="student_id"
                        id="student_id"
                        placeholder="Enrollment Number (e.g. 2026CSE001)"
                        autocomplete="off"
                        required
                    >

                </div>

                <!-- PASSWORD -->

                <div class="input-group">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Access Password"
                        required
                    >

                </div>

                <!-- OPTIONS -->

                <div class="form-options">

                    <label
                        style="
                            display:flex;
                            align-items:center;
                            gap:8px;
                            cursor:pointer;
                        "
                    >

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        <span
                            style="
                                font-size:0.85rem;
                                color:#64748b;
                            "
                        >

                            Remember Me

                        </span>

                    </label>

                    <a
                        href="reset_password.php"
                        style="
                            font-size:0.85rem;
                            color:#3b82f6;
                            text-decoration:none;
                        "
                    >

                        Recover Credentials?

                    </a>

                </div>

                <!-- SUBMIT -->

                <button
                    type="submit"
                    id="authBtn"
                >

                    ACCESS STUDENT DESK

                </button>

            </form>

            <!-- FOOTER -->

            <footer class="login-footer">

                <p>

                    New Student?

                    <a href="registration.php">

                        Register for Enrollment

                    </a>

                </p>

            </footer>

        </section>

    </main>

    <!-- JS -->

    <script src="assets/js/student_auth.js"></script>

</body>
</html>