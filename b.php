<?php
// Database connection
$host = 'localhost';
$dbname = 'tapms4';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password
$port = 3307; // Replace with your database port if different from the default (3306)

try {
    // Create a PDO instance
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname"; // Include the port in the DSN
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Set default fetch mode to associative array
    ];
    $conn = new PDO($dsn, $username, $password, $options);
    echo "Connected to the database successfully.<br>";

    // Step 1: Check if the `department_id` column already exists
    $checkColumnSql = "SHOW COLUMNS FROM Jobs LIKE 'department_id'";
    $stmt = $conn->query($checkColumnSql);
    $columnExists = $stmt->fetch();

    if (!$columnExists) {
        // Step 2: Add the `department_id` column to the `Jobs` table
        $sql = "ALTER TABLE Jobs ADD COLUMN department_id INT";
        $conn->exec($sql);
        echo "Column `department_id` added to the `Jobs` table.<br>";
    } else {
        echo "Column `department_id` already exists in the `Jobs` table.<br>";
    }

    // Step 3: Define the department IDs for each job
    $jobDepartmentMapping = [
        400 => 2,  // System Administrator -> Computer Science
        401 => 2,  // Software Developer -> Computer Science
        402 => 2,  // DevOps Engineer -> Computer Science
        403 => 8,  // Blockchain Developer -> Artificial Intelligence
        404 => 4,  // Electrical Engineer -> Electrical and Electronics Engineering
        405 => 5,  // VLSI Design Engineer -> Electronics and Communication Engineering
        406 => 5,  // Hardware Engineer -> Electronics and Communication Engineering
        407 => 4,  // Network Engineer -> Electrical and Electronics Engineering
        408 => 3,  // Automotive Engineer -> Mechanical Engineering
        409 => 7,  // Petroleum Engineer -> Chemical Engineering
        410 => 6,  // Avionics Specialist -> Aerospace Engineering
        411 => 8,  // Cybersecurity Specialist -> Artificial Intelligence
        412 => 3,  // Quality Control Engineer -> Mechanical Engineering
        413 => 3,  // CNC Programmer -> Mechanical Engineering
        414 => 1,  // Civil Engineer -> Civil Engineering
        415 => 2,  // Data Scientist -> Computer Science
        416 => 8,  // AI Engineer -> Artificial Intelligence
        417 => 2,  // Cloud Engineer -> Computer Science
        418 => 2,  // UI/UX Designer -> Computer Science
        419 => 2,  // Product Manager -> Computer Science
        420 => 2,  // Marketing Specialist -> Computer Science (Consider changing to a Business department if available)
        421 => 2,  // Financial Analyst -> Computer Science (Consider changing to a Business department if available)
        422 => 2,  // HR Manager -> Computer Science (Consider changing to a Business department if available)
        423 => 2,  // Sales Executive -> Computer Science (Consider changing to a Business department if available)
        424 => 2,  // Business Analyst -> Computer Science
        425 => 2,  // Operations Manager -> Computer Science (Consider changing to a Business department if available)
    ];

    // Step 4: Update the `department_id` for each job
    foreach ($jobDepartmentMapping as $job_id => $department_id) {
        $sql = "UPDATE Jobs SET department_id = :department_id WHERE job_id = :job_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt->execute();
        echo "Updated `department_id` for job_id $job_id to $department_id.<br>";
    }

    echo "`department_id` updated for all jobs.<br>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>