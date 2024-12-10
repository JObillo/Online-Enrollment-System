<?php

// Include the database connection
include('../database.php');

session_start();

// Ensure that the user is logged in
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('location:login.php');
    exit;
}

// Fetch user profile data using MySQLi
$select_profile = $connection->prepare("SELECT * FROM `users` WHERE user_id = ?");
$select_profile->bind_param("i", $user_id); // "i" for integer
$select_profile->execute();
$result = $select_profile->get_result(); // Get the result set

$fetch_profile = $result->fetch_assoc(); // Fetch the profile data

if (!$fetch_profile) {
    echo "User not found!";
    exit;
}

// Handle image data: Check if BLOB is set, else use default
if (!empty($fetch_profile['image'])) {
    $image_data = base64_encode($fetch_profile['image']);
    $image_src = "data:image/jpeg;base64," . $image_data;
} else {
    $image_src = "../images/default_image.jpg";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal</title>
    <link rel="stylesheet" href="../CSS/student-page.css">
</head>
<body>
    <div class="header">
        <img src="../IMAGES/logo(1).png" alt="School Logo" class="logo">
        <h1>Monarch College</h1>
    </div>
    <div class="sidebar">
        <div class="side-content">
            <div class="profile">
                <div class="profile-img" style="background-image: url($images_src)"></div>
                <h4><?= htmlspecialchars($fetch_profile['full_name']); ?></h4>
                <!-- <small><a href="profile.php">Student Name</a></small> -->
                 <small>Student </small>
            </div>
            <div class="side-menu">
                <ul>
                    <li><a href="student-page.php" class="active">Dashboard</a></li>
                    <a href="enrollment-form.php" class="btn">Enrollment Form</a>
                    <!-- <li><a href="subjects.php">Subjects</a></li> -->
                    <li><a href="schedule.php">Registration</a></li>
                    <li><a href="">Grade</a></li>
                    <li><a href="">Library</a></li>
                </ul>
            </div>
        </div>
        <div class="log-out">
            <a href="../logout.php" class="log-out delete-btn">
                <span class="las la-power-off"></span>
                Log out
            </a>
        </div>
    </div>
    <div class="main-content">
        <main>
            <div class="page-header">
                <h4>Dashboard</h4>
            </div>
            <div class="page-content">
                <div class="mess">
                    <div class="mess-card">
                        <h1>Welcome, <?= htmlspecialchars($fetch_profile['full_name']); ?></h1>
                        <!-- <h2>Total Subjects 10</h2> -->
                    </div>
                </div>
                <div class="calendar">
                    <div class="calendar-header">
                        <button id="prevMonth" class="nav-btn">&lt;</button>
                        <h2 id="monthYear"></h2>
                        <button id="nextMonth" class="nav-btn">&gt;</button>
                    </div>
                    <table class="calendar-table">
                        <thead>
                            <tr>
                                <th>Sun</th>
                                <th>Mon</th>
                                <th>Tue</th>
                                <th>Wed</th>
                                <th>Thu</th>
                                <th>Fri</th>
                                <th>Sat</th>
                            </tr>
                        </thead>
                        <tbody id="calendarBody">
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>
    <script src="../JS/student-page.js"></script>
</body>
</html>