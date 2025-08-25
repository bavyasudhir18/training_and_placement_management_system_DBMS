<?php
// Include the database connection file
include 'includes/db_connection.php';

// Fetch all trainers with plain text passwords
$sql = "SELECT trainer_id, tpassword FROM Trainers";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop through each trainer
    while ($row = $result->fetch_assoc()) {
        $trainer_id = $row['trainer_id'];
        $plain_password = $row['tpassword']; // Plain text password

        // Hash the plain text password
        $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

        // Update the tpassword column with the hashed password
        $update_sql = "UPDATE Trainers SET tpassword = ? WHERE trainer_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $trainer_id);

        if ($stmt->execute()) {
            echo "Updated password for trainer ID: $trainer_id\n";
        } else {
            echo "Failed to update password for trainer ID: $trainer_id\n";
        }

        $stmt->close();
    }
} else {
    echo "No trainers found in the database.\n";
}

// Close the database connection
$conn->close();
?>