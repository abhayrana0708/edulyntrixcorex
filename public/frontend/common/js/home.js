/**
 * EdulyntrixCoreX Unified JS
 * Handles high-fidelity interactions for the Main Landing Page
 */

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Initial Reveal Logic with Staggered Entrance
    const revealElements = () => {
        const elements = document.querySelectorAll("[data-reveal]");
        elements.forEach((el, index) => {
            setTimeout(() => {
                el.classList.add("active");
            }, index * 200); // 200ms stagger for professional feel
        });
    };

    // 2. 3D Tilt & Branding Parallax Effect
    const handleParallax = (e) => {
        const { clientX, clientY } = e;
        const centerX = window.innerWidth / 2;
        const centerY = window.innerHeight / 2;

        // Calculate move offsets for the EdulyntrixCoreX Watermark
        const moveX = (clientX - centerX) / 60;
        const moveY = (clientY - centerY) / 60;

        const branding = document.querySelector(".bg-branding");
        if (branding) {
            // Maintains the absolute centering while adding the parallax offset
            branding.style.transform = `translate(calc(-50% + ${-moveX}px), calc(-50% + ${-moveY}px))`;
        }

        // Apply 3D Perspective Tilt to Portal Cards
        const cards = document.querySelectorAll(".parallax");
        cards.forEach(card => {
            const rect = card.getBoundingClientRect();
            const cardCenterX = rect.left + rect.width / 2;
            const cardCenterY = rect.top + rect.height / 2;
            
            // Subtle rotation calculation
            const rotateX = (clientY - cardCenterY) / 30;
            const rotateY = (cardCenterX - clientX) / 30;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        });
    };

    // 3. Smooth Reset (Returns cards to original state when mouse leaves)
    const resetTilt = () => {
        const cards = document.querySelectorAll(".parallax");
        cards.forEach(card => {
            card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
        });
    };

    // 4. Scroll Intersection Observer for Info Nodes
    const observerOptions = {
        threshold: 0.2,
        rootMargin: "0px 0px -50px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
                observer.unobserve(entry.target); // Reveal only once
            }
        });
    }, observerOptions);

    document.querySelectorAll(".info-item").forEach(item => {
        // Set initial state for observer items
        item.style.opacity = "0";
        item.style.transform = "translateY(20px)";
        item.style.transition = "all 0.8s ease-out";
        observer.observe(item);
    });

    // Event Initialization
    revealElements(); // Trigger entrance
    document.addEventListener("mousemove", handleParallax);
    
    document.querySelectorAll(".parallax").forEach(card => {
        card.addEventListener("mouseleave", resetTilt);
    });
});