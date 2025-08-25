<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    if (empty($student_id) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Fetch student by student_id
        $sql = "SELECT * FROM Students WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            // Debugging: Print fetched student data
            echo "<pre>";
            print_r($student);
            echo "</pre>";

            // Debugging: Print the entered password and stored hash
            echo "Entered Password: $password<br>";
            echo "Stored Hash: " . $student['spassword'] . "<br>";

            // Verify password
            if (password_verify($password, $student['spassword'])) {
                echo "Password matches!"; // Debug: Check if password verification succeeds

                // Set session variables
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['student_name'] = $student['first_name'];

                // Redirect to dashboard
                header("Location: student_dashboard.php");
                exit();
            } else {
                $error = "Invalid student ID or password.";
                echo "Password does not match!"; // Debug: Check if password verification fails
            }
        } else {
            $error = "Invalid student ID or password.";
            echo "No student found with this ID!"; // Debug: Check if student ID exists
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="login-container">
        <h2>Student Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="student_login.php" method="POST">
            <input type="number" name="student_id" placeholder="Enter your Student ID" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="student_register.php">Register here</a>.</p>
    </div>
    <script src="js/auth.js"></script>
</body>
</html>