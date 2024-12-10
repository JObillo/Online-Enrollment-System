<?php 
require('../database.php'); // Includes the MySQLi connection as $connection

session_start();

$admin_id = $_SESSION['user_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

// Handle image data: Check if BLOB is set, else use default
$image_src = "../images/default_image.jpg"; // Default image

// Query to fetch profile using MySQLi
$select_profile = $connection->prepare("SELECT * FROM `users` WHERE user_id = ?");
$select_profile->bind_param("i", $admin_id); // "i" for integer
$select_profile->execute();
$result = $select_profile->get_result();
$fetch_profile = $result->fetch_assoc(); // Fetch the profile data

if ($fetch_profile && !empty($fetch_profile['image'])) {
   $image_data = base64_encode($fetch_profile['image']);
   $image_src = "data:image/jpeg;base64," . $image_data;
}
// Enable error reporting for debugging (in production, turn this off)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch enrolled students using MySQLi with prepared statement
try {
    $query = $connection->prepare("
        SELECT se.user_id AS student_id, se.first_name, se.last_name, ce.year_level, ce.semester, ce.course_name, ce.status 
        FROM students se
        JOIN courseenrollment ce ON se.user_id = ce.user_id 
        WHERE ce.status = ?");
    
    $status = 'rejected';
    $query->bind_param('s', $status);
    $query->execute();
    $result = $query->get_result();

    if ($result) {
        // Fetch results as an associative array
        $students = $result->fetch_all(MYSQLI_ASSOC); 
    } else {
        // If the query fails, throw an exception
        throw new Exception("Query failed: " . $connection->error);
    }
} catch (Exception $e) {
    die("Query failed: " . $e->getMessage());
}

// Handle undo action
if (isset($_POST['undo'])) {
    $student_id = $_POST['student_id'];
    
    // Query to get the current status of the student
    $queryStatus = "SELECT status FROM courseenrollment WHERE user_id = ?";
    $stmt = $connection->prepare($queryStatus);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $resultStatus = $stmt->get_result();
    $statusRow = $resultStatus->fetch_assoc();
    
    if ($statusRow && $statusRow['status'] === 'rejected') {
        // Revert the status to 'pre-enrolled'
        $revertQuery = "UPDATE courseenrollment SET status = 'pre-enrolled' WHERE user_id = ?";
        $revertStmt = $connection->prepare($revertQuery);
        $revertStmt->bind_param("i", $student_id);
        
        if ($revertStmt->execute()) {
            echo "<script>alert('Status reverted to pre-enrolled.'); window.location.href = 'rejected.php';</script>";
            exit;
        } else {
            echo "<script>alert('Failed to revert status.'); window.location.href = 'rejected.php';</script>";
            exit;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Pre-Enrolled Students</title>
    <link rel="stylesheet" href="../CSS/dean.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <script src="../JS/dean-page.js"></script>
    <style>
        .btn-undo{
            margin: 5px; padding: 10px 20px; background-color:  #007bff;  color: #fff; border: none; cursor: pointer; border-radius: 4px; }
        .btn-undo:hover{
            background-color: #45a049;
        }
    </style>
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
                <div class="profile-img bg-img" style="background-image: url('<?= htmlspecialchars($image_src); ?>')"></div>
                <h4><?= htmlspecialchars($fetch_profile['full_name'] ?? 'Unknown User'); ?></h4>
                <small>Dean</small>
            </div>
            
        <?php
        } else {
            echo '<p class="error">Profile not found!</p>';
        }
        ?>
            <div class="side-menu">
                <div class="side-menu">
                    <ul>
                        <li>
                           <a href="dean-page.php" onclick="setActive(this)">
                                <span class="las la-home"></span>
                                <small>Dashboard</small>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="javascript:void(0);" class="menu-item dropdown-toggle" onclick="setActive(this); toggleDropdown()">
                                <span class="las la-user-alt"></span>
                                <small class="st">Students</small>
                               
                                <span class="las la-angle-down dropdown-icon"></span>
                            </a>
                            <ul class="dropdown-content">
                                <li><a href="pre-enrolled.php"><span class="las la-user-alt"></span><small>Pre-enrolled</small></a></li>
                                <li><a href="enrolled.php"><span class="las la-user-alt" ></span><small>Enrolled</small></a></li>
                                <li><a href="rejected.php" class="menu-item active" onclick="setActive(this)"><span class="las la-user-alt" ></span><small>Rejected </small></a></li>
                            </ul>
                        </li>
                        <!-- <li>
                           <a href="teacher.php" class="menu-item" onclick="setActive(this)">
                                <span class="las la-user-alt"></span>
                                <small>Teacher</small>
                            </a>
                        </li>
                        <li>
                           <a href="sub.php" class="menu-item" onclick="setActive(this)">
                                <span class="las la-book-reader"></span>
                                <small>Subject</small>
                            </a>
                        </li> -->
                    </ul>
                </div>
            </div>
        </div>
        <div class="log-out">
            <a href="../logout.php" class="log-out delete-btn"><span class="las la-power-off"></span> Log out</a>
        </div>
    </div>

    <div class="main-content">
        <main>
            <div class="page-header">
                <h1>REJECTED STUDENTS</h1>
            </div>
            <div class="page-content">
                <div class="records table-responsive">
                    <table class="row" width="100%">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Year Level</th>
                                <th>Semester</th>
                                <th>Course</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($students as $row) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                                <td><?php echo htmlspecialchars($row['semester']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
                                        <!-- <a href="download.php?id=<?php echo htmlspecialchars($row['student_id']); ?>" title="Download"><span class="las la-download"></span></a> -->
                                        <!-- <a href="view-enrolled.php?student_id=<?php echo htmlspecialchars($row['student_id']); ?>" title="View"><span class="las la-eye"></span></a> -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>">
                                        <button type="submit" name="undo" class="btn-undo" onclick="return confirm('Are you sure you want to revert this student to pre-enrolled status?');">Undo</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>