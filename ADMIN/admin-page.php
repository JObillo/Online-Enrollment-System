<?php  
require('../database.php');

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

// Query to count total users
$queryUsers = "SELECT COUNT(*) AS total_users FROM users WHERE user_id";
$stmt = $connection->query($queryUsers);
$totalUsers = $stmt ? $stmt->fetch_assoc()['total_users'] : 0;

// Query to count total rejected students using MySQLi
$queryRejectedStudents = "SELECT COUNT(*) AS total_rejected_students FROM courseenrollment WHERE status = 'rejected'";
$stmt = $connection->query($queryRejectedStudents);
$totalRejectedStudents = $stmt ? $stmt->fetch_assoc()['total_rejected_students'] : 0;

// Query to count total enrolled students using MySQLi
$queryEnrolledCount = "SELECT COUNT(*) AS total_enrolled FROM courseenrollment WHERE status = 'enrolled'";
$stmt = $connection->query($queryEnrolledCount);
$totalEnrolled = $stmt ? $stmt->fetch_assoc()['total_enrolled'] : 0;

$queryUsers = "SELECT user_id, full_name, email, user_type, created_at FROM users LIMIT 10";
$stmt = $connection->query($queryUsers);
$users = $stmt ? $stmt->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Admin</title>
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <script src="../JS/admin-page.js"></script>
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
                       <a href="admin-page.php" class="menu-item active" onclick="setActive(this)">
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
                       <a href="sched-sub.php" class="menu-item" onclick="setActive(this)">
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
                <h1>Dashboard</h1>
                <small>Home / Dashboard</small>
            </div>
            
            <div class="page-content">
                <div class="analytics">
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $totalUsers; ?></h2>
                            <span class="las la-user-friends"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Users</small>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $totalRejectedStudents; ?></h2>
                            <span class="las lea-usr-friends"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Rejected Students</small>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $totalEnrolled; ?></h2>
                            <span class="las la-user-friends"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Enrolled</small>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2>0</h2>
                            <span class="las la-book-reader"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Subject</small>
                        </div>
                    </div>
                </div>

                <div class="records table-responsive">
                    <div class="record-header">
                        <div class="add">
                            <span>Student List</span>
                        </div>

                        <div class="browse">
                           <input type="search" placeholder="Search" class="record-search">
                            <select name="" id="">
                                <option value="">All Course</option>
                                <option value="">Information Technology</option>
                                <option value="">Computer Science</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <table width="100%">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th><span class="las la-sort"></span> Full Name</th>
                                    <th><span class="las la-sort"></span> Email</th>
                                    <th><span class="las la-sort"></span> User Type</th>
                                    <th><span class="las la-sort"></span> Created at</th>
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
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>