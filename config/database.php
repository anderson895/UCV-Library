<?php
// =====================================================
// Database Connection
// =====================================================
// Edit these values if your XAMPP MySQL settings differ.

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ucv_library';

// Connect using mysqli (beginner-friendly)
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// If connection failed, stop and show the error
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error()
        . "<br><br>Make sure you have:
        <ol>
          <li>Started Apache and MySQL in XAMPP</li>
          <li>Imported <code>database.sql</code> in phpMyAdmin</li>
          <li>Run <code>setup.php</code> to create the admin account</li>
        </ol>");
}

// Use UTF-8 so special characters display correctly
mysqli_set_charset($conn, 'utf8mb4');
?>
