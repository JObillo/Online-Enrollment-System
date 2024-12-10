<?php
// Include the database connection (if you need it elsewhere)
include 'database.php';

// Start the session
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the login page
header('location:login.php');
exit; // It's a good practice to call exit after header redirection
?>
