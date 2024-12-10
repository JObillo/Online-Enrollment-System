
<?php  
require('../database.php');

session_start();

$admin_id = $_SESSION['user_id'];

if (!isset($admin_id)) {
   header('location:login.php');
   exit();
}

// Query to fetch profile with 'images' column for the profile image
$select_profile = $connection->prepare("SELECT full_name, email, user_type, images FROM `users` WHERE user_id = ?");
$select_profile->bind_param("i", $admin_id); // "i" for integer
$select_profile->execute();
$result = $select_profile->get_result();
$fetch_profile = $result->fetch_assoc(); // Fetch the profile data

// Use the image from the database or the default image
$image_src = !empty($fetch_profile['images']) ? 'profile-images/' . htmlspecialchars($fetch_profile['images']) : 'default-profile.png'; // Set default image if not available

// Success or error messages
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $program = $_POST['program'];
    $year = $_POST['year'];
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $start_time = $_POST['start_time'];  // 24-hour format
    $end_time = $_POST['end_time'];      // 24-hour format
    $days = $_POST['days'];
    $room = $_POST['room'];

    // Convert 24-hour time to 12-hour format with AM/PM
    $start_time = date("h:i A", strtotime($start_time));  // Format the time
    $end_time = date("h:i A", strtotime($end_time));      // Format the time

    // Determine the table based on program and year
    $table = "";
    if ($program == 'BSIT') {
        switch ($year) {
            case 'fy':
                $table = 'bsit_firstyear';
                break;
            case 'sy':
                $table = 'bsit_secondyear';
                break;
            case 'ty':
                $table = 'bsit_thirdyear';
                break;
            case 'foy':
                $table = 'bsit_fourthyear';
                break;
        }
    } elseif ($program == 'BSCS') {
        switch ($year) {
            case 'fy':
                $table = 'bscs_firstyear';
                break;
            case 'sy':
                $table = 'bscs_secondyear';
                break;
            case 'ty':
                $table = 'bscs_thirdyear';
                break;
            case 'foy':
                $table = 'bscs_fourthyear';
                break;
        }
    }

    // Prepare the insert query
    $sql = "INSERT INTO $table (subject_code, subject_name, start_time, end_time, days, room) 
            VALUES (?, ?, ?, ?, ?, ?)";

    // Prepare statement and bind parameters to avoid SQL injection
    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param("ssssss", $subject_code, $subject_name, $start_time, $end_time, $days, $room);

        // Execute the query
        if ($stmt->execute()) {
            $message = 'New record created successfully.';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . $stmt->error;
            $message_type = 'error';
        }

        $stmt->close();
    } else {
        $message = 'Error: Could not prepare the SQL statement.';
        $message_type = 'error';
    }

    // Close the connection
    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subjects</title>
    <link rel="stylesheet" href="../CSS/admin.css">
    <!-- <link rel="stylesheet" href="../CSS/ss.css"> -->
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <script src="../JS/admin-page.js"></script>
   <!-- <script>
        // Show pop-up based on success/error message
        window.onload = function() {
            <?php if ($message): ?>
                let messageType = "<?php echo $message_type; ?>";
                let messageText = "<?php echo $message; ?>";

                if (messageType == 'success') {
                    alert("Success: " + messageText);
                } else {
                    alert("Error: " + messageText);
                }
            <?php endif; ?>
        };
    </script> -->
</head>
<body>
<div class="header-container">
        <img src="../images/logo(1).png" alt="logo picture" class="logo">
        <h1>Monarch College</h1>
    </div>
    <div class="sidebar">

        <?php
        if ($fetch_profile) {
        ?>
        
        <div class="side-content">
            <div class="profile">
                <!-- Display the user profile image or the default image -->
                <div class="profile-img bg-img" style="background-image: url('<?= htmlspecialchars($image_src); ?>')"></div>
                <h4><?= htmlspecialchars($fetch_profile['full_name'] ?? 'Unknown User'); ?></h4>
                <small>Administrator</small>
            </div>
            <!--
            <?php
        } else {
            echo '<p class="error">Profile not found!</p>';
        }
        ?> -->

            <div class="side-menu">
                <ul>
                    <li>
                       <a href="admin-page.php" class="menu-item " onclick="setActive(this)">
                            <span class="las la-home"></span>
                            <small>Dashboard</small>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="menu-item dropdown-toggle" onclick="setActive(this); toggleDropdown()">
                            <span class="las la-user-alt"></span>
                            <small>ManageUsers</small>
                            <span class="las la-angle-down dropdown-icon"></span>
                        </a>
                        <ul class="dropdown-content">
                            <li><a href="add-user.php"><span class="las la-user-alt"></span><small>Add User</small></a></li>
                            <!-- <li><a href="update-user.php"><span class="las la-user-alt"></span><small>Update User</small></a></li> -->
                            <li><a href="delete-user.php"><span class="las la-user-alt"></span><small>Delete</small></a></li>
                        </ul>
                    </li>
                    <li>
                        
                    <!-- <li>
                       <a href="sched-sub.php" class="menu-item active" onclick="setActive(this)">
                            <span class="las la-user-alt"></span>
                            <small>Schedules & Subject</small>
                        </a> -->
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
                <h1>Add Subjects</h1>
            </div>

            <div class="page-content">
                <div class="add-subject-form">
                    <!-- Submit form to PHP -->
                    <form id="subject-form" action="sched-sub.php" method="POST">
                        <div class="form-group">
                            <label for="program">Program:</label>
                            <select id="program" name="program" required>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="year">Year Level:</label>
                            <select id="year" name="year" required>
                                <option value="fy">First Year</option>
                                <option value="sy">Second Year</option>
                                <option value="ty">Third Year</option>
                                <option value="foy">Fourth Year</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject_code">Subject Code:</label>
                            <input type="text" id="subject_code" name="subject_code" placeholder="Enter subject code" required>
                        </div>

                        <div class="form-group">
                            <label for="subject_name">Subject Name:</label>
                            <input type="text" id="subject_name" name="subject_name" placeholder="Enter subject name" required>
                        </div>

                        <div class="form-group">
                            <label for="start_time">Start Time:</label>
                            <input type="time" id="start_time" name="start_time" required>
                        </div>

                        <div class="form-group">
                            <label for="end_time">End Time:</label>
                            <input type="time" id="end_time" name="end_time" required>
                        </div>

                        <div class="form-group">
                            <label for="days">Days:</label>
                            <input type="text" id="days" name="days" placeholder="e.g., Mon, Wed, Fri" required>
                        </div>

                        <div class="form-group">
                            <label for="room">Room:</label>
                            <input type="text" id="room" name="room" placeholder="Enter room name/number" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="save-btn">Save</button>
                            <button type="reset" class="cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>