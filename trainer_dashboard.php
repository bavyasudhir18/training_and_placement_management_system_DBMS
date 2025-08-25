<?php
session_start();

// Check if the trainer is logged in
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit();
}

include 'includes/db_connection.php'; // Include your database connection file

$trainer_id = $_SESSION['trainer_id'];

// Fetch trainer details
$sql = "SELECT * FROM Trainers WHERE trainer_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer = $result->fetch_assoc();

if (!$trainer) {
    die("Trainer not found.");
}

// Handle form submission for creating a new training program
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_training'])) {
    $training_name = $_POST['training_name'];
    $department_id = $_POST['department_id'];
    $training_start_date = $_POST['training_start_date'];
    $training_end_date = $_POST['training_end_date'];
    $training_venue = $_POST['training_venue'];

    // Validate form data
    if (empty($training_name) || empty($department_id) || empty($training_start_date) || empty($training_end_date) || empty($training_venue)) {
        $error = "All fields are required.";
    } else {
        // Insert into database
        $sql = "INSERT INTO TrainingDetails (training_name, department_id, trainer_id, training_start_date, training_end_date, training_venue) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("sissis", $training_name, $department_id, $trainer_id, $training_start_date, $training_end_date, $training_venue);
        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            $success = "Training program created successfully!";
        } else {
            $error = "Failed to create training program.";
        }
    }
}

// Handle form submission for deleting a training program
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_training'])) {
    $training_id = $_POST['training_id'];

    // Delete the training program from the database
    $sql = "DELETE FROM TrainingDetails WHERE training_id = ? AND trainer_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ii", $training_id, $trainer_id);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        $success = "Training program deleted successfully!";
        // Refresh the page to reflect the changes
        header("Location: trainer_dashboard.php");
        exit();
    } else {
        $error = "Failed to delete training program.";
    }
}

// Fetch all training sessions conducted by the trainer
$sql = "SELECT * FROM TrainingDetails WHERE trainer_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainings = $result->fetch_all(MYSQLI_ASSOC);

// Fetch students enrolled in the trainer's sessions
$sql = "SELECT Students.*, TrainingDetails.training_name 
        FROM Students 
        JOIN TrainingDetails ON Students.department_id = TrainingDetails.department_id 
        WHERE TrainingDetails.trainer_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

// Fetch number of students in each training program
$sql = "SELECT TrainingDetails.training_id, TrainingDetails.training_name, TrainingDetails.training_start_date, TrainingDetails.training_end_date, COUNT(Students.student_id) as student_count 
        FROM TrainingDetails 
        LEFT JOIN Students ON TrainingDetails.department_id = Students.department_id 
        WHERE TrainingDetails.trainer_id = ? 
        GROUP BY TrainingDetails.training_id";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$training_student_counts = $result->fetch_all(MYSQLI_ASSOC);

// Fetch upcoming training sessions
$current_date = date("Y-m-d");
$sql = "SELECT * FROM TrainingDetails WHERE trainer_id = ? AND training_start_date > ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("is", $trainer_id, $current_date);
$stmt->execute();
$result = $stmt->get_result();
$upcoming_trainings = $result->fetch_all(MYSQLI_ASSOC);

// Fetch departments for the dropdown
$sql = "SELECT * FROM Departments";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching departments: " . $conn->error);
}
$departments = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="css/trainer_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($trainer['trainer_name']); ?>!</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
            <a href="#create-training" class="create-training-btn">Create Training Program</a>
        </header>

        <!-- Profile Section -->
        <section class="profile-section">
            <h2>Profile</h2>
            <div class="profile-details">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($trainer['trainer_name']); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($trainer['trainer_contact']); ?></p>
                <a href="update_trainer_profile.php" class="edit-profile-btn">Edit Profile</a>
            </div>
        </section>

        <!-- Training Sessions Section -->
        <section class="trainings-section">
            <h2>Your Training Sessions</h2>
            <div class="training-list">
                <?php
                if (count($training_student_counts) > 0) {
                    foreach ($training_student_counts as $training) {
                        echo "<div class='training-card'>";
                        echo "<h3>" . htmlspecialchars($training['training_name']) . "</h3>";
                        echo "<p><strong>Date:</strong> " . htmlspecialchars($training['training_start_date']) . " to " . htmlspecialchars($training['training_end_date']) . "</p>";
                        echo "<p><strong>Number of Students:</strong> " . htmlspecialchars($training['student_count']) . "</p>";
                        echo "<form method='POST' action='' style='display:inline;'>";
                        echo "<input type='hidden' name='training_id' value='" . htmlspecialchars($training['training_id']) . "'>";
                        echo "<button type='submit' name='delete_training' class='delete-btn'>Delete</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>You have no training sessions.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Upcoming Training Sessions Section -->
        <section class="trainings-section">
            <h2>Upcoming Training Sessions</h2>
            <div class="training-list">
                <?php
                if (count($upcoming_trainings) > 0) {
                    foreach ($upcoming_trainings as $training) {
                        echo "<div class='training-card'>";
                        echo "<h3>" . htmlspecialchars($training['training_name']) . "</h3>";
                        echo "<p><strong>Date:</strong> " . htmlspecialchars($training['training_start_date']) . " to " . htmlspecialchars($training['training_end_date']) . "</p>";
                        echo "<p><strong>Venue:</strong> " . htmlspecialchars($training['training_venue']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No upcoming training sessions.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Students Section -->
        <section class="students-section">
            <h2>Students in Your Sessions</h2>
            <div class="student-list">
                <?php
                if (count($students) > 0) {
                    foreach ($students as $student) {
                        echo "<div class='student-card'>";
                        echo "<h3>" . htmlspecialchars($student['first_name'] . ' ' . htmlspecialchars($student['last_name'])) . "</h3>";
                        echo "<p><strong>Email:</strong> " . htmlspecialchars($student['email']) . "</p>";
                        echo "<p><strong>Training:</strong> " . htmlspecialchars($student['training_name']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No students enrolled in your sessions.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Create Training Program Section -->
        <section id="create-training" class="create-training-section">
            <h2>Create New Training Program</h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <label for="training_name">Training Name:</label>
                <input type="text" id="training_name" name="training_name" required>

                <label for="department_id">Department:</label>
                <select id="department_id" name="department_id" required>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo $department['department_id']; ?>">
                            <?php echo htmlspecialchars($department['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="training_start_date">Start Date:</label>
                <input type="date" id="training_start_date" name="training_start_date" required>

                <label for="training_end_date">End Date:</label>
                <input type="date" id="training_end_date" name="training_end_date" required>

                <label for="training_venue">Venue:</label>
                <input type="text" id="training_venue" name="training_venue" required>

                <button type="submit" name="create_training">Create Training Program</button>
            </form>
        </section>
    </div>
</body>
</html>