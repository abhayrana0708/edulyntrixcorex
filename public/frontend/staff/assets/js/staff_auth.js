/**
 * EDULYNTRIX COREX - STAFF AUTHORITY GATEWAY 
 * Version 4.1: Reactive Node Recognition & Silk Transition
 */

document.addEventListener("DOMContentLoaded", () => {
    // 1. Core DOM Nodes
    const card = document.getElementById("staffReveal");
    const staffIdInput = document.getElementById("staffId");
    const roleIndicator = document.getElementById("detectedRole");
    const loginForm = document.getElementById("staffLoginForm");
    const submitBtn = document.getElementById("submitBtn");

    // 2. Entrance Sequence (Silk Animation)
    if (card) {
        requestAnimationFrame(() => {
            setTimeout(() => {
                card.style.opacity = "1";
                card.style.transform = "translateY(0)";
                card.classList.add("active");
            }, 150);
        });
    }

    /** * 3. AUTHORITY PATTERN MATCHER
     * Dynamically identifies HOD, Admin, or Faculty nodes
     */
    const updateRoleUI = (text, color, isActive = true) => {
        if (!roleIndicator) return;
        
        roleIndicator.innerText = text;
        roleIndicator.style.color = color;
        
        if (isActive) {
            roleIndicator.style.letterSpacing = "1.5px";
            roleIndicator.style.textShadow = `0 0 15px ${color}66`;
            roleIndicator.style.opacity = "1";
        } else {
            roleIndicator.style.letterSpacing = "0px";
            roleIndicator.style.textShadow = "none";
            roleIndicator.style.opacity = "0.5";
        }
    };

    const detectIdentity = () => {
        const val = staffIdInput.value.trim().toUpperCase();
        
        // Priority 1: HOD Executive Nodes
        if (val.includes("HOD") || val.startsWith("H-")) {
            updateRoleUI("HOD EXECUTIVE NODE", "#10b981");
        } 
        // Priority 2: System Administrative Nodes
        else if (val.includes("ADM") || val.startsWith("A-")) {
            updateRoleUI("SYSTEM ADMIN NODE", "#f59e0b");
        } 
        // Priority 3: Faculty/Staff Access Nodes
        else if (val.includes("FAC") || val.includes("STF") || val.startsWith("F-")) {
            updateRoleUI("FACULTY ACCESS NODE", "#3b82f6");
        }
        // Fallback: Searching
        else if (val.length > 0) {
            updateRoleUI("IDENTIFYING CORE NODE...", "#94a3b8", true);
        }
        // Empty State
        else {
            updateRoleUI("AWAITING IDENTITY SCAN", "#64748b", false);
        }
    };

    if (staffIdInput) {
        staffIdInput.addEventListener("input", detectIdentity);
        // Catch browser auto-fills
        setTimeout(detectIdentity, 500); 
    }

    /** * 4. SUBMISSION & SILK FEEDBACK
     * Adds the "Supreme Power" initialization effect
     */
    if (loginForm && submitBtn) {
        loginForm.addEventListener("submit", (e) => {
            // Ensure inputs aren't empty
            if (loginForm.checkValidity()) {
                submitBtn.disabled = true;
                
                // Trigger Silk Loading State
                submitBtn.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <span class="pulse-dot"></span>
                        INITIALIZING SESSION...
                    </div>
                `;
                submitBtn.style.letterSpacing = "2px";
                submitBtn.style.opacity = "0.8";
                submitBtn.style.cursor = "wait";

                // Add a slight glow to the card on submit
                card.style.boxShadow = "0 0 50px rgba(16, 185, 129, 0.2)";
            }
        });
    }
});

// Utility: Shake Cleanup (if logic failed from PHP side)
function triggerAccessDenied() {
    const card = document.getElementById("staffReveal");
    if (card) {
        card.classList.add("shake");
        setTimeout(() => card.classList.remove("shake"), 600);
    }
}