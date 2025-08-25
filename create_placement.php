<?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Check if the application_id is provided in the URL
if (!isset($_GET['application_id']) || !is_numeric($_GET['application_id'])) {
    die("Invalid application ID. Please check the URL.");
}

$application_id = (int)$_GET['application_id']; // Cast to integer for safety
echo "Application ID: " . $application_id; // Debugging line

// Fetch application and interview details
$sql = "SELECT Applications.*, Students.student_id, Jobs.job_id 
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
    die("Application not found or you do not have permission to create a placement.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $placement_date = $_POST['placement_date'];
    $package_offered = $_POST['package_offered'];
    $joining_date = $_POST['joining_date'];

    // Insert placement details into the Placements table
    $sql = "INSERT INTO Placements (student_id, job_id, placement_date, package_offered, joining_date) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iisss", $application['student_id'], $application['job_id'], $placement_date, $package_offered, $joining_date);
    if ($stmt->execute()) {
        $success = "Placement created successfully!";
    } else {
        $error = "Failed to create placement.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Placement</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Create Placement</h1>
            <a href="company_dashboard.php" class="back-btn">Back to Dashboard</a>
        </header>

        <section class="placement-section">
            <h2>Create Placement for <?php echo htmlspecialchars($application['first_name'] . ' ' . htmlspecialchars($application['last_name'])); ?></h2>
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
                <input type="text" id="package_offered" name="package_offered" required>

                <label for="joining_date">Joining Date:</label>
                <input type="date" id="joining_date" name="joining_date" required>

                <button type="submit">Create Placement</button>
            </form>
        </section>
    </div>
</body>
</html>