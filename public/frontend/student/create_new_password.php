<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = $_GET['token'] ?? '';

if (
    empty($token)
    ||
    !isset($_SESSION['reset_token'])
    ||
    $token !== $_SESSION['reset_token']
) {

    die('Invalid or Expired Reset Session.');
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

<title>Create New Password</title>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#f8fafc;
}

.card{

    width:100%;
    max-width:500px;

    background:#fff;

    padding:40px;

    border-radius:20px;

    box-shadow:
    0 20px 40px rgba(0,0,0,.08);
}

h2{

    margin-bottom:10px;

    font-size:2rem;

    color:#1e293b;
}

p{

    color:#64748b;

    margin-bottom:25px;
}

input{

    width:100%;

    padding:14px;

    margin-bottom:15px;

    border:1px solid #cbd5e1;

    border-radius:10px;

    outline:none;
}

button{

    width:100%;

    border:none;

    padding:14px;

    border-radius:10px;

    background:#4f46e5;

    color:#fff;

    font-weight:700;

    cursor:pointer;
}

button:hover{

    background:#4338ca;
}

</style>

</head>

<body>

<div class="card">

<h2>Create New Password</h2>

<p>
Choose a strong password for your account.
</p>

<form
    action="process_new_password.php"
    method="POST"
    id="passwordForm"
>

<input
    type="hidden"
    name="token"
    value="<?= htmlspecialchars($token) ?>"
>

<input
    type="password"
    name="password"
    id="password"
    placeholder="New Password"
    required
>

<input
    type="password"
    id="confirm"
    placeholder="Confirm Password"
    required
>

<button type="submit">
Update Password
</button>

</form>

</div>

<script>

document
.getElementById('passwordForm')
.addEventListener(
'submit',
function(e){

    const pass =
        document.getElementById(
            'password'
        ).value;

    const confirm =
        document.getElementById(
            'confirm'
        ).value;

    if(pass !== confirm){

        e.preventDefault();

        alert(
            'Passwords do not match.'
        );
    }

    if(pass.length < 8){

        e.preventDefault();

        alert(
            'Password must be at least 8 characters.'
        );
    }

});

</script>

</body>
</html>