<?php
/**
 * EDULYNTRIX CORE X - NEXUS LIGHT ENROLLMENT
 * Version 2.3: HOD-Queue Integration
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Path Fix: Reaching includes from /public/frontend/student/
require_once __DIR__ . '/../../../includes/db_connect.php'; 

/**
 * FETCH ACTIVE DEPARTMENTS
 */
try {
    $dept_query = $pdo->query("SELECT id, dept_name FROM departments WHERE status = 'Active' ORDER BY dept_name ASC");
    $departments = $dept_query->fetchAll();
} catch (PDOException $e) {
    $departments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment | EdulyntrixCoreX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/student_register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Ensuring Nexus Light Aesthetics */
        :root { --soft-blue: #818cf8; --pure-white: #ffffff; --text-slate: #1e293b; }
        body { background: #f8fafc; font-family: 'Inter', sans-serif; color: var(--text-slate); }
        .mesh-bg { position: fixed; inset: 0; background: radial-gradient(circle at 0% 0%, #e0e7ff 0%, #f8fafc 100%); z-index: -1; }
        .bg-branding { position: fixed; top: 20px; left: 20px; font-weight: 800; font-size: 1.2rem; color: var(--soft-blue); opacity: 0.5; }
        .enrollment-wrapper { max-width: 900px; margin: 50px auto; background: var(--pure-white); padding: 40px; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02); }
        .hod-tag { display: inline-block; background: #e0e7ff; color: #4338ca; padding: 5px 15px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; margin-bottom: 15px; }
        .section-title { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        input, select, textarea { width: 100%; padding: 12px 18px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fcfcfd; margin-bottom: 15px; font-size: 0.9rem; transition: 0.3s; }
        input:focus { border-color: var(--soft-blue); outline: none; box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.1); }
        .dual-input { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .action-btn { width: 100%; padding: 15px; border-radius: 12px; background: #4f46e5; color: white; border: none; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 20px; }
        .action-btn:hover { background: #4338ca; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2); }
        .dp-preview { width: 80px; height: 80px; border-radius: 20px; background: #f1f5f9; margin: 0 auto 15px; overflow: hidden; display: grid; place-items: center; border: 2px dashed #cbd5e1; }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <div class="bg-branding">EDULYNTRIX<span>COREX</span></div>

    <main class="full-page-container">
        <div class="enrollment-wrapper">
            
            <header class="form-header" style="text-align: center; margin-bottom: 40px;">
                <div class="hod-tag">HOD Approval Protocol v6.8</div>
                <h1 style="font-weight: 800; font-size: 2rem;">Student <b>Enrollment</b></h1>
                <p style="color: #64748b;">Submit credentials for institutional verification</p>
            </header>

            <form action="../../../includes/register_handler.php" method="POST" enctype="multipart/form-data" id="studentForm">
                
                <div class="dp-section" style="text-align: center; margin-bottom: 30px;">
                    <div class="dp-preview" id="dpPreview">
                        <i class="fa-solid fa-camera" style="color: #cbd5e1;"></i>
                    </div>
                    <label for="dpInput" style="cursor: pointer; color: #4f46e5; font-size: 0.8rem; font-weight: 700;">Upload Profile Node Image</label>
                    <input type="file" name="profile_pic" id="dpInput" accept="image/*" hidden required>
                </div>

                <div class="grid-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                    <div class="form-group">
                        <h3 class="section-title"><i class="fa-solid fa-id-card"></i> Identity Profile</h3>
                        <input type="text" name="full_name" placeholder="Full Legal Name" required>
                        
                        <div class="dual-input">
                            <input type="text" name="father_name" placeholder="Father's Name" required>
                            <input type="tel" name="father_phone" placeholder="Father's Phone" required pattern="[0-9]{10}">
                        </div>
                        
                        <div class="dual-input">
                            <input type="text" name="mother_name" placeholder="Mother's Name" required>
                            <input type="tel" name="mother_phone" placeholder="Mother's Phone" required pattern="[0-9]{10}">
                        </div>
                        
                        <textarea name="address" placeholder="Permanent Residential Address" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <h3 class="section-title"><i class="fa-solid fa-shield-halved"></i> Access Credentials</h3>
                        <div class="dual-input">
                            <input type="text" placeholder="ID: AUTO-GEN" readonly style="background: #f1f5f9; color: #6366f1;">
                            
                            <select name="dept_id" id="deptSelect" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" data-name="<?= htmlspecialchars($dept['dept_name']) ?>">
                                        <?= htmlspecialchars($dept['dept_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <input type="hidden" name="branch_name" id="branchName">

                        <input type="email" name="email" placeholder="Institutional Email" required>
                        <input type="tel" name="phone" placeholder="Mobile Number" required pattern="[0-9]{10}">
                        
                        <div class="dual-input">
                            <input type="password" name="password" id="pass" placeholder="Create Password" required>
                            <input type="password" id="confirm" placeholder="Confirm Password" required>
                        </div>
                    </div>
                </div>

                <div class="submission-footer" style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 30px;">
                    <div style="background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 15px; border-radius: 12px; font-size: 0.8rem; text-align: center;">
                        <i class="fa-solid fa-circle-info"></i> NOTICE: Integration is <b>PENDING</b> until verified by the Department Executive.
                    </div>
                    <button type="submit" class="action-btn" id="submitBtn">INITIALIZE ENROLLMENT</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Store the text of selected department for the queue table
        document.getElementById('deptSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('branchName').value = selectedOption.getAttribute('data-name');
        });

        // DP Preview
        document.getElementById('dpInput').onchange = function (evt) {
            const [file] = this.files;
            if (file) {
                document.getElementById('dpPreview').innerHTML = `<img src="${URL.createObjectURL(file)}" style="width:100%; height:100%; object-fit:cover; border-radius:18px;">`;
            }
        }

        // Form Validation
        document.getElementById('studentForm').onsubmit = function(e) {
            const p = document.getElementById('pass').value;
            const c = document.getElementById('confirm').value;
            if(p !== c) {
                e.preventDefault();
                alert("SECURITY ALERT: Passwords do not match!");
            } else {
                document.getElementById('submitBtn').innerHTML = '<i class="fa-solid fa-sync fa-spin"></i> TRANSMITTING DATA...';
            }
        };
    </script>
</body>
</html>