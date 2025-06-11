<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Replace with your DB username
define('DB_PASSWORD', '');   // Replace with your DB password
define('DB_NAME', 'paper_stock_db'); // Replace with your DB name

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
