<?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Fetch departments for the dropdown
$sql = "SELECT * FROM Departments";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching departments: " . $conn->error);
}
$departments = $result->fetch_all(MYSQLI_ASSOC);

// Handle job posting
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_title = $_POST['job_title'];
    $job_description = $_POST['job_description'];
    $required_skills = $_POST['required_skills'];
    $package_offered = $_POST['package_offered'];
    $location = $_POST['location'];
    $job_type = $_POST['job_type'];
    $application_deadline = $_POST['application_deadline'];
    $minimum_cgpa = $_POST['minimum_cgpa'];
    $backlogs_criteria = $_POST['backlogs_criteria'];
    $department_id = $_POST['department_id'];

    // Insert the job into the database
    $sql = "INSERT INTO Jobs (company_id, department_id, job_title, job_description, required_skills, package_offered, location, job_type, application_deadline, minimum_cgpa, backlogs_criteria) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iisssdsssss", $company_id, $department_id, $job_title, $job_description, $required_skills, $package_offered, $location, $job_type, $application_deadline, $minimum_cgpa, $backlogs_criteria);
    if ($stmt->execute()) {
        $success = "Job posted successfully!";
    } else {
        $error = "Failed to post job.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Post a New Job</h1>
            <a href="company_dashboard.php" class="back-btn">Back to Dashboard</a>
        </header>

        <section class="post-job-section">
            <h2>Post a New Job</h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <label for="job_title">Job Title:</label>
                <input type="text" id="job_title" name="job_title" required>

                <label for="job_description">Job Description:</label>
                <textarea id="job_description" name="job_description" required></textarea>

                <label for="required_skills">Required Skills:</label>
                <textarea id="required_skills" name="required_skills" required></textarea>

                <label for="package_offered">Package Offered:</label>
                <input type="number" id="package_offered" name="package_offered" required>

                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>

                <label for="job_type">Job Type:</label>
                <select id="job_type" name="job_type" required>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Internship">Internship</option>
                </select>

                <label for="department_id">Department:</label>
                <select id="department_id" name="department_id" required>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo $department['department_id']; ?>">
                            <?php echo htmlspecialchars($department['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="application_deadline">Application Deadline:</label>
                <input type="date" id="application_deadline" name="application_deadline" required>

                <label for="minimum_cgpa">Minimum CGPA:</label>
                <input type="number" step="0.1" id="minimum_cgpa" name="minimum_cgpa" required>

                <label for="backlogs_criteria">Backlogs Criteria:</label>
                <select id="backlogs_criteria" name="backlogs_criteria" required>
                    <option value="No current arrears">No current arrears</option>
                    <option value="No history of arrears">No history of arrears</option>
                    <option value="1-2 arrears">1-2 arrears</option>
                </select>

                <button type="submit">Post Job</button>
            </form>
        </section>
    </div>
</body>
</html>