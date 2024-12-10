<?php
include '../database.php';

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

if (isset($_POST['submit'])) {
    // Sanitize inputs
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $pass = md5($_POST['pass']);
    $cpass = md5($_POST['cpass']);
    $user_type = filter_var($_POST['user_type'], FILTER_SANITIZE_STRING);
    $faculty = filter_var($_POST['faculty'], FILTER_SANITIZE_STRING); // Use 'faculty' here
    $position = filter_var($_POST['position'], FILTER_SANITIZE_STRING);

    // Validate password match
    if ($_POST['pass'] !== $_POST['cpass']) {
        $message[] = 'Confirm password does not match!';
    } else {
        // Handle profile picture
        $images = $_FILES['profile-pic']['name'] ?? 'default.png'; // Default image if none is uploaded
        $image_tmp_name = $_FILES['profile-pic']['tmp_name'] ?? '';
        $image_size = $_FILES['profile-pic']['size'] ?? 0;
        $image_folder = 'uploaded_img/' . $images;

        if ($images !== 'default.png' && $image_size > 2000000) {
            $message[] = 'Image size is too large!';
        } else {
            // Check if user already exists
            $select = $connection->prepare("SELECT * FROM users WHERE email = ?");
            $select->bind_param("s", $email);
            $select->execute();
            $result = $select->get_result();

            if ($result->num_rows > 0) {
                $message[] = 'User already exists!';
            } else {
                // Insert into users table
                $insert_user = $connection->prepare("INSERT INTO users (full_name, email, password, user_type, images) VALUES (?, ?, ?, ?, ?)");
                $insert_user->bind_param("sssss", $name, $email, $pass, $user_type, $images); // Use the images variable here

                if ($insert_user->execute()) {
                    $user_id = $insert_user->insert_id; // Get the inserted user ID
                    if ($images !== 'default.png') {
                        move_uploaded_file($image_tmp_name, $image_folder); // Move the uploaded image
                    }

                    // Insert into specific details table based on user_type
                    if ($user_type === 'Dean') {
                        $dean_id = uniqid('D'); // Generate a unique dean ID
                        $insert_dean = $connection->prepare("INSERT INTO deandetails (user_id, dean_id, name, faculty) VALUES (?, ?, ?, ?)");
                        $insert_dean->bind_param("isss", $user_id, $dean_id, $name, $faculty); // Use 'faculty' here
                        $insert_dean->execute();
                    } elseif ($user_type === 'Admin') {
                        $admin_id = uniqid('A'); // Generate a unique admin ID
                        $insert_admin = $connection->prepare("INSERT INTO admindetails (user_id, admin_id, name, position) VALUES (?, ?, ?, ?)");
                        $insert_admin->bind_param("isss", $user_id, $admin_id, $name, $position);
                        $insert_admin->execute();
                    }

                    $message[] = 'Registered successfully!';
                    header('Location: add-user.php');
                    exit;
                } else {
                    $message[] = 'Registration failed: ' . $insert_user->error;
                }
            }

            $select->close();
            $insert_user->close();
        }
    }
}

$connection->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Admin</title>
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
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

            <!--<?php
        } else {
            echo '<p class="error">Profile not found!</p>';
        }
        ?> -->

            <div class="side-menu">
                <ul>
                    <li>
                       <a href="admin-page.php" class="menu-item" onclick="setActive(this)">
                            <span class="las la-home"></span>
                            <small>Dashboard</small>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="menu-item dropdown-toggle" onclick="setActive(this); toggleDropdown()">
                            <span class="las la-user-alt"></span>
                            <small class="st">ManageUsers</small>
                            <span class="las la-angle-down dropdown-icon"></span>
                        </a>
                        <ul class="dropdown-content">
                            <li><a href="add-user.php" class="menu-item active" onclick="setActive(this)"><span class="las la-user-alt"></span><small>Add User</small></a></li>
                            <!-- <li><a href="update-user.php"><span class="las la-user-alt"></span><small>Update User</small></a></li> -->
                            <li><a href="delete-user.php"><span class="las la-user-alt"></span><small>Delete</small></a></li>
                        </ul>
                    </li>
                    <li>
                        
                    <li>
                       <!-- <a href="sched-sub.php" class="menu-item" onclick="setActive(this)">
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
                <h1>Add Users</h1>
            </div>
            
            <div class="page-content">
                <div class="add-dean-form">
                    <form id="dean-form" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <select id="user_type" name="user_type" onchange="toggleFields()" required>
                                <option value="" disabled selected>Choose Account Type</option>
                                <option value="Admin">Admin</option>
                                <option value="Dean">Dean</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <input type="text" placeholder="Full Name" id="full-name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" placeholder="Email" name="email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" placeholder="Password" name="pass" required>
                        </div>
                        <div class="form-group">
                            <input type="password" placeholder="Re-enter Password" name="cpass" required>
                        </div>

                        <!-- Position field for Admin -->
                        <div class="form-group" id="position-field" style="display:none;">
                            <select name="position" id="position">
                                <option value="" disabled selected>Choose Position</option>
                                <option value="Main Administrator">Main Administrator</option>
                                <option value="Administrator">Administrator</option>
                            </select>
                        </div>

                        <!-- Department field for Dean -->
                        <div class="form-group" id="department-field" style="display:none;">
                            <select name="faculty" id="faculty">
                                <option value="" disabled selected>Choose College Department</option>
                                <option value="Computer Studies">Computer Studies</option>
                                <option value="Business">Business</option>
                                <option value="Engineering">Engineering</option>
                            </select>
                        </div>

                        <div class="form-group upload-group">
                            <label for="profile-pic">Upload 2x2 Picture:</label>
                            <input type="file" id="profile-pic" name="profile-pic" accept="image/*" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="submit" class="save-btn">Save</button>
                            <button type="reset" class="cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <!-- <script src="../JS/add-user.js"></script> -->
    <script src="../JS/admin-page.js"></script>
</body>
</html>