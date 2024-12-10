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

// Modify the query to only fetch users who are "rejected" based on `courseenrollment` status
$queryUsers = "
    SELECT u.user_id, u.full_name, u.email, u.user_type, u.created_at, c.status
    FROM users u
    JOIN courseenrollment c ON u.user_id = c.user_id
    WHERE c.status = 'rejected'
    LIMIT 10
";
$stmt = $connection->query($queryUsers);
$users = $stmt ? $stmt->fetch_all(MYSQLI_ASSOC) : [];

if (isset($_POST['delete'])) {
    $student_id = $_POST['student_id'];

    // Step 1: Delete dependent records from all tables referencing `user_id`
    $tables = [
        'address', 'contactinfo', 'courseenrollment', 'education',
        'parents', 'requirements', 'sched_sub', 'students', 'transferee'
    ];

    // Loop through each table and delete the user records
    foreach ($tables as $table) {
        $deleteQuery = "DELETE FROM $table WHERE user_id = ?";
        $deleteStmt = $connection->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $student_id);
        if (!$deleteStmt->execute()) {
            error_log("Failed to delete records from $table: " . $deleteStmt->error);
            echo "<script>alert('Failed to delete data from $table.'); window.location.href = 'delete-user.php';</script>";
            exit;
        }
    }

    // Step 2: Finally, delete the user from the `users` table
    $deleteUserQuery = "DELETE FROM users WHERE user_id = ?";
    $deleteUserStmt = $connection->prepare($deleteUserQuery);
    $deleteUserStmt->bind_param("i", $student_id);

    if ($deleteUserStmt->execute()) {
        echo "<script>alert('Student has been deleted successfully.'); window.location.href = 'delete-user.php';</script>";
    } else {
        error_log("Failed to delete user: " . $deleteUserStmt->error);
        echo "<script>alert('Failed to delete the student.'); window.location.href = 'delete-user.php';</script>";
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Rejected Students</title>
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <script src="../JS/dean-page.js"></script>
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
        <?php } else {
            echo '<p class="error">Profile not found!</p>';
        }
        ?>

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
                            <small>Manage Users</small>
                            <span class="las la-angle-down dropdown-icon"></span>
                        </a>
                        <ul class="dropdown-content">
                            <li><a href="add-user.php"><span class="las la-user-alt"></span><small>Add User</small></a></li>
                            <li><a href="delete-user.php" class="menu-item active" onclick="setActive(this)"><span class="las la-user-alt"></span><small>Delete</small></a></li>
                        </ul>
                    </li>
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
                <h1>Rejected Students</h1>
            </div>
            <div class="page-content">
                <div class="records table-responsive">
                    <table class="row" width="100%">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th><span class="las la-sort"></span> Full Name</th>
                                <th><span class="las la-sort"></span> Email</th>
                                <th><span class="las la-sort"></span> User Type</th>
                                <th><span class="las la-sort"></span> Created at</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $results) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($results['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($results['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($results['email']); ?></td>
                                <td><?php echo htmlspecialchars($results['user_type']); ?></td>
                                <td><?php echo htmlspecialchars($results['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($results['status'] ?? 'N/A'); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($results['user_id']); ?>">
                                        <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this student?');">Delete</button>
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
