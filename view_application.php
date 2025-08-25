<?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

// Check if the application ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid application ID.");
}

$application_id = $_GET['id'];
$company_id = $_SESSION['company_id'];

// Fetch application details
$sql = "SELECT Applications.*, Students.first_name, Students.last_name, Students.email, Students.cgpa, Jobs.job_title 
        FROM Applications 
        JOIN Students ON Applications.student_id = Students.student_id 
        JOIN Jobs ON Applications.job_id = Jobs.job_id 
        WHERE Applications.application_id = ? AND Jobs.company_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("ii", $application_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

if (!$application) {
    die("Application not found or you do not have permission to view it.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Application Details</h1>
            <a href="company_dashboard.php" class="logout-btn">Back to Dashboard</a>
        </header>

        <section class="application-details">
            <h2>Application for: <?php echo htmlspecialchars($application['job_title']); ?></h2>
            <div class="details-card">
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($application['first_name'] . ' ' . htmlspecialchars($application['last_name'])); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
                <p><strong>CGPA:</strong> <?php echo htmlspecialchars($application['cgpa']); ?></p>
                <p><strong>Application Status:</strong> <span class="status <?php echo strtolower($application['status']); ?>"><?php echo htmlspecialchars($application['status']); ?></span></p>
                <!-- Removed Applied On Field -->
            </div>

            <!-- Update Application Status Form -->
            <form action="update_application_status.php" method="POST" class="status-form">
                <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                <label for="status"><strong>Update Status:</strong></label>
                <select name="status" id="status">
                    <option value="Pending" <?php echo $application['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Accepted" <?php echo $application['status'] === 'Accepted' ? 'selected' : ''; ?>>Accepted</option>
                    <option value="Rejected" <?php echo $application['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <button type="submit" class="update-btn">Update Status</button>
            </form>
        </section>
    </div>
</body>
</html>