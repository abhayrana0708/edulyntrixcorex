<?php
require_once('../includes/db_connect.php');

/**
 * EDULYNTRIX CORE X – STAFF MANAGER (BACKEND PROCESSOR)
 * ─────────────────────────────────────────────────────
 * Handles: add, edit, purge actions for the staff table.
 * FIX 3: Generates staff_id and login_id correctly.
 * FIX 4: Inserts HOD into hod_accounts table as well.
 * Enforces: Single-HOD-per-department governance rule.
 */

session_start();

// ── Auth guard ─────────────────────────────────────────────────
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=unauthorized');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    header('Location: layout.php?page=staff');
    exit();
}

$action = trim($_POST['action']);

// ══════════════════════════════════════════════════════════════
// ACTION: ADD NEW STAFF
// ══════════════════════════════════════════════════════════════
if ($action === 'add') {

    // ── Collect and sanitise inputs ────────────────────────────
    $full_name       = trim($_POST['full_name']       ?? '');
    $email           = trim($_POST['email']           ?? '');
    $phone           = trim($_POST['phone']           ?? '');
    $designation     = trim($_POST['designation']     ?? '');
    $dept_id         = (int)($_POST['dept_id']        ?? 0);
    $department_name = trim($_POST['department_name'] ?? '');  // FIX 2: from hidden field
    $role            = trim($_POST['role']            ?? 'faculty'); // FIX 1: from hidden field
    $joined_date     = trim($_POST['joined_date']     ?? date('Y-m-d'));
    $password        = trim($_POST['password']        ?? '');

    // ── Basic server-side validation ───────────────────────────
    if (!$full_name || !$email || !$designation || !$dept_id || !$password) {
        header('Location: layout.php?page=staff_provision&status=missing_fields');
        exit();
    }

    // Normalise role: only 'hod' and 'faculty' are valid
    $role = strtolower($role);
    if (!in_array($role, ['hod', 'faculty'])) $role = 'faculty';

    try {

        // ── FIX: Single-HOD Enforcement ────────────────────────
        if ($designation === 'HOD') {
            $hod_check = $pdo->prepare(
                "SELECT COUNT(*) FROM staff
                 WHERE dept_id = ? AND designation = 'HOD' AND status = 'Active'"
            );
            $hod_check->execute([$dept_id]);
            if ($hod_check->fetchColumn() > 0) {
                header('Location: layout.php?page=staff_provision&status=hod_conflict');
                exit();
            }
        }

        // ── FIX 3: Generate staff_id and login_id ──────────────
        // Count existing staff to get next sequence number
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM staff");
        $next_seq   = (int)$count_stmt->fetchColumn() + 1;
        $year       = date('Y');

        if ($designation === 'HOD') {
            // HOD pattern: STF-HOD-{seq} / login_id: HOD-{DEPT_CODE}-{seq}
            $staff_id = 'STF-HOD-' . str_pad($next_seq, 3, '0', STR_PAD_LEFT);

            // Derive department code from name (first 2 letters, uppercase)
            $dept_code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $department_name), 0, 2));
            $login_id  = 'HOD-' . $dept_code . '-' . str_pad($next_seq, 2, '0', STR_PAD_LEFT);
        } else {
            // Faculty pattern: STF-{YEAR}-{seq}
            $staff_id = 'STF-' . $year . '-' . str_pad($next_seq, 3, '0', STR_PAD_LEFT);
            $login_id = $staff_id;   // faculty login_id mirrors staff_id
        }

        // ── Insert into staff table ────────────────────────────
        $insert = $pdo->prepare(
            "INSERT INTO staff
             (staff_id, full_name, email, phone, dept_id, department,
              designation, password, status, joined_date, login_id, role)
             VALUES
             (:staff_id, :full_name, :email, :phone, :dept_id, :department,
              :designation, :password, 'Active', :joined_date, :login_id, :role)"
        );
        $insert->execute([
            ':staff_id'    => $staff_id,
            ':full_name'   => $full_name,
            ':email'       => $email,
            ':phone'       => $phone,
            ':dept_id'     => $dept_id,
            ':department'  => $department_name,     // FIX 2
            ':designation' => $designation,
            ':password'    => $password,            // plain for now; use password_hash() in prod
            ':joined_date' => $joined_date,
            ':login_id'    => $login_id,            // FIX 3
            ':role'        => $role,                // FIX 1
        ]);

        // ── FIX 4: If HOD, also insert into hod_accounts table ─
        if ($designation === 'HOD') {
            // hod_id format: HOD-{DEPT_CODE}-{seq}  (matches existing rows)
            $hod_insert = $pdo->prepare(
                "INSERT INTO hod_accounts
                 (hod_id, name, email, password, department, profile_pic, created_at)
                 VALUES
                 (:hod_id, :name, :email, :password, :department, NULL, NOW())"
            );
            $hod_insert->execute([
                ':hod_id'     => $login_id,
                ':name'       => $full_name,
                ':email'      => $email,
                ':password'   => $password,
                ':department' => $department_name,
            ]);
        }

        header('Location: layout.php?page=staff&msg=personnel_authorized');
        exit();

    } catch (PDOException $e) {
        // Duplicate email (SQLSTATE 23000)
        if ($e->getCode() == 23000) {
            header('Location: layout.php?page=staff_provision&status=email_conflict');
            exit();
        }
        // Any other DB error — log server-side, show generic message
        error_log('StaffManager ADD Error: ' . $e->getMessage());
        header('Location: layout.php?page=staff_provision&status=db_error');
        exit();
    }
}

// ══════════════════════════════════════════════════════════════
// ACTION: EDIT STAFF
// ══════════════════════════════════════════════════════════════
if ($action === 'edit') {

    $id          = (int)($_POST['staff_db_id']     ?? 0);
    $full_name   = trim($_POST['full_name']         ?? '');
    $email       = trim($_POST['email']             ?? '');
    $phone       = trim($_POST['phone']             ?? '');
    $designation = trim($_POST['designation']       ?? '');
    $dept_id     = (int)($_POST['dept_id']          ?? 0);
    $dept_name   = trim($_POST['department_name']   ?? '');
    $joined_date = trim($_POST['joined_date']       ?? '');
    $status      = trim($_POST['status']            ?? 'Active');

    if (!$id) {
        header('Location: layout.php?page=staff&status=invalid_id');
        exit();
    }

    try {
        $update = $pdo->prepare(
            "UPDATE staff SET
               full_name   = :full_name,
               email       = :email,
               phone       = :phone,
               designation = :designation,
               dept_id     = :dept_id,
               department  = :dept_name,
               joined_date = :joined_date,
               status      = :status
             WHERE id = :id"
        );
        $update->execute([
            ':full_name'   => $full_name,
            ':email'       => $email,
            ':phone'       => $phone,
            ':designation' => $designation,
            ':dept_id'     => $dept_id,
            ':dept_name'   => $dept_name,
            ':joined_date' => $joined_date,
            ':status'      => $status,
            ':id'          => $id,
        ]);

        header('Location: layout.php?page=staff&msg=record_updated');
        exit();

    } catch (PDOException $e) {
        error_log('StaffManager EDIT Error: ' . $e->getMessage());
        header('Location: layout.php?page=staff&status=update_failed');
        exit();
    }
}

// ══════════════════════════════════════════════════════════════
// ACTION: PURGE (DELETE) STAFF
// ══════════════════════════════════════════════════════════════
if ($action === 'purge') {

    $id = (int)($_POST['staff_db_id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        header('Location: layout.php?page=staff&status=invalid_id');
        exit();
    }

    try {
        // Fetch staff info before deleting (to also remove from hod_accounts if HOD)
        $fetch = $pdo->prepare("SELECT designation, login_id FROM staff WHERE id = ?");
        $fetch->execute([$id]);
        $staff = $fetch->fetch(PDO::FETCH_ASSOC);

        if (!$staff) {
            header('Location: layout.php?page=staff&status=not_found');
            exit();
        }

        $pdo->beginTransaction();

        // Remove from hod_accounts first if this is an HOD
        if ($staff['designation'] === 'HOD' && !empty($staff['login_id'])) {
            $pdo->prepare("DELETE FROM hod_accounts WHERE hod_id = ?")
                ->execute([$staff['login_id']]);
        }

        $pdo->prepare("DELETE FROM staff WHERE id = ?")->execute([$id]);

        $pdo->commit();

        header('Location: layout.php?page=staff&msg=personnel_purged');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('StaffManager PURGE Error: ' . $e->getMessage());
        header('Location: layout.php?page=staff&status=purge_failed');
        exit();
    }
}

// Fallback for unknown action
header('Location: layout.php?page=staff');
exit();