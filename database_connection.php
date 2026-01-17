<?php
// Database connection with robust fallback
// Disable default exception mode for connection attempts to allow fallbacks
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_OFF;

$db_username = 'root';
$db_password = '';
$db_name = 'gsm_health_system';

// Try IPv6 Localhost first (often fastest on Windows/XAMPP)
$Connection = @mysqli_connect('::1', $db_username, $db_password);

// If failed, try IPv4 Localhost
if (!$Connection) {
    $Connection = @mysqli_connect('127.0.0.1', $db_username, $db_password);
}

// If failed, try "localhost" (let system decide)
if (!$Connection) {
    $Connection = @mysqli_connect('localhost', $db_username, $db_password);
}

// Check connection
if (!$Connection) {
    // Re-enable reporting to show the final error
    $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$create_db_query = "CREATE DATABASE IF NOT EXISTS $db_name";
if (!mysqli_query($Connection, $create_db_query)) {
    die("Error creating database: " . mysqli_error($Connection));
}

// Select the database
if (!mysqli_select_db($Connection, $db_name)) {
    die("Error selecting database: " . mysqli_error($Connection));
}
?>