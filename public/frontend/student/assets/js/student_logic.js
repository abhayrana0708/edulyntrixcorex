/**
 * EDULYNTRIX COREX - NEXUS LOGIC ENGINE (v2.2)
 * Optimized for: Dynamic Module Sync & Global Form Handling
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initial Load - Defaulting to 'overview'
    loadModule('overview');
    
    // Attach Search Listener
    const searchInput = document.getElementById('globalSearch');
    if(searchInput) {
        searchInput.addEventListener('keyup', handleSearch);
    }
});

/**
 * CORE MODULE LOADER
 * Fetches dynamic PHP modules based on session and selected year
 */
function loadModule(moduleName, element = null) {
    const stage = document.getElementById('mainContent');
    const yearSelect = document.getElementById('academicYear');
    if(!stage) return;

    // Get the context (Year 1, 2, 3, or 4)
    const selectedYear = yearSelect ? yearSelect.value : 1;

    // 1. UI Feedback: Update Sidebar Active State
    if (element) {
        document.querySelectorAll('.nav-link').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');
    }

    // 2. Nexus Transition: Outward Animation
    stage.style.transition = 'all 0.3s ease';
    stage.style.opacity = '0';
    stage.style.transform = 'translateY(10px) scale(0.98)';

    // 3. AJAX Fetch
    setTimeout(() => {
        fetch(`modules/${moduleName}.php?year=${selectedYear}`)
            .then(response => {
                if (!response.ok) throw new Error('Network Node Offline');
                return response.text();
            })
            .then(html => {
                stage.innerHTML = html;
                
                // --- SCRIPT RE-PARSER ---
                const scripts = stage.querySelectorAll("script");
                scripts.forEach(oldScript => {
                    const newScript = document.createElement("script");
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                // 4. Nexus Transition: Inward Animation
                stage.style.opacity = '1';
                stage.style.transform = 'translateY(0) scale(1)';
            })
            .catch(err => {
                stage.innerHTML = `
                    <div style="padding: 100px; text-align: center; background: white; border-radius: 20px;">
                        <h2 style="color: #ef4444;">Sync Interrupted</h2>
                        <p style="color: #64748b;">The module "${moduleName}" could not be reached.</p>
                        <button onclick="loadModule('${moduleName}')" style="margin-top:20px; padding:10px 20px; cursor:pointer;">Retry Connection</button>
                    </div>`;
                stage.style.opacity = '1';
                stage.style.transform = 'translateY(0) scale(1)';
            });
    }, 300);
}

/**
 * GLOBAL LEAVE APPLICATION HANDLER
 */
function NexusLeaveSync() {
    const btn = document.getElementById('nx_submit_btn');
    const form = document.getElementById('nexusLeaveForm');
    
    if(!form) return;

    const startDate = document.getElementById('nx_start').value;
    const reason = document.getElementById('nx_reason').value;

    if(!startDate || !reason) {
        alert("System Warning: Required data nodes (Date/Reason) are empty.");
        return;
    }

    btn.innerText = "TRANSMITTING...";
    btn.disabled = true;

    fetch('api/submit_leave.php', {
        method: 'POST',
        body: new FormData(form)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if(data.success) { loadModule('leave'); }
    })
    .catch(err => {
        alert("Nexus Error: Communication with the API failed.");
    })
    .finally(() => {
        if(btn) {
            btn.innerText = "TRANSMIT APPLICATION";
            btn.disabled = false;
        }
    });
}

/**
 * PROFILE DATA SYNC (FIXED FOR IMAGE UPLOAD)
 */
$(document).on('submit', '#profileUpdateForm', function(e) {
    e.preventDefault();
    const btn = $(this).find('button');
    const msg = $('#profileMsg');

    btn.text('SYNCING...').prop('disabled', true);

    // FIX: Using FormData instead of .serialize() to allow file uploads
    const formData = new FormData(this);

    $.ajax({
        url: 'api/update_profile.php',
        method: 'POST',
        data: formData,
        processData: false, // Required for FormData
        contentType: false, // Required for FormData
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                msg.css('color', '#10b981').text(res.message);
                
                // FIX: If a new picture was uploaded, update all instances in the header/sidebar
                if(res.new_pic) {
                    const newPath = '../../../uploads/profiles/' + res.new_pic;
                    // Targets the top nav avatar, the sidebar avatar, and the preview
                    $('.avatar-box img, .student-profile-node img, #avatarPreview').attr('src', newPath);
                }
            } else {
                msg.css('color', '#ef4444').text(res.message);
            }
        },
        error: function(err) {
            msg.css('color', '#ef4444').text('Critical Connection Error.');
        },
        complete: function() {
            btn.text('SAVE CHANGES').prop('disabled', false);
            setTimeout(() => msg.text(''), 5000);
        }
    });
});

/**
 * SECURITY GATEWAY SYNC
 */
$(document).on('submit', '#passwordUpdateForm', function(e) {
    e.preventDefault();
    const btn = $(this).find('button');
    const msg = $('#passMsg');

    btn.text('SYNCING...').prop('disabled', true);

    $.ajax({
        url: 'api/update_password.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                msg.css('color', '#16a34a').text('✔ Credentials updated.');
                $('#passwordUpdateForm')[0].reset();
            } else {
                msg.css('color', '#e11d48').text('✖ ' + res.message);
            }
        },
        error: function() {
            msg.css('color', '#e11d48').text('✖ Transmission error.');
        },
        complete: function() {
            btn.text('RE-SYNC CREDENTIALS').prop('disabled', false);
        }
    });
});

/**
 * LIVE SEARCH ENGINE, YEAR SWITCHER, & FINANCE HANDLERS (UNTOUCHED)
 */
function switchYearContext() {
    const activeLink = document.querySelector('.nav-link.active');
    if (activeLink) {
        const match = activeLink.getAttribute('onclick').match(/'([^']+)'/);
        if (match) loadModule(match[1], activeLink);
    }
}

function handleSearch() {
    const q = document.getElementById('globalSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.attendance-table tbody tr, .subject-progress-card, .leave-history-table tbody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(q) ? "" : "none";
    });
}

function initiatePayment(feeId) {
    if(confirm("Proceed to secure payment gateway for Transaction ID: " + feeId + "?")) {
        alert("Establishing secure tunnel to Bank Node...");
    }
}