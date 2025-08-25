<?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Fetch company details
$sql = "SELECT * FROM Companies WHERE company_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();

if (!$company) {
    die("Company not found.");
}

// Pagination for Job Listings
$jobs_per_page = 5; // Number of jobs to display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$offset = ($page - 1) * $jobs_per_page;

// Fetch total number of jobs
$sql = "SELECT COUNT(*) AS total FROM Jobs WHERE company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$total_jobs = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_jobs / $jobs_per_page);

// Fetch jobs for the current page
$sql = "SELECT * FROM Jobs WHERE company_id = ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $company_id, $jobs_per_page, $offset);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch applications for the company's jobs
$sql = "SELECT Applications.*, Students.first_name, Students.last_name, Students.email, Students.cgpa, Jobs.job_title 
        FROM Applications 
        JOIN Students ON Applications.student_id = Students.student_id 
        JOIN Jobs ON Applications.job_id = Jobs.job_id 
        WHERE Jobs.company_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $company_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch students with scheduled interviews
$sql = "SELECT Interviews.*, Students.first_name, Students.last_name, Students.email, Students.cgpa, Jobs.job_title 
        FROM Interviews 
        JOIN Applications ON Interviews.application_id = Applications.application_id 
        JOIN Students ON Applications.student_id = Students.student_id 
        JOIN Jobs ON Applications.job_id = Jobs.job_id 
        WHERE Jobs.company_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $company_id);
$stmt->execute();
$interview_students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch placed students
$sql = "SELECT Placements.*, Students.first_name, Students.last_name, Students.email, Students.cgpa, Jobs.job_title 
        FROM Placements 
        JOIN Students ON Placements.student_id = Students.student_id 
        JOIN Jobs ON Placements.job_id = Jobs.job_id 
        WHERE Jobs.company_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $company_id);
$stmt->execute();
$placed_students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($company['company_name']); ?>!</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>

        <!-- Profile Section -->
        <section class="profile-section">
            <h2>Profile</h2>
            <div class="profile-details">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($company['company_name']); ?></p>
                <p><strong>Industry:</strong> <?php echo htmlspecialchars($company['industry_type']); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($company['contact_person']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($company['contact_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($company['contact_number']); ?></p>
                <a href="edit_profile.php" class="edit-profile-btn">Edit Profile</a>
            </div>
        </section>

        <!-- Job Posting Section -->
        <section class="job-posting-section">
            <h2>Post a New Job</h2>
            <a href="post_job.php" class="post-job-btn">Post Job</a>
        </section>

        <!-- Job Listings Section -->
        <section class="job-listings-section">
            <h2>Your Job Postings</h2>
            <div class="job-list">
                <?php if (count($jobs) > 0): ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-card">
                            <h3><?php echo htmlspecialchars($job['job_title']); ?></h3>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($job['job_description']); ?></p>
                            <p><strong>Package:</strong> <?php echo htmlspecialchars($job['package_offered']); ?></p>
                            <p><strong>Deadline:</strong> <?php echo htmlspecialchars($job['application_deadline']); ?></p>
                            <a href="view_applications.php?job_id=<?php echo $job['job_id']; ?>" class="view-applications-btn">View Applications</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No job postings found.</p>
                <?php endif; ?>
            </div>
            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                <?php endif; ?>
                <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Applications Section -->
        <section class="applications-section">
            <h2>Applications Received</h2>
            <div class="application-list">
                <?php if (count($applications) > 0): ?>
                    <?php foreach ($applications as $application): ?>
                        <div class="application-card">
                            <h3><?php echo htmlspecialchars($application['first_name'] . ' ' . htmlspecialchars($application['last_name'])); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
                            <p><strong>CGPA:</strong> <?php echo htmlspecialchars($application['cgpa']); ?></p>
                            <p><strong>Job:</strong> <?php echo htmlspecialchars($application['job_title']); ?></p>
                            <p><strong>Status:</strong> <span class="status <?php echo strtolower($application['status']); ?>"><?php echo htmlspecialchars($application['status']); ?></span></p>
                            <a href="view_application.php?id=<?php echo $application['application_id']; ?>" class="view-btn">View Application</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No applications received yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Students with Scheduled Interviews Section -->
        <section class="interview-students-section">
            <h2>Students with Scheduled Interviews</h2>
            <div class="interview-students-list">
                <?php if (count($interview_students) > 0): ?>
                    <?php foreach ($interview_students as $student): ?>
                        <div class="student-card">
                            <h3><?php echo htmlspecialchars($student['first_name'] . ' ' . htmlspecialchars($student['last_name'])); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                            <p><strong>CGPA:</strong> <?php echo htmlspecialchars($student['cgpa']); ?></p>
                            <p><strong>Job Title:</strong> <?php echo htmlspecialchars($student['job_title']); ?></p>
                            <p><strong>Interview Date:</strong> <?php echo htmlspecialchars($student['interview_date']); ?></p>
                            <p><strong>Interview Type:</strong> <?php echo htmlspecialchars($student['interview_type']); ?></p>
                            <p><strong>Interview Status:</strong> <?php echo htmlspecialchars($student['interview_status'] ?? 'Pending'); ?></p>
                            <p><strong>Feedback:</strong> <?php echo htmlspecialchars($student['interview_feedback'] ?? 'No feedback yet'); ?></p>
                            <!-- Link to update interview result -->
                            <a href="interview_result.php?interview_id=<?php echo $student['interview_id']; ?>" class="update-result-btn">Update Result</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No interviews have been scheduled yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Placed Students Section -->
        <section class="placed-students-section">
            <h2>Placed Students</h2>
            <div class="placed-students-list">
                <?php if (count($placed_students) > 0): ?>
                    <?php foreach ($placed_students as $student): ?>
                        <div class="student-card">
                            <h3><?php echo htmlspecialchars($student['first_name'] . ' ' . htmlspecialchars($student['last_name'])); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                            <p><strong>CGPA:</strong> <?php echo htmlspecialchars($student['cgpa']); ?></p>
                            <p><strong>Job Title:</strong> <?php echo htmlspecialchars($student['job_title']); ?></p>
                            <p><strong>Placement Date:</strong> <?php echo htmlspecialchars($student['placement_date']); ?></p>
                            <p><strong>Package Offered:</strong> <?php echo htmlspecialchars($student['package_offered']); ?></p>
                            <p><strong>Joining Date:</strong> <?php echo htmlspecialchars($student['joining_date']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No students have been placed yet.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>