<?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$application_id = $_GET['application_id'];

// Handle interview scheduling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $interview_date = $_POST['interview_date'];
    $interview_type = $_POST['interview_type'];

    // Insert into Interviews table
    $sql = "INSERT INTO Interviews (application_id, interview_date, interview_type) 
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iss", $application_id, $interview_date, $interview_type);
    if ($stmt->execute()) {
        // Update application status to 'Interview Scheduled'
        $update_sql = "UPDATE Applications SET status = 'Interview Scheduled' WHERE application_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            die("Error preparing update statement: " . $conn->error);
        }
        $update_stmt->bind_param("i", $application_id);
        if ($update_stmt->execute()) {
            $success = "Interview scheduled successfully and application status updated!";
        } else {
            $error = "Failed to update application status.";
        }
    } else {
        $error = "Failed to schedule interview.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Interview</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Schedule Interview</h1>
            <a href="company_dashboard.php" class="back-btn">Back to Dashboard</a>
        </header>

        <section class="schedule-interview-section">
            <h2>Schedule Interview for Application ID: <?php echo $application_id; ?></h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <label for="interview_date">Interview Date:</label>
                <input type="date" id="interview_date" name="interview_date" required>

                <label for="interview_type">Interview Type:</label>
                <select id="interview_type" name="interview_type" required>
                    <option value="Online">Online</option>
                    <option value="Offline">Offline</option>
                </select>

                <button type="submit">Schedule Interview</button>
            </form>
        </section>
    </div>
</body>
</html>