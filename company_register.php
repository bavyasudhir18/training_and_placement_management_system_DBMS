<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = $_POST['company_name'];
    $industry_type = $_POST['industry_type'];
    $contact_person = $_POST['contact_person'];
    $contact_email = $_POST['contact_email'];
    $contact_number = $_POST['contact_number'];
    $location = $_POST['location'];
    $website = $_POST['website'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert into database
        $sql = "INSERT INTO Companies (company_name, industry_type, contact_person, contact_email, contact_number, location, website, rpassword) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $company_name, $industry_type, $contact_person, $contact_email, $contact_number, $location, $website, $hashed_password);

        if ($stmt->execute()) {
            $success = "Company registered successfully!";
        } else {
            $error = "Error registering company: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Registration</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="register-container">
        <h2>Company Registration</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="company_register.php" method="POST">
            <input type="text" name="company_name" placeholder="Company Name" required>
            <input type="text" name="industry_type" placeholder="Industry Type" required>
            <input type="text" name="contact_person" placeholder="Contact Person" required>
            <input type="email" name="contact_email" placeholder="Contact Email" required>
            <input type="text" name="contact_number" placeholder="Contact Number" required>
            <input type="text" name="location" placeholder="Location" required>
            <input type="url" name="website" placeholder="Website" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="company_login.php">Login here</a>.</p>
    </div>
    <script src="js/auth.js"></script>
</body>
</html>
