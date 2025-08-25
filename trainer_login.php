<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trainer_id = $_POST['trainer_id'];
    $password = $_POST['password'];

    if (empty($trainer_id) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Fetch trainer by trainer_id
        $sql = "SELECT * FROM Trainers WHERE trainer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $trainer_id); // Use "i" for integer
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $trainer = $result->fetch_assoc();
            if (password_verify($password, $trainer['tpassword'])) {
                // Set session variables
                $_SESSION['trainer_id'] = $trainer['trainer_id'];
                $_SESSION['trainer_name'] = $trainer['trainer_name'];
                header("Location: trainer_dashboard.php");
                exit();
            } else {
                $error = "Invalid trainer ID or password.";
            }
        } else {
            $error = "Invalid trainer ID or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Login</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="login-container">
        <h2>Trainer Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="trainer_login.php" method="POST">
            <input type="number" name="trainer_id" placeholder="Enter your Trainer ID" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="trainer_register.php">Register here</a>.</p>
    </div>
    <script src="js/auth.js"></script>
</body>
</html>