<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $department_id = $_POST['department_id'];
    $year_of_study = $_POST['year_of_study'];
    $cgpa = $_POST['cgpa'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert into database
        $sql = "INSERT INTO Students (first_name, last_name, date_of_birth, gender, email, contact_number, department_id, year_of_study, cgpa, spassword) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssiids", $first_name, $last_name, $date_of_birth, $gender, $email, $contact_number, $department_id, $year_of_study, $cgpa, $hashed_password);

        if ($stmt->execute()) {
            $success = "Student registered successfully!";
        } else {
            $error = "Error registering student: " . $stmt->error;
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
    <title>Student Registration</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="register-container">
        <h2>Student Registration</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="student_register.php" method="POST">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="date" name="date_of_birth" placeholder="Date of Birth" required>
            <select name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="contact_number" placeholder="Contact Number" required>
            <input type="number" name="department_id" placeholder="Department ID" required>
            <input type="number" name="year_of_study" placeholder="Year of Study" required>
            <input type="number" step="0.1" name="cgpa" placeholder="CGPA" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="student_login.php">Login here</a>.</p>
    </div>
    <script src="js/auth.js"></script>
</body>
</html>