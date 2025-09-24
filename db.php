<?php
// db.php
// Database connection using PDO. Include this in all other PHP files.
 
$host = 'localhost'; // Change if your host is different
$dbname = 'dbguwbwejab9q2';
$username = 'uhpdlnsnj1voi';
$password = 'rowrmxvbu3z5';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
 
// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
