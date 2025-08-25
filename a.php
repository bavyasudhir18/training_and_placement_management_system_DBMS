<?php
include 'includes/db_connection.php';

// New password to set for all students
$new_password = "new_password123"; // Replace with the desired password

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Fetch all students
$sql = "SELECT student_id FROM Students";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];

        // Update the spassword column with the new hash
        $update_sql = "UPDATE Students SET spassword = ? WHERE student_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $student_id);

        if ($stmt->execute()) {
            echo "✅ Password reset for student ID: $student_id\n";
        } else {
            echo "❌ Failed to reset password for student ID: $student_id\n";
        }

        $stmt->close();
    }
} else {
    echo "No students found in the database.\n";
}

$conn->close();
?>