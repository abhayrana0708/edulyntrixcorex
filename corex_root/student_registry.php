<?php
/**
 * EDULYNTRIX CORE X - GLOBAL STUDENT REGISTRY
 * Theme: Nexus Light | Version 9.1.0 (All Bugs Fixed)
 */

require_once('../includes/db_connect.php');

try {
    $query = "SELECT s.*, d.dept_name
              FROM students s
              LEFT JOIN departments d ON s.dept_id = d.id
              ORDER BY s.id DESC";
    $students = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='padding:40px;text-align:center;font-family:sans-serif;'>
            <h2 style='color:#ef4444;'>Nexus Registry Offline</h2>
            <p>System Error: " . htmlspecialchars($e->getMessage()) . "</p>
         </div>");
}

function countStatus($arr, $status) {
    return count(array_filter($arr, fn($s) => strtolower($s['status'] ?? '') === strtolower($status)));
}

// FIX 10: Two-letter initials (First + Last name)
function getInitials($name) {
    $parts = explode(' ', trim($name ?? 'S'));
    $first = strtoupper(substr($parts[0] ?? 'S', 0, 1));
    $last  = isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '';
    return $first . $last;
}

// FIX 2: Resolve profile image using server-side __DIR__ path
function resolveProfilePath($filename) {
    if (empty($filename)) return null;
    // Try both possible upload folder locations
    $paths = [
        __DIR__ . '/../uploads/profiles/' . $filename,
        __DIR__ . '/../../uploads/profiles/' . $filename,
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) return '../uploads/profiles/' . $filename;
    }
    return null;
}
?>

<!-- FIX 1: Load FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="nexus-container animate-scale-in">

    <!-- Header -->
    <div class="nexus-header">
        <div class="title-block">
            <h1>Global <span class="text-blue">Registry</span></h1>
            <p class="subtitle">Nexus Light &nbsp;|&nbsp; System Asset Oversight</p>
        </div>
        <div class="action-block">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="nexusSearch"
                       placeholder="Search ID, Name or Dept..."
                       oninput="filterNexus()">
                <!-- FIX 11: Clear button -->
                <button class="search-clear" id="searchClear" onclick="clearSearch()" title="Clear search">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <button class="add-stu-btn" onclick="window.location.href='layout.php?page=student_enroll'">
                <i class="fa-solid fa-user-plus"></i> New Enrollment
            </button>
        </div>
    </div>

    <!-- FIX 4 & 8: Stats updated dynamically by JS; FIX 8: correct labels -->
    <div class="nexus-stats">
        <div class="stat-node">
            <i class="fa-solid fa-users" style="color:#3b82f6;"></i>
            &nbsp;<strong>Total:</strong>
            <span id="stat-total"><?= count($students); ?></span>
        </div>
        <div class="stat-node">
            <span class="dot active"></span>
            <strong>Active:</strong>
            <span id="stat-active"><?= countStatus($students, 'Active'); ?></span>
        </div>
        <div class="stat-node">
            <!-- FIX 8: was counting 'Inactive' but labelled 'Pending' -->
            <span class="dot inactive"></span>
            <strong>Inactive:</strong>
            <span id="stat-inactive"><?= countStatus($students, 'Inactive'); ?></span>
        </div>
        <div class="stat-node">
            <span class="dot pending"></span>
            <strong>Pending:</strong>
            <span id="stat-pending"><?= countStatus($students, 'Pending'); ?></span>
        </div>
    </div>

    <!-- Registry Grid -->
    <div class="nexus-grid" id="registryGrid">
        <?php foreach ($students as $index => $stu):
            $status_val   = $stu['status'] ?? 'Inactive';
            $status_class = strtolower($status_val);

            // FIX 2: server-safe image resolution
            $profile_src  = resolveProfilePath($stu['profile_pic'] ?? '');

            // Fix for the 1970 Date Bug (original)
            $raw_date       = $stu['enrollment_date'] ?? null;
            $formatted_date = ($raw_date && $raw_date !== '0000-00-00')
                ? date('d M Y', strtotime($raw_date))
                : 'Pending';

            // FIX 10: two-letter initials
            $initials = getInitials($stu['full_name'] ?? 'S');

            // FIX 7: staggered animation delay per card (max 600ms)
            $delay = min($index * 60, 600);
        ?>
        <div class="student-node animate-fade-in"
             style="animation-delay: <?= $delay ?>ms;"
             data-search-key="<?= htmlspecialchars(strtolower(
                 ($stu['full_name']   ?? '') . ' ' .
                 ($stu['student_id'] ?? '') . ' ' .
                 ($stu['dept_name']  ?? '') . ' ' .
                 ($status_val)
             )); ?>"
             data-status="<?= $status_class ?>"
             data-id="<?= (int)($stu['id'] ?? 0) ?>"
             data-student-id="<?= htmlspecialchars($stu['student_id'] ?? '') ?>">

            <div class="node-header">
                <span class="status-pill <?= $status_class ?>">
                    <?= htmlspecialchars($status_val) ?>
                </span>
                <span class="node-id">
                    <?= htmlspecialchars($stu['student_id'] ?? 'NO_ID') ?>
                </span>
            </div>

            <div class="node-body">
                <div class="avatar-circle">
                    <?php if ($profile_src): ?>
                        <img src="<?= htmlspecialchars($profile_src) ?>"
                             alt="<?= htmlspecialchars($stu['full_name'] ?? '') ?>"
                             style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                        <?= $initials ?>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($stu['full_name'] ?? 'Unknown Student') ?></h3>
                <p class="dept-tag">
                    <i class="fa-solid fa-building-columns" style="font-size:0.7rem;margin-right:4px;"></i>
                    <?= htmlspecialchars($stu['dept_name'] ?? 'Unassigned Node') ?>
                </p>
                <?php if (!empty($stu['email'])): ?>
                <p class="email-tag">
                    <i class="fa-solid fa-envelope" style="font-size:0.7rem;margin-right:4px;opacity:0.6;"></i>
                    <?= htmlspecialchars($stu['email']) ?>
                </p>
                <?php endif; ?>
            </div>

            <div class="node-footer">
                <div class="meta">
                    <span>Sem: <strong><?= htmlspecialchars($stu['current_semester'] ?? '1st') ?></strong></span>
                    <br>
                    <span>Joined: <strong><?= $formatted_date ?></strong></span>
                </div>
                <!-- FIX 3: onclick handlers on edit/delete buttons -->
                <div class="node-controls">
                    <button class="icon-btn edit" title="Edit Profile"
                            onclick="editStudent(<?= (int)($stu['id'] ?? 0) ?>, '<?= htmlspecialchars($stu['student_id'] ?? '', ENT_QUOTES) ?>')">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="icon-btn delete" title="Wipe Node"
                            onclick="deleteStudent(<?= (int)($stu['id'] ?? 0) ?>, '<?= htmlspecialchars($stu['full_name'] ?? '', ENT_QUOTES) ?>')">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- FIX 5: Empty state (shown/hidden by JS) -->
    <div id="emptyState" class="empty-state" style="display:none;">
        <div class="empty-icon"><i class="fa-solid fa-user-slash"></i></div>
        <h3>No Students Found</h3>
        <p>No records match your search query. Try a different name, ID, or department.</p>
        <button class="add-stu-btn" onclick="clearSearch()" style="margin-top:16px;">
            <i class="fa-solid fa-rotate-left"></i> Clear Search
        </button>
    </div>

</div>

<!-- ── Edit Modal ──────────────────────────────────────────── -->
<div id="editModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square"></i> Edit Student</h3>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <p class="modal-note">
                Editing <strong id="editStudentId" style="color:var(--n-blue);"></strong>
            </p>
            <p style="color:#64748b;font-size:0.8rem;margin-top:6px;">
                Full profile editing is available in the student detail panel.
            </p>
        </div>
        <div class="modal-footer">
            <button class="add-stu-btn" id="editRedirectBtn">
                <i class="fa-solid fa-arrow-right"></i> Open Full Editor
            </button>
            <button class="cancel-btn" onclick="closeModal('editModal')">Cancel</button>
        </div>
    </div>
</div>

<style>
/* ── NEXUS LIGHT SYSTEM STYLES ─────────────────────────── */
:root {
    --n-blue:    #3b82f6;
    --n-blue-dk: #2563eb;
    --n-bg:      #f1f5f9;
    --n-card:    #ffffff;
    --n-text:    #1e293b;
    --n-border:  #e2e8f0;
    --n-muted:   #64748b;
    --n-radius:  16px;
    --n-shadow:  0 1px 3px rgba(0,0,0,0.07), 0 4px 12px rgba(0,0,0,0.05);
}

.nexus-container {
    padding: 30px;
    color: var(--n-text);
    background: var(--n-bg);
    min-height: 100%;
    font-family: 'Space Grotesk', 'Inter', sans-serif;
}
.text-blue  { color: var(--n-blue); }
.subtitle   { color: var(--n-muted); font-size: 0.85rem; margin-top: 4px; }

/* ── Header ─────────────────────────────────────────────── */
.nexus-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}
.nexus-header h1 {
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -1px;
    margin: 0;
    line-height: 1.1;
}

.action-block {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

/* ── Search ─────────────────────────────────────────────── */
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
#nexusSearch {
    padding: 11px 36px 11px 40px;
    border-radius: 12px;
    border: 1.5px solid var(--n-border);
    width: 280px;
    font-size: 0.875rem;
    outline: none;
    transition: all 0.25s;
    background: #fff;
    color: var(--n-text);
    font-family: inherit;
}
#nexusSearch:focus {
    border-color: var(--n-blue);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
}
.search-icon {
    position: absolute;
    left: 13px;
    color: #94a3b8;
    font-size: 0.85rem;
    pointer-events: none;
    z-index: 1;
}
/* FIX 11: clear button */
.search-clear {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    color: #94a3b8;
    font-size: 0.8rem;
    padding: 2px 4px;
    display: none;
    transition: color 0.2s;
    z-index: 1;
}
.search-clear:hover  { color: var(--n-text); }
.search-clear.visible { display: flex; }

.add-stu-btn {
    background: var(--n-blue);
    color: #fff;
    border: none;
    padding: 11px 22px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.25s;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: inherit;
    white-space: nowrap;
}
.add-stu-btn:hover {
    background: var(--n-blue-dk);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(59,130,246,0.3);
}

/* ── Stats ──────────────────────────────────────────────── */
.nexus-stats {
    display: flex;
    gap: 14px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.stat-node {
    background: var(--n-card);
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid var(--n-border);
    font-size: 0.875rem;
    box-shadow: var(--n-shadow);
    display: flex;
    align-items: center;
    gap: 6px;
}
.dot {
    width: 9px; height: 9px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.dot.active   { background: #10b981; box-shadow: 0 0 6px #10b981; }
.dot.inactive { background: #f59e0b; box-shadow: 0 0 6px #f59e0b; }
.dot.pending  { background: #8b5cf6; box-shadow: 0 0 6px #8b5cf6; }

/* ── Cards Grid ─────────────────────────────────────────── */
.nexus-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 22px;
}

.student-node {
    background: var(--n-card);
    border: 1.5px solid var(--n-border);
    border-radius: var(--n-radius);
    padding: 22px;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    position: relative;
    overflow: hidden;
    box-shadow: var(--n-shadow);
}
.student-node::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--n-blue);
    opacity: 0;
    transition: opacity 0.3s;
}
.student-node:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px -8px rgba(59,130,246,0.15);
    border-color: var(--n-blue);
}
.student-node:hover::before { opacity: 1; }

/* ── Card Header ────────────────────────────────────────── */
.node-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* FIX 6: status-pill for all three states */
.status-pill {
    font-size: 0.65rem;
    font-weight: 800;
    padding: 4px 11px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
.status-pill.active   { background: #dcfce7; color: #166534; }
.status-pill.inactive { background: #fef3c7; color: #92400e; }
.status-pill.pending  { background: #ede9fe; color: #5b21b6; }

.node-id {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.7rem;
    color: var(--n-muted);
    font-weight: 600;
    background: #f8fafc;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid var(--n-border);
}

/* ── Card Body ──────────────────────────────────────────── */
.node-body {
    text-align: center;
    padding-bottom: 18px;
    border-bottom: 1px solid #f1f5f9;
}
.avatar-circle {
    width: 78px; height: 78px;
    background: #eff6ff;
    color: var(--n-blue);
    border-radius: 50%;
    margin: 0 auto 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.6rem;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px var(--n-border);
    overflow: hidden;
}
.node-body h3 {
    margin: 0 0 6px;
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dept-tag {
    color: var(--n-blue);
    font-size: 0.8rem;
    font-weight: 600;
    margin: 0 0 4px;
}
.email-tag {
    color: var(--n-muted);
    font-size: 0.72rem;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Card Footer ────────────────────────────────────────── */
.node-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
}
.meta {
    font-size: 0.75rem;
    color: var(--n-muted);
    line-height: 1.6;
}
.meta strong { color: var(--n-text); }

.node-controls { display: flex; gap: 8px; }
.icon-btn {
    border: none;
    width: 34px; height: 34px;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-btn.edit   { background: #eff6ff; color: var(--n-blue); }
.icon-btn.edit:hover   { background: var(--n-blue); color: #fff; transform: scale(1.1); }
.icon-btn.delete { background: #fff1f2; color: #f43f5e; }
.icon-btn.delete:hover { background: #f43f5e; color: #fff; transform: scale(1.1); }

/* ── Empty State (FIX 5) ────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: var(--n-muted);
}
.empty-icon {
    font-size: 3.5rem;
    color: #cbd5e1;
    margin-bottom: 20px;
}
.empty-state h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--n-text);
    margin-bottom: 8px;
}
.empty-state p { font-size: 0.875rem; max-width: 320px; margin: 0 auto; }

/* ── Modal ──────────────────────────────────────────────── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,0.5);
    backdrop-filter: blur(4px);
    z-index: 9000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeOverlay 0.2s ease;
}
@keyframes fadeOverlay { from{opacity:0} to{opacity:1} }

.modal-box {
    background: var(--n-card);
    border-radius: 18px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.2);
    overflow: hidden;
    animation: modalIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes modalIn {
    from { transform: scale(0.92) translateY(16px); opacity: 0; }
    to   { transform: scale(1) translateY(0);       opacity: 1; }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--n-border);
}
.modal-header h3 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--n-text);
}
.modal-close {
    background: #f1f5f9;
    border: none;
    width: 30px; height: 30px;
    border-radius: 8px;
    cursor: pointer;
    color: var(--n-muted);
    font-size: 0.85rem;
    transition: all 0.2s;
    display: flex; align-items: center; justify-content: center;
}
.modal-close:hover { background: #f43f5e; color: #fff; }

.modal-body   { padding: 20px 24px; }
.modal-note   { font-size: 0.875rem; font-weight: 600; color: var(--n-text); margin: 0; }
.modal-footer {
    padding: 16px 24px 20px;
    display: flex;
    gap: 10px;
    border-top: 1px solid var(--n-border);
}
.cancel-btn {
    background: #f1f5f9;
    color: var(--n-muted);
    border: none;
    padding: 11px 22px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.cancel-btn:hover { background: #e2e8f0; color: var(--n-text); }

/* ── Animations ─────────────────────────────────────────── */
@keyframes scaleIn {
    from { transform: scale(0.97); opacity: 0; }
    to   { transform: scale(1);    opacity: 1; }
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0);    }
}
.animate-scale-in { animation: scaleIn 0.4s ease-out; }
.animate-fade-in  { animation: fadeIn 0.5s ease-out both; }
</style>

<script>
// FIX 4 & 8: filterNexus updates all four stat counters
function filterNexus() {
    const input     = document.getElementById('nexusSearch').value.toLowerCase().trim();
    const cards     = document.querySelectorAll('.student-node');
    const clearBtn  = document.getElementById('searchClear');
    const emptyEl   = document.getElementById('emptyState');

    // FIX 11: show/hide clear button
    clearBtn.classList.toggle('visible', input.length > 0);

    let visible = 0, active = 0, inactive = 0, pending = 0;

    cards.forEach(card => {
        const key    = card.getAttribute('data-search-key') || '';
        const status = card.getAttribute('data-status') || '';
        const match  = key.includes(input);

        card.style.display = match ? '' : 'none';

        if (match) {
            visible++;
            if (status === 'active')   active++;
            if (status === 'inactive') inactive++;
            if (status === 'pending')  pending++;
        }
    });

    // Update counters
    document.getElementById('stat-total').textContent   = visible;
    document.getElementById('stat-active').textContent   = active;
    document.getElementById('stat-inactive').textContent = inactive;
    document.getElementById('stat-pending').textContent  = pending;

    // FIX 5: show/hide empty state
    emptyEl.style.display = (visible === 0 && input.length > 0) ? 'block' : 'none';
}

// FIX 11: clear search
function clearSearch() {
    document.getElementById('nexusSearch').value = '';
    filterNexus();
}

// FIX 3: Edit with modal
function editStudent(id, studentId) {
    const modal   = document.getElementById('editModal');
    const label   = document.getElementById('editStudentId');
    const btn     = document.getElementById('editRedirectBtn');

    label.textContent = studentId || ('ID #' + id);
    btn.onclick = () => {
        window.location.href = 'layout.php?page=student_edit&id=' + id;
    };
    modal.style.display = 'flex';
}

// FIX 9: Delete with confirm dialog
function deleteStudent(id, name) {
    const confirmed = confirm(
        `⚠ WIPE STUDENT NODE?\n\nName: ${name}\n\nThis action is permanent and cannot be undone.`
    );
    if (!confirmed) return;

    // Send DELETE request via fetch
    fetch('../includes/student_manager.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Animate card out
            const card = document.querySelector(`.student-node[data-id="${id}"]`);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity    = '0';
                card.style.transform  = 'scale(0.9)';
                setTimeout(() => { card.remove(); filterNexus(); }, 300);
            }
        } else {
            alert('Delete failed: ' + (data.message || 'Unknown error.'));
        }
    })
    .catch(() => alert('Network error. Please try again.'));
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Close modal on overlay click
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal('editModal');
});

// Keyboard: Esc closes any open modal
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal('editModal');
});
</script>