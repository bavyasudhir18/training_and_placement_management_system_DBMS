<?php
// Include the database connection file
include 'includes/db_connection.php';

// Fetch all students with plain text passwords
$sql = "SELECT student_id, spassword FROM Students";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop through each student
    while ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];
        $plain_password = $row['spassword']; // Plain text password

        // Hash the plain text password
        $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

        // Update the spassword column with the hashed password
        $update_sql = "UPDATE Students SET spassword = ? WHERE student_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $student_id);

        if ($stmt->execute()) {
            echo "Updated password for student ID: $student_id\n";
        } else {
            echo "Failed to update password for student ID: $student_id\n";
        }

        $stmt->close();
    }
} else {
    echo "No students found in the database.\n";
}

// Close the database connection
$conn->close();
?>