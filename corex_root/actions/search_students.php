<?php
session_start();
require_once '../../includes/db_connect.php';

// Smart backend check: only allow requests if user is logged in
if (!isset($_SESSION['student_id']) && !isset($_SESSION['admin_id'])) {
    exit('Unauthorized Access');
}

$search = isset($_GET['query']) ? "%" . $_GET['query'] . "%" : "";

if ($search !== "") {
    try {
        // We use full_name and student_id based on your DESCRIBE results
        $query = "SELECT student_id, full_name, email, current_semester, status 
                  FROM students 
                  WHERE full_name LIKE :term 
                  OR student_id LIKE :term 
                  LIMIT 25";
                  
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':term', $search, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return JSON for the frontend to process
        header('Content-Type: application/json');
        echo json_encode($results);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database node sync failure"]);
    }
}