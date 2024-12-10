<?php 
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "enrollment_db";

    $connection = mysqli_connect($host, $username, $password, $database);
    
    if (!$connection) {
        die("Database connection failed: " . mysqli_connect_error());
    }

?>