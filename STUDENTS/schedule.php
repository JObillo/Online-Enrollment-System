<?php
    // Include the database connection
    include('../database.php');

    // Start the session to access session variables
    session_start();

    // Ensure that the user is logged in
    $user_id = $_SESSION['user_id'] ?? null;

    // If user_id is not set in the session, redirect to the login page
    if (!$user_id) {
        header('location:login.php');
        exit; // Stop further execution of the script
    }

    // Fetch schedules for the specific user
    $schedulesQuery = "SELECT block, subject_name, start_time, end_time, days, room, instructor FROM sched_sub WHERE user_id = $user_id";
    $schedulesResult = mysqli_query($connection, $schedulesQuery);
    if (!$schedulesResult) {
        die("Error fetching schedules: " . mysqli_error($connection));
    }

    // Fetch the first row for the block
    $firstRow = mysqli_fetch_assoc($schedulesResult);

    // Reset the result pointer to use the rest of the rows in the loop
    mysqli_data_seek($schedulesResult, 0);

    // Fetch user profile data using MySQLi
    $select_profile = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
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

    // Fetch the user's year level using the correct user_id
    $select_year = $connection->prepare("SELECT year_level FROM courseenrollment WHERE user_id = ?");
    $select_year->bind_param("i", $user_id); // Bind the actual user_id to the query
    $select_year->execute();
    $resultt = $select_year->get_result(); // Get the result set

    // Fetch the user's year level
    $fetch_year = $resultt->fetch_assoc();

    if (!$fetch_year) {
        echo "Year level not found!";
        exit;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule</title>
    <link rel="stylesheet" href="../CSS/student-page.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

</head>
<body>
    <div class="header">
        <img src="../IMAGES/logo(1).png" alt="School Logo" class="logo">
        <h1>Monarch College</h1>
    </div>
    <div class="sidebar">
        <div class="side-content">
            <div class="profile">
                <div class="profile-img" style="background-image: url(img/3.jpeg)"></div>
                <h4><?= htmlspecialchars($fetch_profile['full_name']); ?></h4>
                <small>Student</small>
            </div>
            <div class="side-menu">
                <ul>
                    <li><a href="student-page.php">Dashboard</a></li>
                    <!-- <li><a href="subjects.php">Subjects</a></li> -->
                    <li><a href="schedule.php" class="active">Registration</a></li>
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
                <h4>Registration Form</h4>
            </div>
            <div class="page-content">
                <div class="student-info">
                    <h2 id="student-name">Student name: <?= htmlspecialchars($fetch_profile['full_name']); ?></h2>
                    <h3 id="year-level">Year level: <?= htmlspecialchars($fetch_year['year_level']); ?></h3>
                    <?php if ($firstRow): ?>
                        <h3 id="block">Block: <?= htmlspecialchars($firstRow['block']); ?></h3>
                    <?php else: ?>
                        <h3 id="block">Block: Not Available</h3>
                    <?php endif; ?>
                </div>

                <div class="subjects-table">
                    <table id="scheduleTable">
                        <thead>
                            <tr>
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
                                    <!-- <td><?= htmlspecialchars($row['block']) ?></td> -->
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
                <div class="download-section" style="margin-top: 20px;">
                    <button id="download-btn" class="download-btn" style="padding: 10px 20px; background-color: #3a3fdb; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-download"></i> Download Registration Form</button>
                </div>
            </div>
        </main>
    </div>

<script>

    document.getElementById("download-btn").addEventListener("click", function () {
        // Select the table and student info
        const table = document.querySelector("#scheduleTable");
        const studentName = document.getElementById("student-name").innerText;
        const yearLevel = document.getElementById("year-level").innerText;
        const block = document.getElementById("block").innerText;

        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Set up the title - Centered header
        doc.setFont("helvetica", "bold");
        doc.setFontSize(20);
        const title = "MONARCH COLLEGE";
        const pageWidth = doc.internal.pageSize.width;
        doc.text(title, pageWidth / 2, 20, { align: "center" });

        // Set up the registration form label
        doc.setFontSize(12);
        doc.text("Registration Form", pageWidth / 2, 30, { align: "center" });

        // Add student information
        doc.setFont("helvetica", "normal");
        doc.text(` ${studentName}`, 14, 50);
        doc.text(` ${yearLevel}`, 14, 60);
        doc.text(` ${block}`, 14, 70);

        // Table setup: Column headers
        const headers = ["SUBJECT NAME", "START TIME", "END TIME", "DAYS", "ROOM", "INSTRUCTOR"];
        const colWidths = [50, 25, 25, 25, 25, 40]; // Width for each column
        const tableWidth = colWidths.reduce((a, b) => a + b, 0); // Total table width
        const startX = (pageWidth - tableWidth) / 2; // Center the table horizontally
        let startY = 80; // Start Y after student info

        // Draw header background
        doc.setFillColor(51, 122, 183); // Blue background
        doc.rect(startX, startY, tableWidth, 10, "F");

        // Add header text
        doc.setTextColor(255, 255, 255); // White text
        headers.forEach((header, index) => {
            const xPos = startX + colWidths.slice(0, index).reduce((a, b) => a + b, 0) + colWidths[index] / 2;
            doc.text(header, xPos, startY + 7, { align: "center" });
        });

        // Reset text color for table rows
        doc.setTextColor(0, 0, 0);
        startY += 12; // Move below the header

        // Add table rows
        const rows = table.querySelectorAll("tbody tr");
        rows.forEach(row => {
            const cols = row.querySelectorAll("td");
            let offsetX = startX;
            let rowHeight = 10; // Minimum row height

            // Calculate dynamic row height based on text wrapping
            const cellTexts = [];
            cols.forEach((col, index) => {
                const cellText = col.innerText.trim();
                const lines = doc.splitTextToSize(cellText, colWidths[index] - 2); // Wrap text
                rowHeight = Math.max(rowHeight, lines.length * 7); // Update row height
                cellTexts.push(lines);
            });

            // Draw table cells and text
            cols.forEach((col, index) => {
                const xPos = offsetX;
                const yPos = startY + rowHeight / 2; // Vertical centering

                doc.rect(offsetX, startY, colWidths[index], rowHeight); // Draw cell borders

                // Add text inside the cell (horizontal centering)
                const lines = cellTexts[index];
                lines.forEach((line, lineIndex) => {
                    const textX = offsetX + colWidths[index] / 2; // Horizontal centering
                    const textY = startY + 5 + lineIndex * 5; // Vertical positioning with line spacing
                    doc.text(line, textX, textY, { align: "center" });
                });

                offsetX += colWidths[index]; // Move to the next column
            });

            // Move to the next row
            startY += rowHeight;
        });

        // Save the PDF with a name
        doc.save("registration-form.pdf");
    });

</script>
</body>
</html>