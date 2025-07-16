<?php
$host = 'localhost';
$db = 'dbqpt3idyhnpqr';
$user = 'uaozeqcbxyhyg';
$pass = 'f4kld3wzz1v3';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}
?>
