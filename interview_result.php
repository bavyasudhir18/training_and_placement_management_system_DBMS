<?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Check if the interview_id is provided in the URL
if (!isset($_GET['interview_id']) || !is_numeric($_GET['interview_id'])) {
    die("Invalid interview ID.");
}

$interview_id = $_GET['interview_id'];

// Fetch interview details to ensure it belongs to the logged-in company
$sql = "SELECT Interviews.*, Students.first_name, Students.last_name, Students.email, Students.cgpa, Jobs.job_title 
        FROM Interviews 
        JOIN Applications ON Interviews.application_id = Applications.application_id 
        JOIN Students ON Applications.student_id = Students.student_id 
        JOIN Jobs ON Applications.job_id = Jobs.job_id 
        WHERE Interviews.interview_id = ? AND Jobs.company_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("ii", $interview_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();
$interview = $result->fetch_assoc();

if (!$interview) {
    die("Interview not found or you do not have permission to update it.");
}

// Handle interview results update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $interview_status = $_POST['interview_status'];
    $feedback = $_POST['feedback'];

    // Update interview result and feedback
    $sql = "UPDATE Interviews SET interview_status = ?, interview_feedback = ? WHERE interview_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ssi", $interview_status, $feedback, $interview_id);
    if ($stmt->execute()) {
        $success = "Interview result updated successfully!";
    } else {
        $error = "Failed to update interview result.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Interview Result</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Update Interview Result</h1>
            <a href="company_dashboard.php" class="back-btn">Back to Dashboard</a>
        </header>

        <section class="interview-results-section">
            <h2>Update Interview Result for Interview ID: <?php echo $interview_id; ?></h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <label for="interview_status">Interview Status:</label>
                <select id="interview_status" name="interview_status" required>
                    <option value="Scheduled" <?php echo $interview['interview_status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="Completed" <?php echo $interview['interview_status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo $interview['interview_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>

                <label for="feedback">Feedback:</label>
                <textarea id="feedback" name="feedback" required><?php echo htmlspecialchars($interview['interview_feedback'] ?? ''); ?></textarea>

                <button type="submit">Update Result</button>
            </form>
        </section>
    </div>
</body>
</html>