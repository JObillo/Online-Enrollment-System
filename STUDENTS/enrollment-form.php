<?php
require('../database.php');
session_start(); // Ensure session is started

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['submit'])) {
    // Collecting form data
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; // Get user_id from session

    if (empty($user_id)) {
        echo "Error: user_id is missing or not logged in.";
        exit; // Stop execution if no user_id is found
    }

    // Extract form inputs
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $religion = $_POST['religion'];
    $civil_status = $_POST['civil_status'];
    $email = $_POST['email'];
    $phone_no = $_POST['student_phoneNo'];
    $barangay = $_POST['barangay'];
    $municipal = $_POST['municipal'];
    $province = $_POST['province'];
    $country = $_POST['country'];
    $father_last_name = $_POST['father_last_name'];
    $father_first_name = $_POST['father_first_name'];
    $father_middle_name = $_POST['father_middle_name'];
    $father_occupation = $_POST['father_occupation'];
    $father_phone_no = $_POST['father_phone_no'];
    $mother_last_name = $_POST['mother_last_name'];
    $mother_first_name = $_POST['mother_first_name'];
    $mother_middle_name = $_POST['mother_middle_name'];
    $mother_occupation = $_POST['mother_occupation'];
    $mother_phone_no = $_POST['mother_phone_no'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $course_name = $_POST['course_name'];
    $last_school_attended = $_POST['last_school_attended'];
    $strand = $_POST['strand'];
    $year_graduated = $_POST['year_graduated'];
    $general_average = $_POST['general_average'];

    // Check if the user is a transferee
    $is_transferee = isset($_POST['is_transferee']) && $_POST['is_transferee'] === 'Yes';
    $transfer_last_school = $_POST['transfer_last_school'] ?? '';
    $transfer_last_year = $_POST['transfer_last_year'] ?? '';
    $transfer_course = $_POST['transfer_course'] ?? '';

    // Begin transaction
    $connection->begin_transaction();

    try {
        // Insert data into students table
        $queryStudents = "INSERT INTO students (user_id, last_name, first_name, middle_name, birthdate, gender, religion, civil_status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtStudents = $connection->prepare($queryStudents);
        $stmtStudents->bind_param('ssssssss', $user_id, $last_name, $first_name, $middle_name, $birthdate, $gender, $religion, $civil_status);
        $stmtStudents->execute();

        // Insert data into contactinfo table
        $queryContactinfo = "INSERT INTO contactinfo (user_id, email, phone_no) 
                             VALUES (?, ?, ?)";
        $stmtContactinfo = $connection->prepare($queryContactinfo);
        $stmtContactinfo->bind_param('sss', $user_id, $email, $phone_no);
        $stmtContactinfo->execute();

        // Insert data into address table
        $queryAddress = "INSERT INTO address (user_id, barangay, municipal, province, country) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmtAddress = $connection->prepare($queryAddress);
        $stmtAddress->bind_param('sssss', $user_id, $barangay, $municipal, $province, $country);
        $stmtAddress->execute();

        // Insert data into parents table
        $queryParents = "INSERT INTO parents (user_id, father_last_name, father_first_name, father_middle_name, father_occupation, father_phone_no, 
                                              mother_last_name, mother_first_name, mother_middle_name, mother_occupation, mother_phone_no) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtParents = $connection->prepare($queryParents);
        $stmtParents->bind_param(
            'sssssssssss',
            $user_id,
            $father_last_name,
            $father_first_name,
            $father_middle_name,
            $father_occupation,
            $father_phone_no,
            $mother_last_name,
            $mother_first_name,
            $mother_middle_name,
            $mother_occupation,
            $mother_phone_no
        );
        $stmtParents->execute();

        // Insert data into courseenrollment table
        $queryCourseEnrollment = "INSERT INTO courseenrollment (user_id, course_name, year_level, semester) 
                                  VALUES (?, ?, ?, ?)";
        $stmtCourseEnrollment = $connection->prepare($queryCourseEnrollment);
        $stmtCourseEnrollment->bind_param('ssss', $user_id, $course_name, $year_level, $semester);
        $stmtCourseEnrollment->execute();

        // Insert data into education table
        $queryEducation = "INSERT INTO education (user_id, senior_high_school, strand, year_graduated, general_average) 
                           VALUES (?, ?, ?, ?, ?)";
        $stmtEducation = $connection->prepare($queryEducation);
        $stmtEducation->bind_param('sssss', $user_id, $last_school_attended, $strand, $year_graduated, $general_average);
        $stmtEducation->execute();

        // Insert data into transfer table (if applicable)
        if ($transfer_last_school && $transfer_last_year && $transfer_course && 
            isset($_FILES['transferee_tor_file']) && $_FILES['transferee_tor_file']['error'] === UPLOAD_ERR_OK && 
            isset($_FILES['transferee_pic_file']) && $_FILES['transferee_pic_file']['error'] === UPLOAD_ERR_OK) {
            
            // Handling TOR file
            $fileTypeTor = 'Image'; // Assuming the file is an image
            $fileContentTor = file_get_contents($_FILES['transferee_tor_file']['tmp_name']);
            $fileNameTor = $_FILES['transferee_tor_file']['name'];

            // Handling 2x2 picture file
            $fileTypePic = 'Image'; // Assuming the file is an image
            $fileContentPic = file_get_contents($_FILES['transferee_pic_file']['tmp_name']);
            $fileNamePic = $_FILES['transferee_pic_file']['name'];

            $queryTransfer = "INSERT INTO transferee (user_id, transfer_last_school, transfer_last_year, transfer_course, 
                            type_tor, file, file_name, type_picture, file_picture, file_name_picture, upload_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmtTransfer = $connection->prepare($queryTransfer);
            $stmtTransfer->bind_param('ssssssssss', $user_id, $transfer_last_school, $transfer_last_year, $transfer_course, 
                                    $fileTypeTor, $fileContentTor, $fileNameTor, $fileTypePic, $fileContentPic, $fileNamePic);
            $stmtTransfer->execute();

            echo "File uploaded successfully to the database!";
        } else {
            $errorTor = $_FILES['transferee_tor_file']['error'] ?? 'No TOR provided.';
            $errorPic = $_FILES['transferee_pic_file']['error'] ?? 'No 2x2 picture provided.';
            echo "Error: File upload failed. Error code: $errorTor and $errorPic";
        }

        if (isset($_FILES['requirement_file_grade']) && $_FILES['requirement_file_grade']['error'] === UPLOAD_ERR_OK && 
        isset($_FILES['requirement_file_pic']) && $_FILES['requirement_file_pic']['error'] === UPLOAD_ERR_OK) {
    
        // File details for grade file
        $fileTypeGrade = 'Image';
        $fileContentGrade = file_get_contents($_FILES['requirement_file_grade']['tmp_name']);
        $fileNameGrade = $_FILES['requirement_file_grade']['name'];
    
        // File details for picture file
        $fileTypePic = 'Image';
        $fileContentPic = file_get_contents($_FILES['requirement_file_pic']['tmp_name']);
        $fileNamePic = $_FILES['requirement_file_pic']['name'];
    
        // Insert files into the database
        $queryRequirements = "INSERT INTO requirements (user_id, type_grade, file_grade, file_name_grade, type_picture, file_picture, file_name_picture, upload_date) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmtRequirements = $connection->prepare($queryRequirements);
        $stmtRequirements->bind_param('sssssss', $user_id, $fileTypeGrade, $fileContentGrade, $fileNameGrade, $fileTypePic, $fileContentPic, $fileNamePic);
        $stmtRequirements->send_long_data(2, $fileContentGrade); // Handle large file content
        $stmtRequirements->send_long_data(3, $fileContentPic);  // Handle large file content
        $stmtRequirements->execute();
    
            echo "Files uploaded successfully!";
        } else {
            $errorGrade = $_FILES['requirement_file_grade']['error'] ?? 'No grade file provided.';
            $errorPic = $_FILES['requirement_file_pic']['error'] ?? 'No 2x2 picture provided.';
            echo "Error: File upload failed. Error code: $errorGrade and $errorPic";
        }

        // Commit the transaction
        $connection->commit();

        echo "<script>alert('Enrollment successful!'); window.location.href='../STUDENTS/student-page.php';</script>";
    } catch (Exception $e) {
        // If any query fails, rollback the transaction
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/enrollment-form.css">
    <title>Monarch Online Enrollment</title>
</head>
<body>
    <div class="header-container">
        <img src="../IMAGES/logo(1).png" alt="logo picture" class="logo">
        <h1>Monarch College Enrollment Form</h1>
    </div>    
    <div class="form-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
           
            
            <h3 class="box1">Basic Information</h3>
            <label for="last_name"></label>
            <input type="text" id="last_name" name="last_name" placeholder="Last Name" required>
            
            <label for="first_name"></label>
            <input type="text" id="first_name" name="first_name" placeholder="First Name" required>
            
            <label for="middle_name"></label>
            <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" required> 
            <br> <br>

            <label for="birthdate">Birthdate</label>
            <input type="date" id="birthdate" name="birthdate" required> 

            <label for="gender" class="gender">Gender</label>
            <select name="gender" id="gender">
                <option value="">Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label for="religion" class="religion">Religion</label>
            <select id="religion" name="religion" required>
                <option value="">Select Religion</option>
                <option value="Roman Catholic">Catholic</option>
                <option value="Iglesia Ni Cristo">Iglesia Ni Cristo</option>
                <option value="Jehovah Witnesses">Jehovah Witnesses</option>
                <option value="Islam">Islam</option>
            </select>

            <label for="civil_status" class="civil_status">Civil Status</label>
            <select name="civil_status" id="civil_status">
                <option value="">Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Divorce">Divorce</option>

            </select>
            <br> <br>

            <h3 class="box1">Contact Information</h3>
                <div class="Contact_row">
                    <div class="contact-info">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                        <div id="email-error-message" class="error-message"></div>
                    </div>

                    <div class="contact-info">
                        <label for="student_phoneNo">Phone No</label>
                        <input type="tel" id="student_phoneNo" name="student_phoneNo" maxlength="11" required>
                        <div id="error-message" class="error-message"></div>
                    </div>
                </div>
            <br> <br>

            <h3 class="box1">Address</h3>
            <label for="barangay">Barangay</label>
            <input type="text" id="barangay" name="barangay" required>
        
            <label for="municipal">Municipality</label>
            <input type="text" id="municipal" name="municipal" required> <br> <br>
            
            <label for="province">Province</label>
            <input type="text" id="province" name="province" required>
            
            <label for="country">Country</label>
            <input type="text" id="country" name="country" required>

            <h3 class="box1">Parents</h3>
            <h3>Father</h3>
            <label for="father_last_name"></label>
            <input type="text" id="father_last_name" name="father_last_name" placeholder="Last Name" required>
            
            <label for="father_first_name"></label>
            <input type="text" id="father_first_name" name="father_first_name" placeholder="First Name" required>
            
            <label for="father_middle_name"></label>
            <input type="text" id="father_middle_name" name="father_middle_name" placeholder="Middle Name"> <br> <br>

            <label for="father_occupation"></label>
            <input type="text" id="father_occupation" name="father_occupation" placeholder="Occupation" required> 
            
            <label for="father_phone_no"></label>
            <input type="tel" id="father_phone_no" name="father_phone_no" placeholder="Phone No" maxlength="11" required>
            <div id="error-father-phoneno" class="error-father-phoneno"></div>

            <br> <br>
            <h3>Mother</h3>

            <label for="mother_last_name"></label>
            <input type="text" id="mother_last_name" name="mother_last_name" placeholder="Last Name" required>
            
            <label for="mother_first_name"></label>
            <input type="text" id="mother_first_name" name="mother_first_name" placeholder="First Name" required>
            
            <label for="mother_middle_name"></label>
            <input type="text" id="mother_middle_name" name="mother_middle_name" placeholder="Middle Name"> <br> <br>

            <label for="mother_occupation"></label>
            <input type="text" id="mother_occupation" name="mother_occupation" placeholder="Occupation" required> 
            
            <label for="mother_phone_no"></label>
            <input type="tel" id="mother_phone_no" name="mother_phone_no" placeholder="Phone No" required>
            <div id="error-mother-phoneno" class="error-mother-phoneno"></div>
            <br> <br>
             
             <h3 class="box1">Choose Course</h3>
            <label for="semester" class="sem">Semester:</label>
            <input type="hidden" id="semester" name="semester" value="First Semester">
            <span class="sem">First Semester</span> <br> <br>

            <label for="course_name" class="Course">Courses</label>
            <select id="course_name" name="course_name" required>
                <option value="">Select Course</option>
                <option value="BS-Information Technology">Information Technology</option>
                <option value="BS-Computer Science">Computer Science</option>
            </select> <br> <br>

            <div>
                <label for="year_level">Year Level</label>
                <select id="year_level" name="year_level" onchange="toggleSections();">
                    <option value="">Select Year Level</option>
                    <option value="1st Year">First Year</option>
                    <option value="2nd Year">Second Year</option>
                    <option value="3rd Year">Third Year</option>
                    <option value="4th Year">Fourth Year</option>
                </select>
            </div>

            <div class="TR">
                <label for="is_transferee">Are you a transferee?</label>
                <input type="checkbox" id="is_transferee" name="is_transferee" value="Yes" onclick="toggleSections()"> Yes
            </div>

            <div id="transfereeSection" style="display:none;">
                <h3 class="box1">Transferee Information</h3>
                <label for="transfer_last_school">Last School Attended</label>
                <input type="text" id="transfer_last_school" name="transfer_last_school"><br>
                
                <label for="transfer_last_year">Last Year Attended</label>
                <input type="text" id="transfer_last_year" name="transfer_last_year"><br>
                
                <label for="transfer_course">Course Taken</label>
                <input type="text" id="transfer_course" name="transfer_course"><br>

                <div class="file-upload-container">
                    <label for="transferee_tor_file">Upload your TOR</label>
                    <input type="file" id="transferee_tor_file" name="transferee_tor_file" accept="image/*">
                    <label for="transferee_pic_file">Upload 2x2 Picture</label>
                    <input type="file" id="transferee_pic_file" name="transferee_pic_file" accept="image/*">
                </div>
            </div>

            <div id="educationSection" style="display:none;">
                <h3 class="box1">Education</h3>
                <h4>Senior High School</h4>
                <label for="last_school_attended"></label>
                <input type="text" id="last_school_attended" name="last_school_attended" placeholder="Last School Attended">

                <label for="strand"></label>
                <input type="text" id="strand" name="strand" placeholder="SHS Strand" required> <br> <br>

                <label for="year_graduated"></label>
                <input type="text" id="year_graduated" name="year_graduated" placeholder="Year Graduated" required>

                <label for="general_average"></label>
                <input type="text" id="general_average" name="general_average" placeholder="General Average" required>
                <br>
            </div>

            <div id="requirementsSection" style="display:none;">
                <h3>Requirements</h3>
                <div class="file-upload-container">
                    <label for="requirement_file_grade">Senior High School Grades</label>
                    <input type="file" id="requirement_file_grade" name="requirement_file_grade" accept="image/*">
                    <label for="requirement_file_pic">Upload 2x2 Picture</label>
                    <input type="file" id="requirement_file_pic" name="requirement_file_pic" accept="image/*">
                </div>
            </div>

            <input class="submit" type="submit" name="submit" value="Enroll">

            <br><br>
            <a href="student-page.php" class="btn">Back</a>

            <a href="view-pre-enrolled.php?user_id=<?php echo htmlspecialchars($row['user_id']); ?>" title="View"><span class="las la-eye"></span></a>
        </form>
    </div>
    <script src="../JS/new.js"></script>
</body>
</html>