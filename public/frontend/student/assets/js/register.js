document.addEventListener("DOMContentLoaded", () => {
    const registerPage = document.getElementById("registerPage");
    const dpInput = document.getElementById('dpInput');
    const dpPreview = document.getElementById('dpPreview');
    const studentForm = document.getElementById('studentForm');

    // Entrance Animation (Nexus Light Style)
    setTimeout(() => {
        if(registerPage) registerPage.classList.add("active");
    }, 100);

    // Profile Picture Preview
    dpInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                dpPreview.innerHTML = `<img src="${e.target.result}" alt="DP Preview" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
                dpPreview.style.border = "2px solid #818cf8";
            };
            reader.readAsDataURL(file);
        }
    });

    // Mobile Verification Simulation
    window.sendMobileOTP = function() {
        const phone = document.getElementById('studentPhone').value;
        if (phone && phone.length === 10) {
            alert(`SMS OTP has been sent to +91 ${phone}`);
        } else {
            alert("Please enter a valid 10-digit mobile number.");
        }
    };

    // Email Verification Simulation
    window.sendEmailOTP = function() {
        const email = document.getElementById('userEmail').value;
        if (email && email.includes("@")) {
            alert(`Institutional OTP sent to: ${email}`);
        } else {
            alert("Please enter a valid institutional email.");
        }
    };

    // Form Submission & Validation
    studentForm.addEventListener("submit", function(e) {
        const p1 = document.getElementById("pass").value;
        const p2 = document.getElementById("confirm").value;
        const dept = document.getElementById("deptSelect").value;

        // 1. Department Selection Check
        if (!dept) {
            e.preventDefault();
            alert("Please select a Department to generate your Enrollment ID.");
            return;
        }

        // 2. Password Format Validation (Matches Abhay@01 requirement)
        // Requires: 1 Uppercase, 1 Number, 1 Special Char, Min 6 characters
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;
        
        if (!passwordRegex.test(p1)) {
            e.preventDefault();
            alert("Security Protocol: Password must contain at least one uppercase letter, one number, and one special character (e.g., Abhay@01).");
            return;
        }

        // 3. Password Match Check
        if (p1 !== p2) {
            e.preventDefault();
            alert("Security Error: Passwords do not match.");
            return;
        }

        // 4. UI Feedback for Submission
        const submitBtn = document.querySelector(".action-btn");
        submitBtn.innerText = "DEPLOYING TO COREX ENGINE...";
        submitBtn.style.opacity = "0.7";
        submitBtn.style.pointerEvents = "none"; // Prevent double-clicking
        
        // Form will now proceed to register_handler.php
    });
});