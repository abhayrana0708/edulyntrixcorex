
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    Reset Password | EdulyntrixCoreX
</title>

<link
href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet"
>

<link
rel="stylesheet"
href="assets/css/student_auth.css"
>

<style>

body{
    margin:0;
    font-family:'Inter',sans-serif;
}

.page-wrapper{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}

.login-card{
    width:100%;
    max-width:500px;
}

</style>

</head>

<body>

<div class="mesh-bg"></div>

<div class="bg-branding">

    Edulyntrix
    <span>CoreX</span>

</div>

<main class="page-wrapper">

<section
    class="login-card"
    id="resetReveal"
>

<header class="login-header">

<p>
Security Recovery
</p>

<h1>

Reset

<b>Password</b>

</h1>

</header>

<form
    id="studentResetForm"
    class="login-form"
>

<div style="margin-bottom:20px;">

<p
style="
font-size:.85rem;
color:#94a3b8;
line-height:1.6;
"
>

Enter your Enrollment Number
and Registered Email Address.

</p>

</div>

<input
type="text"
name="enrollment_no"
placeholder="Enrollment Number"
required
>

<input
type="email"
name="email"
placeholder="Registered Email Address"
required
>

<button
type="submit"
id="resetBtn"
>

Verify Identity

</button>

</form>

<footer class="login-footer">

<p>

Remembered your password?

<a href="student_login.php">

Back to Login

</a>

</p>

</footer>

</section>

</main>

<script>

window.addEventListener('load', () => {

    const reveal =
        document.getElementById(
            'resetReveal'
        );

    if (reveal) {

        reveal.classList.add(
            'active'
        );
    }

});

const resetForm =
    document.getElementById(
        'studentResetForm'
    );

const resetBtn =
    document.getElementById(
        'resetBtn'
    );

resetForm.addEventListener(
    'submit',
    async function(e){

        e.preventDefault();

        resetBtn.disabled = true;

        resetBtn.style.opacity = '.7';

        resetBtn.innerHTML =
            'VERIFYING...';

        const formData =
            new FormData(
                resetForm
            );

        try{

            const response =
                await fetch(
                    'api/update_password.php',
                    {
                        method:'POST',
                        body:formData
                    }
                );

            const data =
                await response.json();

            if(data.success){

                alert(
                    '✅ '
                    +
                    data.message
                );

                window.location.href =
                    data.redirect;

            }else{

                alert(
                    '❌ '
                    +
                    data.message
                );

                resetBtn.disabled = false;

                resetBtn.style.opacity = '1';

                resetBtn.innerHTML =
                    'Verify Identity';
            }

        }catch(error){

            console.error(error);

            alert(
                'Connection Error.'
            );

            resetBtn.disabled = false;

            resetBtn.style.opacity = '1';

            resetBtn.innerHTML =
                'Verify Identity';
        }

    }
);

</script>

</body>

</html>
