<?php
session_start();

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

include 'includes/db_connection.php';

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

// Handle form submission for updating profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $company_name = $_POST['company_name'];
    $industry_type = $_POST['industry_type'];
    $contact_person = $_POST['contact_person'];
    $contact_email = $_POST['contact_email'];
    $contact_number = $_POST['contact_number'];

    // Validate form data
    if (empty($company_name) || empty($industry_type) || empty($contact_person) || empty($contact_email) || empty($contact_number)) {
        $error = "All fields are required.";
    } else {
        // Update company profile in the database
        $sql = "UPDATE Companies SET company_name = ?, industry_type = ?, contact_person = ?, contact_email = ?, contact_number = ? WHERE company_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("sssssi", $company_name, $industry_type, $contact_person, $contact_email, $contact_number, $company_id);
        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            $success = "Profile updated successfully!";
            // Refresh the page to reflect the changes
            header("Location: update_company_profile.php");
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
    <title>Update Company Profile</title>
    <link rel="stylesheet" href="css/company_dashboard.css"> <!-- Use the same CSS as the dashboard -->
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header>
            <h1>Update Profile</h1>
            <a href="company_dashboard.php" class="logout-btn">Back to Dashboard</a>
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
                <label for="company_name">Company Name:</label>
                <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company['company_name']); ?>" required>

                <label for="industry_type">Industry Type:</label>
                <input type="text" id="industry_type" name="industry_type" value="<?php echo htmlspecialchars($company['industry_type']); ?>" required>

                <label for="contact_person">Contact Person:</label>
                <input type="text" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($company['contact_person']); ?>" required>

                <label for="contact_email">Contact Email:</label>
                <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($company['contact_email']); ?>" required>

                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($company['contact_number']); ?>" required>

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </section>
    </div>
</body>
</html>