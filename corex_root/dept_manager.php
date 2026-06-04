<?php
require_once('../includes/db_connect.php');

/**
 * EDULYNTRIX CORE X - DEPARTMENT MANAGER
 * Handles Global Infrastructure Nodes
 */

// 1. INITIALIZE (ADD) NODE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['dept_name']);
    $head = trim($_POST['dept_head']);
    $cap  = (int)$_POST['capacity'];
    $stat = $_POST['status'];

    try {
        $stmt = $pdo->prepare("INSERT INTO departments (dept_name, dept_head, capacity, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $head, $cap, $stat]);
        header("Location: layout.php?page=departments&msg=initialized");
        exit(); 
    } catch (PDOException $e) {
        die("Critical Failure: " . $e->getMessage());
    }
}

// 2. UPDATE (EDIT) NODE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id   = (int)$_POST['dept_id'];
    $head = trim($_POST['dept_head']);
    $cap  = (int)$_POST['capacity'];
    $stat = $_POST['status'];

    try {
        // Update the Authority Head, Capacity, and Status
        $stmt = $pdo->prepare("UPDATE departments SET dept_head = ?, capacity = ?, status = ? WHERE id = ?");
        $stmt->execute([$head, $cap, $stat, $id]);
        header("Location: layout.php?page=departments&msg=updated");
        exit();
    } catch (PDOException $e) {
        die("Update Failed: " . $e->getMessage());
    }
}

// 3. PURGE (DELETE) NODE
if (isset($_GET['action']) && $_GET['action'] == 'purge') {
    $id = (int)$_GET['id']; // Cast to integer for security
    try {
        // Optional: In the future, add a check here to prevent purging 
        // if staff/students are still linked to this ID.
        
        $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: layout.php?page=departments&msg=purged");
        exit();
    } catch (PDOException $e) {
        die("Purge Failed: " . $e->getMessage());
    }
}
?>