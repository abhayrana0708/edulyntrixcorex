<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../../../includes/db_connect.php';

$sid     = $_SESSION['student_id'] ?? null;
$student = [];

if ($sid) {
    // Bind as STR so alphanumeric IDs like '2026IT001' don't trigger SQL errors
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :sid");
    $stmt->bindValue(':sid', (string) $sid, PDO::PARAM_STR);
    $stmt->execute();
    $student = $stmt->fetch();
}

$departments    = [1 => 'CSE', 2 => 'ECE', 3 => 'MECH', 4 => 'CIVIL'];
$departmentName = $departments[$student['dept_id'] ?? ''] ?? 'General Sciences';

$profilePic = !empty($student['profile_pic']) ? $student['profile_pic'] : 'default.png';
$uploadPath = '../../../uploads/profiles/';
?>

<div class="module-entrance">

    <h2 class="section-heading">Profile <b>Settings</b></h2>

    <div class="profile-grid">

        <!-- Sidebar: avatar + quick info -->
        <div class="card profile-sidebar">

            <div class="avatar-wrap">
                <img
                    id="avatarPreview"
                    src="<?= $uploadPath . $profilePic ?>"
                    onerror="this.src='<?= $uploadPath ?>default.png'"
                    class="avatar-img"
                >
                <label for="picUpload" class="avatar-edit-btn">
                    <i class="fas fa-camera"></i>
                </label>
            </div>

            <h3 class="student-name"><?= htmlspecialchars($student['full_name'] ?? 'Student Node') ?></h3>
            <span class="student-id-badge"><?= htmlspecialchars($student['student_id'] ?? 'N/A') ?></span>

            <div class="info-stack">
                <div class="info-box">
                    <small>Academic Dept</small>
                    <div><?= htmlspecialchars($departmentName) ?></div>
                </div>
                <div class="info-box">
                    <small>Course Standing</small>
                    <div>
                        Semester <?= htmlspecialchars($student['current_semester'] ?? '0') ?>
                        &bull;
                        Year <?= htmlspecialchars($student['academic_year'] ?? '0') ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Main column: editable info + password -->
        <div class="profile-main">

            <div class="card">
                <h4 class="card-title">General <b>Information</b></h4>

                <form id="profileUpdateForm" enctype="multipart/form-data">

                    <input
                        type="file"
                        id="picUpload"
                        name="profile_pic"
                        accept="image/*"
                        class="hidden-file-input"
                        onchange="previewImage(this)"
                    >

                    <div class="field-grid">
                        <div class="field-group">
                            <label class="nexus-label">Contact Phone</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" class="nexus-input">
                        </div>
                        <div class="field-group">
                            <label class="nexus-label">Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" class="nexus-input">
                        </div>
                    </div>

                    <div class="field-group field-group-full">
                        <label class="nexus-label">Permanent Address</label>
                        <textarea name="address" class="nexus-input address-textarea"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                    </div>

                    <div class="field-grid locked-fields">
                        <div class="field-group">
                            <label class="nexus-label">Father's Name</label>
                            <input type="text" disabled value="<?= htmlspecialchars($student['father_name'] ?? 'N/A') ?>" class="nexus-input-locked">
                        </div>
                        <div class="field-group">
                            <label class="nexus-label">Mother's Name</label>
                            <input type="text" disabled value="<?= htmlspecialchars($student['mother_name'] ?? 'N/A') ?>" class="nexus-input-locked">
                        </div>
                    </div>

                    <button type="submit" class="save-btn" id="saveProfileBtn">Save Changes</button>
                    <span id="profileMsg" class="feedback-msg"></span>

                </form>
            </div>

            <div class="card card-danger">
                <h4 class="card-title card-title-danger">Security <b>Gateway</b></h4>

                <form id="passwordUpdateForm">

                    <div class="field-grid">
                        <div class="field-group">
                            <label class="nexus-label label-danger">Current Password</label>
                            <input type="password" name="current_password" required class="nexus-input">
                        </div>
                        <div class="field-group">
                            <label class="nexus-label label-danger">New Password</label>
                            <input type="password" name="new_password" required class="nexus-input">
                        </div>
                        <div class="field-group field-group-full">
                            <label class="nexus-label label-danger">Confirm Password</label>
                            <input type="password" name="confirm_password" required class="nexus-input">
                        </div>
                    </div>

                    <button type="submit" id="changePassBtn" class="btn-danger">Update Password</button>

                </form>

                <div id="passMsg" class="feedback-msg"></div>
            </div>

        </div>

    </div>

</div>

<style>
.module-entrance {
    animation: nexusFadeIn .5s ease-out forwards;
}

.section-heading {
    color: #1e293b;
    font-weight: 800;
    margin-bottom: 2rem;
}

.profile-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
}

.card {
    background: #fff;
    padding: 30px;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
}

.profile-sidebar {
    text-align: center;
    height: fit-content;
}

.avatar-wrap {
    position: relative;
    display: inline-block;
}

.avatar-img {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #f8fafc;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1);
}

.avatar-edit-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 38px;
    height: 38px;
    background: #3b82f6;
    color: #fff;
    border: 3px solid #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: .9rem;
    transition: .3s cubic-bezier(.4, 0, .2, 1);
}

.avatar-edit-btn:hover {
    transform: scale(1.1);
    background: #2563eb;
}

.student-name {
    margin-top: 20px;
    color: #1e293b;
    font-weight: 800;
    font-size: 1.2rem;
}

.student-id-badge {
    background: #eff6ff;
    color: #3b82f6;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 800;
    letter-spacing: .5px;
}

.info-stack {
    margin-top: 30px;
    display: grid;
    gap: 12px;
}

.info-box {
    padding: 14px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 18px;
    text-align: left;
}

.info-box small {
    color: #94a3b8;
    font-weight: 700;
    font-size: .6rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.info-box div {
    font-weight: 700;
    color: #475569;
    font-size: .95rem;
}

.profile-main {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.card {
    padding: 40px;
}

.card-title {
    color: #1e293b;
    font-weight: 800;
    font-size: 1.1rem;
    margin-bottom: 25px;
}

.hidden-file-input {
    display: none;
}

.field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

.field-group-full {
    margin-bottom: 30px;
}

.locked-fields {
    opacity: .6;
    margin-bottom: 30px;
}

.nexus-label {
    display: block;
    font-size: .65rem;
    font-weight: 800;
    color: #64748b;
    margin-bottom: 8px;
    letter-spacing: .5px;
    text-transform: uppercase;
}

.nexus-input,
.nexus-input-locked {
    width: 100%;
    padding: 14px;
    border-radius: 15px;
    border: 1px solid #e2e8f0;
    font-size: .95rem;
    font-weight: 600;
    transition: .2s;
}

.nexus-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, .1);
}

.nexus-input-locked {
    background: #f8fafc;
    cursor: not-allowed;
}

.address-textarea {
    height: 100px;
    resize: none;
}

.save-btn {
    background: #1e293b;
    color: #fff;
    border: none;
    padding: 15px 40px;
    border-radius: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
    transition: .3s;
}

.save-btn:hover {
    background: #0f172a;
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1);
}

.feedback-msg {
    margin-left: 15px;
    font-weight: 700;
    font-size: .85rem;
}

.card-danger {
    background: #fff1f2;
    border-color: #fecdd3;
}

.card-title-danger {
    color: #9f1239;
}

.label-danger {
    color: #9f1239;
}

.btn-danger {
    margin-top: 15px;
    background: #e11d48;
    color: #fff;
    border: none;
    padding: 14px 25px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
}

#passMsg {
    margin-top: 15px;
}

@media (max-width: 768px) {
    .profile-grid,
    .field-grid {
        grid-template-columns: 1fr !important;
    }

    .save-btn {
        width: 100%;
    }
}
</style>

<script>
function previewImage(input) {
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

async function handleFormSubmit(form, endpoint, btn, msgEl, busyText, idleText, onSuccess) {
    btn.disabled = true;
    btn.innerHTML = busyText;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: new FormData(form),
        });
        const data = await response.json();

        if (onSuccess) {
            onSuccess(data, msgEl);
        }
    } catch (err) {
        msgEl.innerHTML = 'Connection Error';
        msgEl.style.color = '#dc2626';
    }

    btn.disabled = false;
    btn.innerHTML = idleText;
}

// Profile update
const profileForm = document.getElementById('profileUpdateForm');

if (profileForm) {
    profileForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const btn = document.getElementById('saveProfileBtn');
        const msg = document.getElementById('profileMsg');

        handleFormSubmit(
            profileForm,
            'api/update_profile.php',
            btn,
            msg,
            'SAVING...',
            'SAVE CHANGES',
            (data, msgEl) => {
                msgEl.innerHTML = data.message;
                msgEl.style.color = data.status === 'success' ? '#16a34a' : '#dc2626';
            }
        );
    });
}

// Password update
const passwordForm = document.getElementById('passwordUpdateForm');

if (passwordForm) {
    passwordForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const btn = document.getElementById('changePassBtn');
        const msg = document.getElementById('passMsg');

        handleFormSubmit(
            passwordForm,
            'api/change_password.php',
            btn,
            msg,
            'UPDATING...',
            'UPDATE PASSWORD',
            (data, msgEl) => {
                msgEl.innerHTML = data.message;
                msgEl.style.color = data.success ? '#16a34a' : '#dc2626';

                if (data.success) {
                    passwordForm.reset();
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            }
        );
    });
}
</script>