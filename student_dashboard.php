<?php
session_start();

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

include 'includes/db_connection.php';

// Fetch student details
$student_id = $_SESSION['student_id'];
$sql = "SELECT * FROM Students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Fetch department name
$sql = "SELECT department_name FROM Departments WHERE department_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student['department_id']);
$stmt->execute();
$result = $stmt->get_result();
$department = $result->fetch_assoc();

// Fetch current training programs the student is involved in
$sql = "SELECT TrainingDetails.*, Trainers.trainer_name 
        FROM TrainingDetails 
        JOIN Trainers ON TrainingDetails.trainer_id = Trainers.trainer_id 
        JOIN Students ON TrainingDetails.department_id = Students.department_id 
        WHERE Students.student_id = ? 
        AND TrainingDetails.training_start_date <= CURDATE() 
        AND TrainingDetails.training_end_date >= CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$current_trainings = $result->fetch_all(MYSQLI_ASSOC);

// Fetch upcoming training programs for the student's department
$sql = "SELECT TrainingDetails.*, Trainers.trainer_name 
        FROM TrainingDetails 
        JOIN Trainers ON TrainingDetails.trainer_id = Trainers.trainer_id 
        WHERE TrainingDetails.department_id = ? 
        AND TrainingDetails.training_start_date > CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student['department_id']);
$stmt->execute();
$result = $stmt->get_result();
$upcoming_trainings = $result->fetch_all(MYSQLI_ASSOC);

// Fetch jobs related to the student's department
$sql = "SELECT Jobs.*, Companies.company_name 
        FROM Jobs 
        JOIN Companies ON Jobs.company_id = Companies.company_id 
        WHERE Jobs.department_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student['department_id']);
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result->fetch_all(MYSQLI_ASSOC);

// Fetch applications submitted by the student
$sql = "SELECT Applications.*, Jobs.job_title, Companies.company_name 
        FROM Applications 
        JOIN Jobs ON Applications.job_id = Jobs.job_id 
        JOIN Companies ON Jobs.company_id = Companies.company_id 
        WHERE Applications.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$applications = $result->fetch_all(MYSQLI_ASSOC);

// Fetch interview calls for the student
$sql = "SELECT Interviews.*, Jobs.job_title, Companies.company_name 
        FROM Interviews 
        JOIN Applications ON Interviews.application_id = Applications.application_id 
        JOIN Jobs ON Applications.job_id = Jobs.job_id 
        JOIN Companies ON Jobs.company_id = Companies.company_id 
        WHERE Applications.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$interviews = $result->fetch_all(MYSQLI_ASSOC);

// Fetch placement details for the student
$sql = "SELECT Placements.*, Companies.company_name, Jobs.job_title 
        FROM Placements 
        JOIN Jobs ON Placements.job_id = Jobs.job_id 
        JOIN Companies ON Jobs.company_id = Companies.company_id 
        WHERE Placements.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$placement = $result->fetch_assoc();

// Handle job application form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_job'])) {
    $job_id = $_POST['job_id'];
    $application_date = date("Y-m-d H:i:s");

    // Check if the student is eligible for the job
    $sql = "SELECT * FROM Jobs WHERE job_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();

    if ($student['cgpa'] >= $job['minimum_cgpa'] && $student['current_arrears'] <= $job['backlogs_criteria']) {
        // Insert application into the database
        $sql = "INSERT INTO Applications (student_id, job_id, application_date, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $student_id, $job_id, $application_date);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $success = "Job application submitted successfully!";
        } else {
            $error = "Failed to submit job application.";
        }
    } else {
        $error = "You are not eligible for this job.";
    }
}

// Handle application removal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_application'])) {
    $application_id = $_POST['application_id'];

    // Delete the application from the database
    $sql = "DELETE FROM Applications WHERE application_id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $application_id, $student_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Application removed successfully!";
    } else {
        $error = "Failed to remove application.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($student['first_name']); ?>!</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>

        <!-- Profile Section -->
        <section class="profile-section">
            <h2>Profile</h2>
            <div class="profile-details">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . htmlspecialchars($student['last_name'])); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <p><strong>CGPA:</strong> <?php echo htmlspecialchars($student['cgpa']); ?></p>
                <p><strong>Arrears:</strong> <?php echo htmlspecialchars($student['current_arrears']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($department['department_name']); ?></p>
                <a href="update_profile.php" class="edit-profile-btn">Update Profile</a>
            </div>
        </section>

        <!-- Placement Status Section -->
        <section class="placement-section">
            <h2>Placement Status</h2>
            <?php if ($placement): ?>
                <div class="placement-details">
                    <p><strong>Congratulations! You have been placed.</strong></p>
                    <p><strong>Company:</strong> <?php echo htmlspecialchars($placement['company_name']); ?></p>
                    <p><strong>Job Title:</strong> <?php echo htmlspecialchars($placement['job_title']); ?></p>
                    <p><strong>Package Offered:</strong> $<?php echo htmlspecialchars($placement['package_offered']); ?></p>
                    <p><strong>Placement Date:</strong> <?php echo htmlspecialchars($placement['placement_date']); ?></p>
                    <p><strong>Joining Date:</strong> <?php echo htmlspecialchars($placement['joining_date']); ?></p>
                </div>
            <?php else: ?>
                <p>You have not been placed yet.</p>
            <?php endif; ?>
        </section>

        <!-- Current Training Programs Section -->
        <section class="current-trainings-section">
            <h2>Current Training Programs</h2>
            <div class="training-list">
                <?php
                if (count($current_trainings) > 0) {
                    foreach ($current_trainings as $training) {
                        echo "<div class='training-card'>";
                        echo "<h3>" . htmlspecialchars($training['training_name']) . "</h3>";
                        echo "<p><strong>Trainer:</strong> " . htmlspecialchars($training['trainer_name']) . "</p>";
                        echo "<p><strong>Date:</strong> " . htmlspecialchars($training['training_start_date']) . " to " . htmlspecialchars($training['training_end_date']) . "</p>";
                        echo "<p><strong>Venue:</strong> " . htmlspecialchars($training['training_venue']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>You are not currently enrolled in any training programs.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Upcoming Training Programs Section -->
        <section class="upcoming-trainings-section">
            <h2>Upcoming Training Programs</h2>
            <div class="training-list">
                <?php
                if (count($upcoming_trainings) > 0) {
                    foreach ($upcoming_trainings as $training) {
                        echo "<div class='training-card'>";
                        echo "<h3>" . htmlspecialchars($training['training_name']) . "</h3>";
                        echo "<p><strong>Trainer:</strong> " . htmlspecialchars($training['trainer_name']) . "</p>";
                        echo "<p><strong>Date:</strong> " . htmlspecialchars($training['training_start_date']) . " to " . htmlspecialchars($training['training_end_date']) . "</p>";
                        echo "<p><strong>Venue:</strong> " . htmlspecialchars($training['training_venue']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No upcoming training programs for your department.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Job Applications Section -->
        <section class="jobs-section">
            <h2>Job Applications</h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="job-list">
                <?php
                if (count($jobs) > 0) {
                    foreach ($jobs as $job) {
                        // Check eligibility
                        $is_eligible = ($student['cgpa'] >= $job['minimum_cgpa'] && $student['current_arrears'] <= $job['backlogs_criteria']);

                        echo "<div class='job-card'>";
                        echo "<h3>" . htmlspecialchars($job['job_title']) . "</h3>";
                        echo "<p><strong>Company:</strong> " . htmlspecialchars($job['company_name']) . "</p>";
                        echo "<p><strong>Package:</strong> $" . htmlspecialchars($job['package_offered']) . "</p>";
                        echo "<p><strong>Location:</strong> " . htmlspecialchars($job['location']) . "</p>";
                        echo "<p><strong>Eligibility:</strong> " . ($is_eligible ? "Eligible" : "Not Eligible") . "</p>";
                        if ($is_eligible) {
                            echo "<form method='POST' action='student_dashboard.php'>";
                            echo "<input type='hidden' name='job_id' value='" . htmlspecialchars($job['job_id']) . "'>";
                            echo "<button type='submit' name='apply_job' class='apply-btn'>Apply</button>";
                            echo "</form>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p>No job postings available for your department.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Applied Jobs Section -->
        <section class="applications-section">
            <h2>Your Applications</h2>
            <div class="applications-list">
                <?php
                if (count($applications) > 0) {
                    foreach ($applications as $application) {
                        echo "<div class='application-card'>";
                        echo "<h3>" . htmlspecialchars($application['job_title']) . "</h3>";
                        echo "<p><strong>Company:</strong> " . htmlspecialchars($application['company_name']) . "</p>";
                        echo "<p><strong>Status:</strong> " . htmlspecialchars($application['status']) . "</p>";
                        echo "<p><strong>Applied On:</strong> " . htmlspecialchars($application['application_date']) . "</p>";
                        // Add a form to remove the application
                        echo "<form method='POST' action='student_dashboard.php'>";
                        echo "<input type='hidden' name='application_id' value='" . htmlspecialchars($application['application_id']) . "'>";
                        echo "<button type='submit' name='remove_application' class='remove-btn'>Remove Application</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>You have not applied for any jobs yet.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Interview Calls Section -->
        <section class="interviews-section">
            <h2>Interview Calls</h2>
            <div class="interview-list">
                <?php
                if (count($interviews) > 0) {
                    foreach ($interviews as $interview) {
                        echo "<div class='interview-card'>";
                        echo "<h3>" . htmlspecialchars($interview['job_title']) . "</h3>";
                        echo "<p><strong>Company:</strong> " . htmlspecialchars($interview['company_name']) . "</p>";
                        echo "<p><strong>Date:</strong> " . htmlspecialchars($interview['interview_date']) . "</p>";
                        echo "<p><strong>Status:</strong> " . htmlspecialchars($interview['interview_status']) . "</p>";
                        echo "<p><strong>Feedback:</strong> " . htmlspecialchars($interview['interview_feedback']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No interview calls yet.</p>";
                }
                ?>
            </div>
        </section>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>