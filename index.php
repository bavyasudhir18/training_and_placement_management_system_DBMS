<?php
include 'includes/db_connection.php';
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Management System</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <!-- Login Portal -->
        <div class="login-portal">
            <button onclick="location.href='student_login.php'">Student Login</button>
            <button onclick="location.href='trainer_login.php'">Trainer Login</button>
            <button onclick="location.href='company_login.php'">Company Login</button>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div>
                <h3>Students</h3>
                <p><?php echo getTotalStudents(); ?></p>
            </div>
            <div>
                <h3>Trainers</h3>
                <p><?php echo getTotalTrainers(); ?></p>
            </div>
            <div>
                <h3>Companies</h3>
                <p><?php echo getTotalCompanies(); ?></p>
            </div>
        </div>

        <!-- Company List -->
        <div class="company-list">
            <h3>Top Companies</h3>
            <ul>
                <?php
                $companies = getCompanyList();
                foreach ($companies as $company) {
                    echo "<li><a href='{$company['website']}' target='_blank'>{$company['company_name']}</a></li>";
                }
                ?>
            </ul>
        </div>

        <!-- Placement Percentage -->
        <div class="placement-percentage">
            <h3>Placement Percentage: <?php echo calculatePlacementPercentage(); ?>%</h3>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>