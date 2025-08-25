<?php
session_start();
include 'includes/db_connection.php';

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['application_id']) || empty($_POST['status'])) {
        die("Invalid input. Application ID and status are required.");
    }

    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    $company_id = $_SESSION['company_id'];

    // Validate status value
    $allowed_statuses = ['Pending', 'Accepted', 'Rejected'];
    if (!in_array($status, $allowed_statuses)) {
        die("Invalid status value. Allowed values are: Pending, Accepted, Rejected.");
    }

    // Debugging: Print the application_id, status, and company_id
    echo "Application ID: " . htmlspecialchars($application_id) . "<br>";
    echo "Status: " . htmlspecialchars($status) . "<br>";
    echo "Company ID: " . htmlspecialchars($company_id) . "<br>";

    // Update the application status
    $sql = "UPDATE Applications 
            JOIN Jobs ON Applications.job_id = Jobs.job_id 
            SET Applications.status = ? 
            WHERE Applications.application_id = ? AND Jobs.company_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("sii", $status, $application_id, $company_id);
    $stmt->execute();

    // Check if the update was successful
    if ($stmt->affected_rows > 0) {
        echo "Application status updated successfully.";
        header("Location: view_application.php?id=" . $application_id);
        exit();
    } else {
        die("Failed to update application status. No rows were affected. Please check if the application ID is correct and belongs to your company.");
    }
} else {
    die("Invalid request method. Expected POST.");
}