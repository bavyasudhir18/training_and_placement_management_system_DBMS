<?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$student_id = $_GET['student_id'];
$job_id = $_GET['job_id'];

// Handle placement
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $placement_date = $_POST['placement_date'];
    $package_offered = $_POST['package_offered'];
    $joining_date = $_POST['joining_date'];

    // Insert into Placements table
    $sql = "INSERT INTO Placements (student_id, job_id, placement_date, package_offered, joining_date) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iisss", $student_id, $job_id, $placement_date, $package_offered, $joining_date);
    if ($stmt->execute()) {
        // Delete the corresponding application and interview
        $delete_sql = "DELETE FROM Applications WHERE student_id = ? AND job_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        if (!$delete_stmt) {
            die("Error preparing delete statement: " . $conn->error);
        }
        $delete_stmt->bind_param("ii", $student_id, $job_id);
        if ($delete_stmt->execute()) {
            // Delete the corresponding interview (if any)
            $delete_interview_sql = "DELETE FROM Interviews WHERE application_id IN (SELECT application_id FROM Applications WHERE student_id = ? AND job_id = ?)";
            $delete_interview_stmt = $conn->prepare($delete_interview_sql);
            if (!$delete_interview_stmt) {
                die("Error preparing delete statement: " . $conn->error);
            }
            $delete_interview_stmt->bind_param("ii", $student_id, $job_id);
            if ($delete_interview_stmt->execute()) {
                $success = "Placement added successfully, and application/interview removed!";
            } else {
                $error = "Failed to remove interview.";
            }
        } else {
            $error = "Failed to remove application.";
        }
    } else {
        $error = "Failed to add placement.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Placement</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Add Placement</h1>
            <a href="company_dashboard.php" class="back-btn">Back to Dashboard</a>
        </header>

        <section class="add-placement-section">
            <h2>Add Placement for Student ID: <?php echo $student_id; ?></h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <label for="placement_date">Placement Date:</label>
                <input type="date" id="placement_date" name="placement_date" required>

                <label for="package_offered">Package Offered:</label>
                <input type="number" id="package_offered" name="package_offered" required>

                <label for="joining_date">Joining Date:</label>
                <input type="date" id="joining_date" name="joining_date" required>

                <button type="submit">Add Placement</button>
            </form>
        </section>
    </div>
</body>
</html><?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$student_id = $_GET['student_id'];
$job_id = $_GET['job_id'];

// Handle placement
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $placement_date = $_POST['placement_date'];
    $package_offered = $_POST['package_offered'];
    $joining_date = $_POST['joining_date'];

    // Insert into Placements table
    $sql = "INSERT INTO Placements (student_id, job_id, placement_date, package_offered, joining_date) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iisss", $student_id, $job_id, $placement_date, $package_offered, $joining_date);
    if ($stmt->execute()) {
        // Delete the corresponding application and interview
        $delete_sql = "DELETE FROM Applications WHERE student_id = ? AND job_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        if (!$delete_stmt) {
            die("Error preparing delete statement: " . $conn->error);
        }
        $delete_stmt->bind_param("ii", $student_id, $job_id);
        if ($delete_stmt->execute()) {
            // Delete the corresponding interview (if any)
            $delete_interview_sql = "DELETE FROM Interviews WHERE application_id IN (SELECT application_id FROM Applications WHERE student_id = ? AND job_id = ?)";
            $delete_interview_stmt = $conn->prepare($delete_interview_sql);
            if (!$delete_interview_stmt) {
                die("Error preparing delete statement: " . $conn->error);
            }
            $delete_interview_stmt->bind_param("ii", $student_id, $job_id);
            if ($delete_interview_stmt->execute()) {
                $success = "Placement added successfully, and application/interview removed!";
            } else {
                $error = "Failed to remove interview.";
            }
        } else {
            $error = "Failed to remove application.";
        }
    } else {
        $error = "Failed to add placement.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Placement</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Add Placement</h1>
            <a href="company_dashboard.php" class="back-btn">Back to Dashboard</a>
        </header>

        <section class="add-placement-section">
            <h2>Add Placement for Student ID: <?php echo $student_id; ?></h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <label for="placement_date">Placement Date:</label>
                <input type="date" id="placement_date" name="placement_date" required>

                <label for="package_offered">Package Offered:</label>
                <input type="number" id="package_offered" name="package_offered" required>

                <label for="joining_date">Joining Date:</label>
                <input type="date" id="joining_date" name="joining_date" required>

                <button type="submit">Add Placement</button>
            </form>
        </section>
    </div>
</body>
</html>