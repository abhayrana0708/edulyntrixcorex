<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../../../includes/db_connect.php';

$sid = $_SESSION['student_id'] ?? null;
$student = [];

if ($sid) {
    // We bind as STR to ensure alphanumeric IDs like '2026IT001' don't trigger SQL errors
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :sid");
    $stmt->bindValue(':sid', (string)$sid, PDO::PARAM_STR);
    $stmt->execute();
    $student = $stmt->fetch();
}

// Optimized Dept Mapping
$dept_map = [1 => 'CSE', 2 => 'ECE', 3 => 'MECH', 4 => 'CIVIL'];
$dept_name = $dept_map[$student['dept_id'] ?? ''] ?? 'General Sciences';

// Standardized Image Path
$profile_pic = !empty($student['profile_pic']) ? $student['profile_pic'] : 'default.png';
$base_img_path = "../../../uploads/profiles/";
?>

<div class="module-entrance" style="animation: nexusFadeIn 0.5s ease-out forwards;">
    <h2 style="color: #1e293b; font-weight: 800; margin-bottom: 2rem;">Profile <b>Settings</b></h2>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        
        <div style="background: white; padding: 30px; border-radius: 24px; border: 1px solid #e2e8f0; text-align: center; height: fit-content;">
            <div style="position: relative; display: inline-block;">
                <img id="avatarPreview" src="<?= $base_img_path . $profile_pic ?>" 
                     onerror="this.src='<?= $base_img_path ?>default.png'"
                     style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid #f8fafc; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                
                <label for="picUpload" style="position: absolute; bottom: 5px; right: 5px; background: #3b82f6; color: white; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                    <i class="fas fa-camera" style="font-size: 0.9rem;"></i>
                </label>
            </div>
            
            <h3 style="margin-top: 20px; color: #1e293b; font-weight: 800; font-size: 1.2rem;"><?= htmlspecialchars($student['full_name'] ?? 'Student Node') ?></h3>
            <span style="background: #eff6ff; color: #3b82f6; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px;">
                <?= htmlspecialchars($student['student_id'] ?? 'N/A') ?>
            </span>
            
            <div style="margin-top: 30px; display: grid; gap: 12px;">
                <div style="padding: 14px; background: #f8fafc; border-radius: 18px; text-align: left; border: 1px solid #f1f5f9;">
                    <small style="color: #94a3b8; font-weight: 700; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 1px;">Academic Dept</small>
                    <div style="font-weight: 700; color: #475569; font-size: 0.95rem;"><?= $dept_name ?></div>
                </div>
                <div style="padding: 14px; background: #f8fafc; border-radius: 18px; text-align: left; border: 1px solid #f1f5f9;">
                    <small style="color: #94a3b8; font-weight: 700; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 1px;">Course Standing</small>
                    <div style="font-weight: 700; color: #475569; font-size: 0.95rem;">Semester <?= htmlspecialchars($student['current_semester'] ?? '0') ?> • Year <?= htmlspecialchars($student['academic_year'] ?? '0') ?></div>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 30px;">
            
            <div style="background: white; padding: 40px; border-radius: 24px; border: 1px solid #e2e8f0;">
                <h4 style="color:#1e293b; margin-bottom: 25px; font-weight: 800; font-size: 1.1rem;">General <b>Information</b></h4>
                <form id="profileUpdateForm" enctype="multipart/form-data">
                    <input type="file" id="picUpload" name="profile_pic" accept="image/*" style="display: none;" onchange="previewImage(this)">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        <div>
                            <label class="nexus-label">CONTACT PHONE</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" class="nexus-input">
                        </div>
                        <div>
                            <label class="nexus-label">EMAIL ADDRESS</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" class="nexus-input">
                        </div>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <label class="nexus-label">PERMANENT ADDRESS</label>
                        <textarea name="address" class="nexus-input" style="height: 100px; resize: none;"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; opacity: 0.6;">
                        <div>
                            <label class="nexus-label">FATHER'S NAME</label>
                            <input type="text" value="<?= htmlspecialchars($student['father_name'] ?? 'N/A') ?>" disabled class="nexus-input-locked">
                        </div>
                        <div>
                            <label class="nexus-label">MOTHER'S NAME</label>
                            <input type="text" value="<?= htmlspecialchars($student['mother_name'] ?? 'N/A') ?>" disabled class="nexus-input-locked">
                        </div>
                    </div>

                    <button type="submit" class="save-btn">
                        SAVE CHANGES
                    </button>
                    <span id="profileMsg" style="margin-left: 15px; font-weight: 700; font-size: 0.85rem;"></span>
                </form>
            </div>

            <div style="background: #fff1f2; padding: 35px; border-radius: 24px; border: 1px solid #fecdd3;">
                <h4 style="color: #9f1239; font-weight: 800; margin-bottom: 20px; font-size: 1.1rem;">Security <b>Gateway</b></h4>
                <form id="passwordUpdateForm" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: flex-end;">
                    <div>
                        <label class="nexus-label" style="color: #9f1239;">CURRENT PASSWORD</label>
                        <input type="password" name="current_password" required placeholder="••••••••" class="nexus-input" style="border-color: #fecdd3;">
                    </div>
                    <div>
                        <label class="nexus-label" style="color: #9f1239;">NEW PASSWORD</label>
                        <input type="password" name="new_password" required placeholder="Min. 8 chars" class="nexus-input" style="border-color: #fecdd3;">
                    </div>
                    <button type="submit" style="background: #e11d48; color: white; border: none; padding: 14px 25px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">
                        RE-SYNC CREDENTIALS
                    </button>
                </form>
                <div id="passMsg" style="margin-top: 15px; font-weight: 700; font-size: 0.85rem;"></div>
            </div>

        </div>
    </div>
</div>

<style>
.nexus-label { display:block; font-size: 0.65rem; font-weight: 800; color: #64748b; margin-bottom: 8px; letter-spacing: 0.5px; }
.nexus-input { width:100%; padding:14px; border-radius:15px; border:1px solid #e2e8f0; font-size: 0.95rem; font-weight: 600; transition: 0.2s; }
.nexus-input-locked { width:100%; padding:14px; border-radius:15px; border:1px solid #e2e8f0; background:#f8fafc; cursor: not-allowed; font-weight: 600; }
.save-btn { background: #1e293b; color: white; border: none; padding: 15px 40px; border-radius: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
.save-btn:hover { background: #0f172a; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }

.nexus-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
label[for="picUpload"]:hover { transform: scale(1.1); background: #2563eb !important; }
</style>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('avatarPreview');
            img.style.opacity = '0';
            setTimeout(() => {
                img.src = e.target.result;
                img.style.opacity = '1';
            }, 200);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>