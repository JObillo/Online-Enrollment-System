<?php 
// Require database connection
require('../database.php');

// Get the student_id from the URL
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if ($student_id === 0) {
    die("Invalid student ID.");
}

// Initialize student information array
$studentInformation = [];

// Query to get student information for the specific student
$queryStudentInformation = "
SELECT 
    s.student_id,
    s.first_name,
    s.last_name,
    ce.year_level,
    ce.semester,
    ce.course_name,
    ce.status
FROM 
    students s
INNER JOIN 
    courseenrollment ce ON s.student_id = ce.student_id
WHERE 
    s.student_id = $student_id;";

$result = mysqli_query($connection, $queryStudentInformation);
if (!$result) {
    die("Database query failed: " . mysqli_error($connection));
}

// Fetch student data
if (mysqli_num_rows($result) > 0) {
    $studentInformation = mysqli_fetch_assoc($result);
} else {
    echo "No student information found.";
    exit; // Stop further processing if no data is found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
</head>
<body>
    <h1>Student Information</h1>
    <div>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($studentInformation['last_name']) ?></p>
        <p><strong>First Name:</strong> <?= htmlspecialchars($studentInformation['first_name']) ?></p>
        <p><strong>Year Level:</strong> <?= htmlspecialchars($studentInformation['year_level']) ?></p>
        <p><strong>Semester:</strong> <?= htmlspecialchars($studentInformation['semester']) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($studentInformation['course_name']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($studentInformation['status']) ?></p>

        <form action="view-rejected.php?student_id=<?= $student_id ?>" method="post">
            <button type="submit" name="undo">Undo Rejection</button>
        </form>
    </div>

    <?php
    // Handle undo action
    if (isset($_POST['undo'])) {
        // Update the status back to 'pre-enrolled'
        $updateQuery = "UPDATE courseenrollment SET status = 'pre-enrolled' WHERE student_id = $student_id";
        
        if (mysqli_query($connection, $updateQuery)) {
            echo "<script>alert('Student status updated to pre-enrolled.'); window.location.href = 'rejected.php';</script>";
            exit;
        } else {
            die("Failed to update status: " . mysqli_error($connection));
        }
    }
    ?>
</body>
</html>
