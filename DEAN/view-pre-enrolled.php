<?php
    // Require database connection
    require('../database.php');

    // Get the user_id from the URL (make sure it's always present)
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    if ($user_id === 0) {
        die("Error: Missing or invalid user ID in the URL.");
    }

    // Initialize variables
    $schedulesResult = null;
    $studentStatus = null;

    // Handle accept/reject actions
    if (isset($_POST['action'])) {
        $status = null;
        if ($_POST['action'] === 'accept') {
            $status = 'enrolled';  // Update status to 'accepted'
        } elseif ($_POST['action'] === 'reject') {
            $status = 'rejected';  // Update status to 'rejected'
        }

        if ($status) {
            // Update the status in the database
            $updateQuery = "UPDATE courseenrollment SET status = '$status' WHERE user_id = $user_id";
            if (mysqli_query($connection, $updateQuery)) {
                if ($_POST['action'] === 'reject') {
                    // Redirect to pre-enrolled after rejection
                    echo "<script>
                        alert('Status reverted to Rejected.');
                        window.location.href = 'pre-enrolled.php';
                    </script>";
                    exit;
                } else {
                    // Redirect with the user_id in the URL for other actions
                    header("Location: view-pre-enrolled.php?user_id=$user_id");
                    exit;
                }
            } else {
                die("Failed to update status: " . mysqli_error($connection));
            }
        } elseif ($_POST['action'] === 'add_schedule_subject') {
            // Handle adding a schedule and subject
            $block = mysqli_real_escape_string($connection, $_POST['block']);
            $subjectName = mysqli_real_escape_string($connection, $_POST['subject_name']);
            $startTime = mysqli_real_escape_string($connection, $_POST['start_time']);
            $endTime = mysqli_real_escape_string($connection, $_POST['end_time']);
            $days = mysqli_real_escape_string($connection, $_POST['days']);
            $room = mysqli_real_escape_string($connection, $_POST['room']);
            $instructor = mysqli_real_escape_string($connection, $_POST['instructor']);
            
            // Insert the data into the database
            $insertQuery = "INSERT INTO sched_sub (user_id, block, subject_name, start_time, end_time, days, room, instructor) 
                            VALUES ($user_id, '$block', '$subjectName', '$startTime', '$endTime', '$days', '$room', '$instructor')";
            if (mysqli_query($connection, $insertQuery)) {
                echo "<script>
                    alert('Schedule and subject added successfully.');
                    document.getElementById('addScheduleModal').style.display = 'block'; // Keep modal open
                </script>";
            } else {
                echo "<script>
                    alert('Error adding schedule and subject: " . mysqli_error($connection) . "');
                    document.getElementById('addScheduleModal').style.display = 'block'; // Keep modal open
                </script>";
            }
        }
    }

    // Query student information
    $queryStudentInformation = "
    SELECT 
        s.user_id,
        s.first_name,
        s.last_name,
        s.middle_name,
        s.birthdate,
        s.gender,
        s.religion,
        s.civil_status,
        ci.email,
        ci.phone_no,
        a.barangay,
        a.municipal,
        a.province,
        a.country,
        p.father_last_name,
        p.father_first_name,
        p.father_middle_name,
        p.father_occupation,
        p.father_phone_no,
        p.mother_last_name,
        p.mother_first_name,
        p.mother_middle_name,
        p.mother_occupation,
        p.mother_phone_no,
        e.senior_high_school,
        e.strand,
        e.year_graduated,
        e.general_average,
        t.transfer_last_school,
        t.transfer_last_year,
        t.transfer_course,
        t.file,
        t.file_picture AS file_picture_transferee,
        ce.year_level,
        ce.semester,
        ce.course_name,
        ce.status,
        r.file_grade,
        r.file_picture
    FROM 
        students s
    INNER JOIN 
        contactinfo ci ON s.user_id = ci.user_id
    INNER JOIN 
        address a ON s.user_id = a.user_id
    INNER JOIN 
        parents p ON s.user_id = p.user_id
    LEFT JOIN 
        education e ON s.user_id = e.user_id
    INNER JOIN 
        courseenrollment ce ON s.user_id = ce.user_id
    LEFT JOIN 
        requirements r ON s.user_id = r.user_id
    LEFT JOIN 
        transferee t ON s.user_id = t.user_id
    WHERE 
        s.user_id = $user_id;";

    $result = mysqli_query($connection, $queryStudentInformation);

    // Check if the query is successful and returns data
    if (!$result) {
        die("Database query failed: " . mysqli_error($connection));
    }

    if (mysqli_num_rows($result) > 0) {
        $studentInformation = mysqli_fetch_assoc($result);
        $studentStatus = isset($studentInformation['status']) ? $studentInformation['status'] : null; 
    } else {
        echo "No student information found for user_id: $user_id";
        exit;
    }

    // Fetch schedules for the specific user
    $schedulesQuery = "SELECT block, subject_name, start_time, end_time, days, room, instructor FROM sched_sub WHERE user_id = $user_id";
    $schedulesResult = mysqli_query($connection, $schedulesQuery);
    if (!$schedulesResult) {
        die("Error fetching schedules: " . mysqli_error($connection));
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <link rel="stylesheet" href="../CSS/view-pre-enrolled.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f9; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        .action-buttons, .schedule-form { margin: 20px 0; text-align: center; }
        button { margin: 5px; padding: 10px 20px; background-color:  #007bff; color: #fff; border: none; cursor: pointer; border-radius: 4px; }
        button.reject{ background-color: #f44336; }
        button:hover { background-color: #45a049; }
        button.reject:hover { background-color: #d32f2f; }
        button.cancel:hover{background-color: red;}
        .modal { display: none; position: fixed; top: 40%; left: 50%; height: 580px;  transform: translate(-50%, -50%); z-index: 1000; width: 60%; background: #fff; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .modalimg{
                display: none;
                position: fixed;
                z-index: 1000;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.8); /* Dark background with transparency */
                overflow: auto;
                justify-content: center;
                align-items: center;
                text-align: center;
        }

        .modalimg.active { display: flex; }
        .modalimg-header, .modal-footer { display: flex; justify-content: space-between; align-items: center; }
        .modalimg-header h3 { margin: 0; }
        .close {
                position: absolute;
                top: 20px;
                right: 30px;
                color: #fff;
                font-size: 30px;
                font-weight: bold;
                cursor: pointer;
                transition: color 0.3s;
            }
        .close:hover {
                color: #ccc;
            }
        .clickable-image { cursor: pointer; max-width: 100%; height: auto; }
        .modal-content {
                max-width: 90%; /* Maximum width of image */
                max-height: 90%; /* Maximum height of image */
                margin: auto; /* Center horizontally */
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5); /* Adds shadow for depth */
                border-radius: 8px; /* Rounded corners for the image */
                object-fit: contain; /* Ensures full image fits without distortion */
                animation: zoomIn 0.3s ease-in-out; /* Smooth zoom-in animation */
        }
        .top{background-color:#007bff ;}
        #addScheduleModal {
            max-height: 700px; /* Limits the modal to 80% of the viewport height */
            width: 90%; /* Responsive width */
            max-width: 400px; /* Prevents the form from stretching too wide */
            margin: 20px auto; /* Centers the modal */
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }


            /* Form Group Styling */
            .form-group {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
                min-height: 50px; /* Ensures consistent height */
            }

            /* Labels */
            .form-group label {
                flex: 0 0 150px; /* Fixed width for label */
                font-size: 1rem; /* Responsive font size */
                line-height: 1.5; /* Line height for vertical alignment */
                margin-bottom: 5px;
            }

            /* Inputs and Selects */
            .form-group input,
            .form-group select {
                flex: 1; /* Input and select fields take up remaining space */
                height: 2.5rem; /* Relative height for consistent sizing */
                margin-left: 10px;
                padding: 5px 10px; /* Inner padding for better spacing */
                font-size: 1rem; /* Maintain readability */
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            @keyframes zoomIn {
                from {
                    transform: scale(0.8);
                    opacity: 0;
                }
                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }

        #backButton {
        display: inline-flex; 
        align-items: center;
        text-decoration: none; 
        font-size: 15px;
        color:white;
        background-color: #007bff; 
        padding: 10px 20px;
        border-radius: 4px;
        transition: all 0.3s ease; /* Smooth transition */
        height: 15px;
        margin: 5px;
        }

        #backButton i {
            margin-right: 5px; 
            font-size: 1rem;
        }

        #backButton:hover {
            text-decoration: none;
            background-color: #0056b3;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Student Information</h1>

        <div class="info-section">
            <h2>Basic Information</h2>
            <div class="info-item">Last Name: <?= htmlspecialchars($studentInformation['last_name']) ?></div>
            <div class="info-item">First Name: <?= htmlspecialchars($studentInformation['first_name']) ?></div>
            <div class="info-item">Middle Name: <?= htmlspecialchars($studentInformation['middle_name'] ?? 'N/A') ?></div>
            <div class="info-item">Birthdate: <?= htmlspecialchars($studentInformation['birthdate'] ?? 'N/A') ?></div>
            <div class="info-item">Gender: <?= htmlspecialchars($studentInformation['gender'] ?? 'N/A') ?></div>
            <div class="info-item">Religion: <?= htmlspecialchars($studentInformation['religion'] ?? 'N/A') ?></div>
            <div class="info-item">Civil Status: <?= htmlspecialchars($studentInformation['civil_status'] ?? 'N/A') ?></div>
        </div>

        <div class="info-section">
            <h2>Contact Information</h2>
            <div class="info-item">Email: <?= htmlspecialchars($studentInformation['email']) ?></div>
            <div class="info-item">Phone No: <?= htmlspecialchars($studentInformation['phone_no']) ?></div>
        </div>

        <div class="info-section">
            <h2>Address</h2>
            <div class="info-item">Address: <?= htmlspecialchars($studentInformation['barangay']) ?>, <?= htmlspecialchars($studentInformation['municipal']) ?>, <?= htmlspecialchars($studentInformation['province']) ?>, <?= htmlspecialchars($studentInformation['country']) ?></div>
        </div>

        <div class="info-section">
            <h2>Parent Information</h2>
            <h3>Father's Details</h3>
            <div class="info-item">Last Name: <?= htmlspecialchars($studentInformation['father_last_name'] ?? 'N/A') ?></div>
            <div class="info-item">First Name: <?= htmlspecialchars($studentInformation['father_first_name'] ?? 'N/A') ?></div>
            <div class="info-item">Middle Name: <?= htmlspecialchars($studentInformation['father_middle_name'] ?? 'N/A') ?></div>
            <div class="info-item">Occupation: <?= htmlspecialchars($studentInformation['father_occupation'] ?? 'N/A') ?></div>
            <div class="info-item">Phone No: <?= htmlspecialchars($studentInformation['father_phone_no'] ?? 'N/A') ?></div>

            <h3>Mother's Details</h3>
            <div class="info-item">Last Name: <?= htmlspecialchars($studentInformation['mother_last_name'] ?? 'N/A') ?></div>
            <div class="info-item">First Name: <?= htmlspecialchars($studentInformation['mother_first_name'] ?? 'N/A') ?></div>
            <div class="info-item">Middle Name: <?= htmlspecialchars($studentInformation['mother_middle_name'] ?? 'N/A') ?></div>
            <div class="info-item">Occupation: <?= htmlspecialchars($studentInformation['mother_occupation'] ?? 'N/A') ?></div>
            <div class="info-item">Phone No: <?= htmlspecialchars($studentInformation['mother_phone_no'] ?? 'N/A') ?></div>
        </div>

        <div class="info-section">
            <h2>Education</h2>
            <div class="info-item">Last School Attended: <?= htmlspecialchars($studentInformation['senior_high_school']) ?></div>
            <div class="info-item">Strand: <?= htmlspecialchars($studentInformation['strand']) ?></div>
            <div class="info-item">Year Graduated: <?= htmlspecialchars($studentInformation['year_graduated']) ?></div>
            <div class="info-item">General Average: <?= htmlspecialchars($studentInformation['general_average']) ?></div>
        </div>

        <div class="info-section">
            <h2>Transfer Information</h2>
            <div class="info-item">Last School Transferred From: <?= htmlspecialchars($studentInformation['transfer_last_school'] ?? 'N/A') ?></div>
            <div class="info-item">Last Year Attended: <?= htmlspecialchars($studentInformation['transfer_last_year'] ?? 'N/A') ?></div>
            <div class="info-item">Transfer Course: <?= htmlspecialchars($studentInformation['transfer_course'] ?? 'N/A') ?></div>
        </div>

        <div class="info-section">
            <h2>Course Information</h2>
            <div class="info-item">Year Level: <?= htmlspecialchars($studentInformation['year_level']) ?></div>
            <div class="info-item">Semester: <?= htmlspecialchars($studentInformation['semester']) ?></div>
            <div class="info-item">Course: <?= htmlspecialchars($studentInformation['course_name']) ?></div>
        </div>

        <div class="info-section">
            <h2>Requirements</h2>

        <!-- Display file_grade from requirements table -->
        <div class="info-item">
            <strong>Requirement File (Grade):</strong>
            <?php if (!empty($studentInformation['file_grade'])): ?>
                <?php 
                $imageDataFileGrade = base64_encode($studentInformation['file_grade']);
                $mimeTypeFileGrade = 'image/jpeg'; // Adjust as necessary
                ?>
                <img src="data:<?= $mimeTypeFileGrade ?>;base64,<?= $imageDataFileGrade ?>" 
                    alt="File Grade" 
                    class="clickable-image" 
                    style="max-width: 20%; height: auto;" />
            <?php else: ?>
                No grade requirement file submitted.
            <?php endif; ?>
        </div>

        <!-- Display file_picture from requirements table -->
        <div class="info-item">
            <strong>Requirement File (Picture):</strong>
            <?php if (!empty($studentInformation['file_picture'])): ?>
                <?php 
                $imageDataFilePicture = base64_encode($studentInformation['file_picture']);
                $mimeTypeFilePicture = 'image/jpeg'; // Adjust as necessary
                ?>
                <img src="data:<?= $mimeTypeFilePicture ?>;base64,<?= $imageDataFilePicture ?>" 
                    alt="Requirement Picture" 
                    class="clickable-image" 
                    style="max-width: 20%; height: auto;" />
            <?php else: ?>
                No requirement picture submitted.
            <?php endif; ?>
        </div>

        <!-- Display file from transferee table -->
        <div class="info-item">
            <strong>Transferee File:</strong>
            <?php if (!empty($studentInformation['file'])): ?>
                <?php 
                $imageDataTransfereeFile = base64_encode($studentInformation['file']);
                $mimeTypeTransfereeFile = 'image/jpeg'; // Adjust as necessary
                ?>
                <img src="data:<?= $mimeTypeTransfereeFile ?>;base64,<?= $imageDataTransfereeFile ?>" 
                    alt="Transferee File" 
                    class="clickable-image" 
                    style="max-width: 20%; height: auto;" />
            <?php else: ?>
                No transferee file submitted.
            <?php endif; ?>
        </div>

        <!-- Display file_picture from transferee table -->
        <div class="info-item">
            <strong>Transferee Picture:</strong>
            <?php if (!empty($studentInformation['file_picture_transferee'])): ?>
                <?php 
                $imageDataTransfereePicture = base64_encode($studentInformation['file_picture_transferee']);
                $mimeTypeTransfereePicture = 'image/jpeg'; // Adjust as necessary
                ?>
                <img src="data:<?= $mimeTypeTransfereePicture ?>;base64,<?= $imageDataTransfereePicture ?>" 
                    alt="Transferee Picture" 
                    class="clickable-image" 
                    style="max-width: 20%; height: auto;" />
            <?php else: ?>
                No transferee picture submitted.
            <?php endif; ?>
        </div>

        <!-- Modal for large image view -->
        <div id="imageModal" class="modalimg">
            <span class="close" onclick="closeModal()">&times;</span>
            <img class="modal-content" id="modalImage">
        </div>
    </div>

        <!-- Schedule Information -->
        <div class="info-section">
            <h2>Schedules</h2>
            <table>
                <thead class="top">
                    <tr>
                        <th>Block</th>
                        <th>Subject Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Days</th>
                        <th>Room</th>
                        <th>Instructor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($schedulesResult)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['block']) ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= htmlspecialchars($row['start_time']) ?></td>
                            <td><?= htmlspecialchars($row['end_time']) ?></td>
                            <td><?= htmlspecialchars($row['days']) ?></td>
                            <td><?= htmlspecialchars($row['room']) ?></td>
                            <td><?= htmlspecialchars($row['instructor']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

            <form method="POST">
                <?php if ($studentStatus === 'pre-enrolled'): ?>
                    <button type="submit" name="action" value="accept">Accept</button>
                    <button type="submit" name="action" value="reject" class="reject">Reject</button>
                    <!-- <a href="pre-enrolled.php" id="backButton"><i class="fas fa-arrow-left"></i>Back</a> -->
                <?php endif; ?>
                
                <?php if ($studentStatus === 'enrolled'): ?>
                    <button type="button" id="addScheduleButton">Add Schedule</button>
                    <!-- <a href="pre-enrolled.php" id="backButton"><i class="fas fa-arrow-left"></i>Back</a> -->
                <?php endif; ?>
            </form>
            <a href="pre-enrolled.php" id="backButton"><i class="fas fa-arrow-left"></i>Back</a>


        <!-- Add Schedule Modal -->
        <div id="addScheduleModal" class="modal">
            <div class="modal-header">
                <h3>Add Schedule</h3>
                <span class="close" id="closeModal">&times;</span>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label for="block">Block:</label>
                    <select name="block" id="block" required>
                        <option value="">Select Block</option>
                        <option value="Blk 1">Blk 1</option>
                        <option value="Blk 2">Blk 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject_name">Subject Name:</label>
                    <input type="text" name="subject_name" id="subject_name" required>
                </div>
                <div class="form-group">
                    <label for="start_time">Start Time:</label>
                    <select name="start_time" id="start_time" required>
                        <option value="">Select Start Time</option>
                        <option value="7:30 am">7:30 am</option>
                        <option value="8:30 am">8:30 am</option>
                        <option value="9:30 am">9:30 am</option>
                        <option value="10:30 am">10:30 am</option>
                        <option value="11:30 am">11:30 am</option>
                        <option value="12:30 pm">12:30 pm</option>
                        <option value="1:00 pm">1:00 pm</option>
                        <option value="2:00 pm">2:00 pm</option>
                        <option value="3:00 pm">3:00 pm</option>
                        <option value="4:00 pm">4:00 pm</option>
                        <option value="5:00 pm">5:00 pm</option>
                        <option value="6:00 pm">6:00 pm</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="end_time">End Time:</label>
                    <select name="end_time" id="end_time" required>
                        <option value="">Select End Time</option>
                        <option value="7:30 am">7:30 am</option>
                        <option value="8:30 am">8:30 am</option>
                        <option value="9:30 am">9:30 am</option>
                        <option value="10:30 am">10:30 am</option>
                        <option value="11:30 am">11:30 am</option>
                        <option value="12:30 pm">12:30 pm</option>
                        <option value="1:00 pm">1:00 pm</option>
                        <option value="2:00 pm">2:00 pm</option>
                        <option value="3:00 pm">3:00 pm</option>
                        <option value="4:00 pm">4:00 pm</option>
                        <option value="5:00 pm">5:00 pm</option>
                        <option value="6:00 pm">6:00 pm</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="days">Days:</label>
                    <select name="days" id="days" required>
                        <option value="">Select Days</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="room">Room:</label>
                    <select name="room" id="room" required>
                        <option value="">Select room</option>
                        <option value="101">101</option>
                        <option value="102">102</option>
                        <option value="104">104</option>
                        <option value="201">201</option>
                        <option value="202">202</option>
                        <option value="203">203</option>
                        <option value="GYM">GYM</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="instructor">Instructor:</label>
                    <select name="instructor" id="instructor" required>
                        <option value="">Select Instructor</option>
                        <option value="Mr. Nathaniel Sarzaba">Mr. Nathaniel Sarzaba</option>
                        <option value="Mr. Bojo Aquino">Mr. Bojo Aquino</option>
                        <option value="Mr Renz Tabbada">Mr Renz Tabbada</option>
                        <option value="Mr. Jericho Barcelon">Mr. Jericho Barcelon</option>
                        <option value="Ms. Belle Misalang">Ms. Belle Misalang</option>
                        <option value="Mrs. Thaniel Sarzaba">Mrs. Thaniel Sarzaba</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="action" value="add_schedule_subject">Add</button>
                    <button type="button" id="cancelModal" class="cancel">Cancel</button>
                </div>
            </form>
        </div>
        
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Image Modal logic
            const imageModal = document.getElementById("imageModal");
            const modalImage = document.getElementById("modalImage");
            const images = document.querySelectorAll(".clickable-image");

            // Add click event to all images
            images.forEach(function(img) {
                img.addEventListener("click", function() {
                    imageModal.style.display = "block"; // Show the image modal
                    modalImage.src = this.src; // Set the modal image source to the clicked image
                });
            });

            // Close image modal
            const closeImageModalButton = document.querySelector(".modalimg .close");
            if (closeImageModalButton) {
                closeImageModalButton.addEventListener("click", function() {
                    imageModal.style.display = "none"; // Hide the image modal
                });
            }

            // Add Schedule Modal logic
            const addScheduleButton = document.getElementById('addScheduleButton');
            const addScheduleModal = document.getElementById('addScheduleModal');
            const closeAddScheduleModalButton = document.getElementById('closeModal');
            const cancelAddScheduleModalButton = document.getElementById('cancelModal');

            // Open Add Schedule Modal
            if (addScheduleButton) {
                addScheduleButton.onclick = function() {
                    addScheduleModal.style.display = 'block'; // Show the modal
                }
            }

            // Close Add Schedule Modal
            if (closeAddScheduleModalButton) {
                closeAddScheduleModalButton.onclick = function() {
                    addScheduleModal.style.display = 'none'; // Hide the modal
                }
            }

            // Cancel Add Schedule Modal
            if (cancelAddScheduleModalButton) {
                cancelAddScheduleModalButton.onclick = function() {
                    addScheduleModal.style.display = 'none'; // Hide the modal
                }
            }
        });
    </script>
    <!-- <script src="../JS/view-pre-enrolled.js"></script> -->
</body>
</html>