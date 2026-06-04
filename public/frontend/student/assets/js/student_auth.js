document.addEventListener("DOMContentLoaded", () => {
    // 1. Reveal animation (Nexus Light Entrance)
    const card = document.getElementById("studentReveal");
    if(card) {
        setTimeout(() => {
            card.classList.add("active");
            // Standard transition: opacity 1, transform scale 1
        }, 150);
    }

    // 2. Handle Login Submission
    const loginForm = document.getElementById("studentLoginForm");
    if(loginForm) {
        loginForm.addEventListener("submit", function(e) {
            // We REMOVED e.preventDefault() so the form can reach auth_handler.php
            
            const authBtn = document.getElementById("authBtn");
            
            // Visual feedback during the "Handshake"
            authBtn.innerText = "VERIFYING NODE...";
            authBtn.style.opacity = "0.7";
            authBtn.style.cursor = "wait";
            
            // Disable button to prevent double submission
            // We use a small timeout to ensure the form data is captured before disabling
            setTimeout(() => {
                authBtn.disabled = true;
            }, 50);

            // The browser will now POST the data to:
            // ../../../includes/auth_handler.php
        });
    }
});