<?php
/**
 * EDULYNTRIX CORE X - ADVANCED SCHEDULING ENGINE
 * Features: Room Conflict Check & Faculty Multi-Booking Protection
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. INPUT DATA NORMALIZATION
    $subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
    $day        = trim($_POST['day'] ?? ''); 
    $room       = trim($_POST['room'] ?? '');
    $start      = $_POST['start_time'] ?? '';
    $end        = $_POST['end_time'] ?? '';
    $type       = trim($_POST['lecture_type'] ?? 'Theory');

    if (!$subject_id || !$day || !$room || !$start || !$end) {
        echo json_encode(['status' => 'error', 'message' => 'REQUIRED_FIELDS_MISSING']);
        exit;
    }

    try {
        // 2. RESOLVE DEPARTMENT AND ASSIGNED FACULTY
        $stmt = $pdo->prepare("SELECT dept_id, assigned_faculty_id FROM subjects WHERE subject_id = ?");
        $stmt->execute([$subject_id]);
        $subject_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subject_info) {
            echo json_encode(['status' => 'error', 'message' => 'SUBJECT_NOT_FOUND']);
            exit;
        }

        $dept_id = $subject_info['dept_id'];
        $fac_id  = $subject_info['assigned_faculty_id'];

        // 3. ROOM CONFLICT CHECK (Is the room busy?)
        $room_sql = "SELECT id FROM timetable 
                     WHERE day_of_week = ? AND room_no = ? 
                     AND (start_time < ? AND end_time > ?)";
        $room_stmt = $pdo->prepare($room_sql);
        $room_stmt->execute([$day, $room, $end, $start]);
        
        if ($room_stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => "ROOM_CONFLICT: Room $room is already occupied during this slot."]);
            exit;
        }

        // 4. FACULTY CONFLICT CHECK (Is the teacher busy elsewhere?)
        if ($fac_id) {
            $fac_sql = "SELECT tt.id FROM timetable tt 
                        JOIN subjects s ON tt.subject_id = s.subject_id 
                        WHERE s.assigned_faculty_id = ? 
                        AND tt.day_of_week = ? 
                        AND (tt.start_time < ? AND tt.end_time > ?)";
            $fac_stmt = $pdo->prepare($fac_sql);
            $fac_stmt->execute([$fac_id, $day, $end, $start]);

            if ($fac_stmt->fetch()) {
                echo json_encode(['status' => 'error', 'message' => "FACULTY_CONFLICT: The assigned lecturer is already teaching another class at this time."]);
                exit;
            }
        }

        // 5. FINAL INJECTION
        $sql = "INSERT INTO timetable (dept_id, subject_id, day_of_week, start_time, end_time, room_no, lecture_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $insert = $pdo->prepare($sql);
        $insert->execute([
            $dept_id, 
            $subject_id, 
            $day, 
            $start, 
            $end, 
            $room, 
            $type
        ]);

        echo json_encode(['status' => 'success']);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'DATABASE_ERROR: ' . $e->getMessage()]);
    }
}
else {
    echo json_encode(['status' => 'error', 'message' => 'INVALID_REQUEST_METHOD']);
}
