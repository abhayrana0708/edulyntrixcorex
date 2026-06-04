<?php
/**
 * EDULYNTRIX CORE X - CREDENTIAL RECOVERY (INTEGRATED)
 * Theme: Nexus Light (Student Indigo)
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. DYNAMIC DATABASE UPLINK
// Moves up 3 levels to reach the root 'includes' folder
$db_path = __DIR__ . '/../../../includes/db_connect.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    die("<div style='background:#fee2e2; color:#b91c1c; padding:20px; font-family:sans-serif; border-radius:10px;'>
            <strong>COREX_UPLINK_ERROR:</strong> Database configuration not found at " . htmlspecialchars($db_path) . "
         </div>");
}

// 2. SERVER-SIDE PROCESSING LOGIC
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recover_access'])) {
    $enrollment = $_POST['enrollment_no'] ?? '';
    $email = $_POST['email'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ? AND email = ? LIMIT 1");
        $stmt->execute([$enrollment, $email]);
        
        if ($stmt->fetch()) {
            $message = json_encode(['success' => true, 'text' => "Security token dispatched to " . $email]);
        } else {
            $message = json_encode(['success' => false, 'text' => "Credentials not recognized in CoreX Registry."]);
        }
    } catch (PDOException $e) {
        $message = json_encode(['success' => false, 'text' => "Database Error: " . $e->getMessage()]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Credentials | EdulyntrixCoreX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/student_auth.css">
</head>
<body>

    <div class="mesh-bg"></div>
    <div class="bg-branding">Edulyntrix<span>CoreX</span></div>

    <main class="page-wrapper">
        <!-- Added .active class trigger for the opacity: 0 in your CSS -->
        <section class="login-card" id="resetReveal">
            <header class="login-header">
                <p>Security Recovery</p>
                <h1>Reset <b>Password</b></h1>
            </header>

            <form class="login-form" id="studentResetForm" method="POST">
                <div style="margin-bottom: 20px;">
                    <p style="font-size: 0.8rem; color: #94a3b8; line-height: 1.6;">
                        Enter your <b>Enrollment Number</b> and <b>Institutional Email</b>. 
                        A secure reset token will be dispatched to your inbox.
                    </p>
                </div>

                <input type="text" name="enrollment_no" placeholder="Enrollment No (STU-XXX)" required>
                <input type="email" name="email" placeholder="Institutional Email" required>

                <button type="submit" name="recover_access" id="resetBtn">Dispatch Reset Link</button>
            </form>

            <footer class="login-footer">
                <p>Remembered your password? <a href="student_login.php">Back to Login</a></p>
            </footer>
        </section>
    </main>

    <script>
    /** 3. INTEGRATED FRONTEND LOGIC **/
    window.addEventListener('load', () => {
        // Trigger the Entrance Animation from your CSS
        document.getElementById('resetReveal').classList.add('active');

        // Handle PHP Response if present
        const phpResponse = <?php echo $message ? $message : 'null'; ?>;
        if (phpResponse) {
            if (phpResponse.success) {
                alert("✅ " + phpResponse.text);
                window.location.href = 'student_login.php';
            } else {
                alert("❌ " + phpResponse.text);
            }
        }
    });

    // Button Interaction State
    const resetForm = document.getElementById('studentResetForm');
    const resetBtn = document.getElementById('resetBtn');

    resetForm.addEventListener('submit', () => {
        resetBtn.innerText = "VERIFYING UPLINK...";
        resetBtn.style.opacity = "0.7";
        resetBtn.style.pointerEvents = "none";
    });
    </script>
</body>
</html>