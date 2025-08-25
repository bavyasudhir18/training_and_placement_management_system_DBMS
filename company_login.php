<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact_email = $_POST['contact_email']; // Use contact_email instead of email
    $password = $_POST['password'];

    if (empty($contact_email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Fetch company by contact_email
        $sql = "SELECT * FROM Companies WHERE contact_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $contact_email); // Bind contact_email
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $company = $result->fetch_assoc();
            if (password_verify($password, $company['rpassword'])) { // Use rpassword for hashed password
                // Set session variables
                $_SESSION['company_id'] = $company['company_id'];
                $_SESSION['company_name'] = $company['company_name'];
                header("Location: company_dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Login</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="login-container">
        <h2>Company Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="company_login.php" method="POST">
            <input type="email" name="contact_email" placeholder="Enter your contact email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="company_register.php">Register here</a>.</p>
    </div>
    <script src="js/auth.js"></script>
</body>
</html>