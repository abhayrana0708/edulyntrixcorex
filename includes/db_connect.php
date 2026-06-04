<?php
$host = 'localhost';
$user = 'root'; // Default for XAMPP
$pass = '';     // Default for XAMPP (Empty)
$dbname = 'edulyntrix_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>