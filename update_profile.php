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

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $cgpa = $_POST['cgpa'];
    $current_arrears = $_POST['current_arrears'];

    // Update student profile in the database
    $sql = "UPDATE Students SET first_name = ?, last_name = ?, email = ?, cgpa = ?, current_arrears = ? WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdii", $first_name, $last_name, $email, $cgpa, $current_arrears, $student_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Profile updated successfully!";
        // Redirect back to the dashboard
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header>
            <h1>Update Profile</h1>
            <a href="student_dashboard.php" class="back-btn">Back to Dashboard</a>
        </header>

        <!-- Update Profile Form -->
        <section class="update-profile-section">
            <h2>Update Your Profile</h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="update_profile.php">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>

                <label for="cgpa">CGPA:</label>
                <input type="number" step="0.1" id="cgpa" name="cgpa" value="<?php echo htmlspecialchars($student['cgpa']); ?>" required>

                <label for="current_arrears">Current Arrears:</label>
                <input type="number" id="current_arrears" name="current_arrears" value="<?php echo htmlspecialchars($student['current_arrears']); ?>" required>

                <button type="submit" name="update_profile" class="update-btn">Update Profile</button>
            </form>
        </section>
    </div>
</body>
</html>