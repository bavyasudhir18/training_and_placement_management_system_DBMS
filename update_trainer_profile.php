<?php
session_start();

// Check if the trainer is logged in
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit();
}

include 'includes/db_connection.php';

$trainer_id = $_SESSION['trainer_id'];

// Fetch trainer details
$sql = "SELECT * FROM Trainers WHERE trainer_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer = $result->fetch_assoc();

if (!$trainer) {
    die("Trainer not found.");
}

// Handle form submission for updating profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $trainer_name = $_POST['trainer_name'];
    $trainer_contact = $_POST['trainer_contact'];

    // Validate form data
    if (empty($trainer_name) || empty($trainer_contact)) {
        $error = "All fields are required.";
    } else {
        // Update trainer profile in the database
        $sql = "UPDATE Trainers SET trainer_name = ?, trainer_contact = ? WHERE trainer_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ssi", $trainer_name, $trainer_contact, $trainer_id);
        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            $success = "Profile updated successfully!";
            // Refresh the page to reflect the changes
            header("Location: update_trainer_profile.php");
            exit();
        } else {
            $error = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Trainer Profile</title>
    <link rel="stylesheet" href="css/trainer_dashboard.css"> <!-- Use the same CSS as the dashboard -->
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header>
            <h1>Update Profile</h1>
            <a href="trainer_dashboard.php" class="logout-btn">Back to Dashboard</a>
        </header>

        <!-- Profile Update Section -->
        <section class="profile-section">
            <h2>Update Your Profile</h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <label for="trainer_name">Name:</label>
                <input type="text" id="trainer_name" name="trainer_name" value="<?php echo htmlspecialchars($trainer['trainer_name']); ?>" required>

                <label for="trainer_contact">Contact:</label>
                <input type="text" id="trainer_contact" name="trainer_contact" value="<?php echo htmlspecialchars($trainer['trainer_contact']); ?>" required>

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </section>
    </div>
</body>
</html>