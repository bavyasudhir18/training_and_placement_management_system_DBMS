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
$stmt->bind_param("i", $company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();

if (!$company) {
    die("Company not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = $_POST['company_name'];
    $industry_type = $_POST['industry_type'];
    $contact_person = $_POST['contact_person'];
    $contact_email = $_POST['contact_email'];
    $contact_number = $_POST['contact_number'];

    $sql = "UPDATE Companies 
            SET company_name = ?, industry_type = ?, contact_person = ?, contact_email = ?, contact_number = ? 
            WHERE company_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $company_name, $industry_type, $contact_person, $contact_email, $contact_number, $company_id);
    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
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
    <title>Edit Company Profile</title>
    <link rel="stylesheet" href="css/company_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Edit Company Profile</h1>
            <a href="company_dashboard.php" class="back-btn">Back to Dashboard</a>
        </header>

        <section class="edit-profile-section">
            <h2>Edit Profile</h2>
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

                <label for="contact_number">Contact Phone:</label>
                <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($company['contact_number']); ?>" required>

                <button type="submit">Update Profile</button>
            </form>
        </section>
    </div>
</body>
</html>