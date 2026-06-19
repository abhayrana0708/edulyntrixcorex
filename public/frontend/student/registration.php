<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/db_connect.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $stmt = $pdo->query("
        SELECT id, dept_name
        FROM departments
        WHERE status = 'Active'
        ORDER BY dept_name ASC
    ");

    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $departments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Registration | EdulyntrixCoreX</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Inter',sans-serif;
    background:#f8fafc;
    min-height:100vh;
    color:#1e293b;
}

.mesh-bg{
    position:fixed;
    inset:0;
    background:
    radial-gradient(circle at top left,#dbeafe 0%,#f8fafc 40%);
    z-index:-1;
}

.container{
    width:100%;
    max-width:1100px;
    margin:30px auto;
    padding:20px;
}

.card{
    background:#fff;
    border-radius:24px;
    padding:40px;
    box-shadow:0 10px 40px rgba(0,0,0,.08);
}

.header{
    text-align:center;
    margin-bottom:35px;
}

.header h1{
    font-size:2rem;
    font-weight:800;
    margin-bottom:10px;
}

.header p{
    color:#64748b;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:30px;
}

.section{
    background:#fafafa;
    padding:20px;
    border-radius:18px;
    border:1px solid #e5e7eb;
}

.section-title{
    font-size:14px;
    font-weight:700;
    color:#4f46e5;
    margin-bottom:20px;
    text-transform:uppercase;
}

.row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

input,
select,
textarea{
    width:100%;
    padding:14px;
    border:1px solid #dbe2ea;
    border-radius:12px;
    margin-bottom:15px;
    font-size:14px;
}

textarea{
    resize:none;
}

input:focus,
select:focus,
textarea:focus{
    outline:none;
    border-color:#6366f1;
}

.dp-wrapper{
    text-align:center;
    margin-bottom:30px;
}

.dp-preview{
    width:120px;
    height:120px;
    border-radius:20px;
    border:2px dashed #cbd5e1;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:auto;
    background:#f8fafc;
}

.dp-preview img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.upload-btn{
    display:inline-block;
    margin-top:12px;
    color:#4f46e5;
    font-weight:700;
    cursor:pointer;
}

.notice{
    margin-top:25px;
    background:#fef3c7;
    padding:15px;
    border-radius:12px;
    text-align:center;
    color:#92400e;
    font-size:14px;
}

.submit-btn{
    width:100%;
    margin-top:20px;
    padding:15px;
    border:none;
    border-radius:14px;
    background:#4f46e5;
    color:#fff;
    font-size:15px;
    font-weight:700;
    cursor:pointer;
}

.submit-btn:hover{
    background:#4338ca;
}

@media(max-width:768px){

    .card{
        padding:20px;
    }

    .grid{
        grid-template-columns:1fr;
    }

    .row{
        grid-template-columns:1fr;
    }

    .header h1{
        font-size:1.5rem;
    }
}

</style>

</head>
<body>

<div class="mesh-bg"></div>

<div class="container">

<div class="card">

<div class="header">
    <h1>Student Enrollment</h1>
    <p>Submit your details for HOD approval</p>
</div>

<form
action="../../../includes/register_handler.php"
method="POST"
enctype="multipart/form-data"
id="studentForm"
>

<input
type="hidden"
name="csrf_token"
value="<?= $_SESSION['csrf_token']; ?>"
>

<div class="dp-wrapper">

<div class="dp-preview" id="preview">
    <i class="fa-solid fa-camera fa-2x"></i>
</div>

<label class="upload-btn" for="profile_pic">
    Upload Profile Photo
</label>

<input
type="file"
id="profile_pic"
name="profile_pic"
accept=".jpg,.jpeg,.png,.webp"
hidden
required
>

</div>

<div class="grid">
    <div class="section">

    <div class="section-title">
        Personal Information
    </div>

    <input
        type="text"
        name="full_name"
        placeholder="Full Name"
        required
    >

    <div class="row">

        <input
            type="text"
            name="father_name"
            placeholder="Father Name"
            required
        >

        <input
            type="tel"
            name="father_phone"
            placeholder="Father Phone"
            pattern="[0-9]{10}"
            required
        >

    </div>

    <div class="row">

        <input
            type="text"
            name="mother_name"
            placeholder="Mother Name"
            required
        >

        <input
            type="tel"
            name="mother_phone"
            placeholder="Mother Phone"
            pattern="[0-9]{10}"
            required
        >

    </div>

    <textarea
        name="address"
        rows="4"
        placeholder="Permanent Address"
        required
    ></textarea>

</div>

<div class="section">

    <div class="section-title">
        Account Information
    </div>

    <select
        name="dept_id"
        id="deptSelect"
        required
    >

        <option value="">
            Select Department
        </option>

        <?php foreach($departments as $dept): ?>

            <option
                value="<?= $dept['id']; ?>"
                data-name="<?= htmlspecialchars($dept['dept_name']); ?>"
            >
                <?= htmlspecialchars($dept['dept_name']); ?>
            </option>

        <?php endforeach; ?>

    </select>

    <input
        type="hidden"
        name="branch_name"
        id="branchName"
    >

    <input
        type="email"
        name="email"
        placeholder="Email Address"
        required
    >

    <input
        type="tel"
        name="phone"
        placeholder="Mobile Number"
        pattern="[0-9]{10}"
        required
    >

    <input
        type="password"
        name="password"
        id="password"
        placeholder="Password"
        minlength="8"
        required
    >

    <input
        type="password"
        id="confirm_password"
        placeholder="Confirm Password"
        minlength="8"
        required
    >

</div>

</div>

<div class="notice">
    Registration requests remain pending until approved by HOD.
</div>

<button
    type="submit"
    class="submit-btn"
    id="submitBtn"
>
    Register Student
</button>

</form>

</div>

</div>
<script>

const profileInput = document.getElementById('profile_pic');
const previewBox   = document.getElementById('preview');

profileInput.addEventListener('change', function () {

    const file = this.files[0];

    if (!file) return;

    previewBox.innerHTML =
        `<img src="${URL.createObjectURL(file)}" alt="Preview">`;

});

document
.getElementById('deptSelect')
.addEventListener('change', function () {

    const selected =
        this.options[this.selectedIndex];

    document
    .getElementById('branchName')
    .value =
        selected.getAttribute('data-name') || '';

});

document
.getElementById('studentForm')
.addEventListener('submit', function(e){

    const password =
        document.getElementById('password').value;

    const confirm =
        document.getElementById('confirm_password').value;

    if(password !== confirm){

        e.preventDefault();

        alert(
            'Passwords do not match.'
        );

        return false;
    }

    if(password.length < 8){

        e.preventDefault();

        alert(
            'Password must be at least 8 characters.'
        );

        return false;
    }

    document
    .getElementById('submitBtn')
    .innerHTML =
        '<i class="fa fa-spinner fa-spin"></i> Registering...';

    document
    .getElementById('submitBtn')
    .disabled = true;
});

</script>

</body>
</html>