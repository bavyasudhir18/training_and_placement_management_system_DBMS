<?php
// Include the database connection file
include 'includes/db_connection.php';

// Fetch all companies with plain text passwords
$sql = "SELECT company_id, rpassword FROM Companies";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop through each company
    while ($row = $result->fetch_assoc()) {
        $company_id = $row['company_id'];
        $plain_password = $row['rpassword']; // Plain text password

        // Hash the plain text password
        $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

        // Update the rpassword column with the hashed password
        $update_sql = "UPDATE Companies SET rpassword = ? WHERE company_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $company_id);

        if ($stmt->execute()) {
            echo "Updated password for company ID: $company_id\n";
        } else {
            echo "Failed to update password for company ID: $company_id\n";
        }

        $stmt->close();
    }
} else {
    echo "No companies found in the database.\n";
}

// Close the database connection
$conn->close();
?>