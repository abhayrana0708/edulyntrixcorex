/**
 * EDULYNTRIX CORE X - ADMIN EFFECTS
 * Core Logic: UI Persistence, Modals, and Real-time Clock
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Real-time Clock Protocol
    const updateClock = () => {
        const now = new Date();
        const clockElement = document.getElementById('clock');
        const dateElement = document.getElementById('date');

        if (clockElement) {
            clockElement.innerText = now.toLocaleTimeString('en-US', { 
                hour12: false, 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
        }
        if (dateElement) {
            dateElement.innerText = now.toLocaleDateString('en-GB', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric' 
            }).toUpperCase();
        }
    };
    setInterval(updateClock, 1000);
    updateClock();

    // 2. Profile Dropdown Logic
    const profileTrigger = document.querySelector('.user-profile');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (profileTrigger && dropdownMenu) {
        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('active');
        });

        document.addEventListener('click', () => {
            dropdownMenu.classList.remove('active');
        });
    }

    // 3. Navigation State Management
    const currentPath = window.location.search;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href').includes(currentPath) && currentPath !== "") {
            link.classList.add('active');
        }
    });
});

/** Modal System Controllers **/

function openNodeModal() {
    const modal = document.getElementById('nodeModal');
    modal.style.display = 'flex';
    modal.classList.add('animate-fade-in');
}

function closeNodeModal() {
    document.getElementById('nodeModal').style.display = 'none';
}

function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_name').value = data.dept_name;
    document.getElementById('edit_head').value = data.dept_head;
    document.getElementById('edit_capacity').value = data.capacity;
    document.getElementById('edit_status').value = data.status;
    
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
    modal.classList.add('animate-fade-in');
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Global Click-to-Close for Modals
window.onclick = function(event) {
    const nodeModal = document.getElementById('nodeModal');
    const editModal = document.getElementById('editModal');
    if (event.target == nodeModal) closeNodeModal();
    if (event.target == editModal) closeEditModal();
};