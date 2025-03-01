<?php
// Include database connection
include('../db_connect.php');

// Fetch student data based on the passed ID
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $sql = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        echo "No student found.";
    }
} else {
    echo "Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            padding-top: 50px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            padding: 15px;
        }
        .card-body {
            padding: 30px;
        }
        .student-details p {
            font-size: 16px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            Student Details
        </div>
        <div class="card-body">
            <?php if (isset($student)): ?>
                <p><strong>Full Name:</strong> <?php echo $student['first_name'] . ' ' . $student['last_name']; ?></p>
                <p><strong>Student ID:</strong> <?php echo $student['student_id']; ?></p>
                <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
