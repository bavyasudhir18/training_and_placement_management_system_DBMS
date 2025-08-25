<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trainer_name = $_POST['trainer_name'];
    $trainer_contact = $_POST['trainer_contact'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert into database
        $sql = "INSERT INTO Trainers (trainer_name, trainer_contact, tpassword) 
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $trainer_name, $trainer_contact, $hashed_password);

        if ($stmt->execute()) {
            $success = "Trainer registered successfully!";
        } else {
            $error = "Error registering trainer: " . $stmt->error;
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
    <title>Trainer Registration</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="register-container">
        <h2>Trainer Registration</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="trainer_register.php" method="POST">
            <input type="text" name="trainer_name" placeholder="Trainer Name" required>
            <input type="text" name="trainer_contact" placeholder="Trainer Contact" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="trainer_login.php">Login here</a>.</p>
    </div>
    <script src="js/auth.js"></script>
</body>
</html>