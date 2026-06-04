<?php
require_once('../includes/db_connect.php');

/**
 * EDULYNTRIX CORE X - TEMPORAL MANAGER
 * Handles Institutional Timeline Cycles
 */

// 1. INITIALIZE (ADD) SESSION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name  = trim($_POST['session_name']);
    $start = $_POST['start_date'];
    $end   = $_POST['end_date'];
    $stat  = $_POST['status'];
    
    // Logic: Active status forces Master status
    $is_current = ($stat == 'Active') ? 1 : 0;

    try {
        $pdo->beginTransaction();

        // If new session is Active, archive existing master
        if ($is_current == 1) {
            $pdo->exec("UPDATE academic_sessions SET is_current = 0, status = 'Concluded' WHERE is_current = 1");
        }

        $stmt = $pdo->prepare("INSERT INTO academic_sessions (session_name, start_date, end_date, status, is_current) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $start, $end, $stat, $is_current]);
        
        $pdo->commit();
        header("Location: layout.php?page=sessions&msg=timeline_synced");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Temporal Fragment Error: " . $e->getMessage());
    }
}

// 2. MODIFY (UPDATE) SESSION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id    = $_POST['session_id'];
    $name  = trim($_POST['session_name']); // Captured from 'edit_name' in UI
    $start = $_POST['start_date'];
    $end   = $_POST['end_date'];
    $stat  = $_POST['status'];
    $is_current = ($stat == 'Active') ? 1 : 0;

    try {
        $pdo->beginTransaction();

        // Supreme Logic: If this modified session is now Active, dethrone ALL others
        if ($is_current == 1) {
            $pdo->exec("UPDATE academic_sessions SET is_current = 0, status = 'Concluded' WHERE id != $id");
        }

        $stmt = $pdo->prepare("UPDATE academic_sessions SET session_name = ?, start_date = ?, end_date = ?, status = ?, is_current = ? WHERE id = ?");
        $stmt->execute([$name, $start, $end, $stat, $is_current, $id]);
        
        $pdo->commit();
        header("Location: layout.php?page=sessions&msg=timeline_updated");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Modification Failed: " . $e->getMessage());
    }
}

// 3. ACTIVATE MASTER (QUICK TOGGLE)
if (isset($_GET['action']) && $_GET['action'] == 'activate') {
    $id = (int)$_GET['id'];
    try {
        $pdo->beginTransaction();
        
        // Dethrone current master
        $pdo->exec("UPDATE academic_sessions SET is_current = 0, status = 'Concluded'");
        
        // Crown new master
        $stmt = $pdo->prepare("UPDATE academic_sessions SET is_current = 1, status = 'Active' WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        header("Location: layout.php?page=sessions&msg=master_clock_synced");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Activation Failed: " . $e->getMessage());
    }
}

// 4. PURGE (DELETE) SESSION
if (isset($_GET['action']) && $_GET['action'] == 'purge') {
    $id = (int)$_GET['id'];
    try {
        // Prevent purging the Active Master session to protect institutional core
        $check = $pdo->prepare("SELECT is_current FROM academic_sessions WHERE id = ?");
        $check->execute([$id]);
        $session = $check->fetch();

        if ($session && $session['is_current'] == 1) {
            header("Location: layout.php?page=sessions&msg=purge_denied_active_master");
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM academic_sessions WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: layout.php?page=sessions&msg=fragment_purged");
        exit();
    } catch (PDOException $e) {
        die("Purge Failed: " . $e->getMessage());
    }
}