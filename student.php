<?php
include('../db_connect.php');

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Delete related quiz_history first to avoid foreign key constraints
    $delete_history_sql = "DELETE FROM quiz_history WHERE student_id = ?";
    $delete_history_stmt = $conn->prepare($delete_history_sql);
    $delete_history_stmt->bind_param("i", $delete_id);
    $delete_history_stmt->execute();
    $delete_history_stmt->close();

    // Delete student
    $delete_sql = "DELETE FROM students WHERE student_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        header("Location: student.php?message=Student deleted successfully");
    } else {
        header("Location: student.php?error=Error deleting student: " . $conn->error);
    }
    $delete_stmt->close();
    exit();
}

// Fetch all student data
$sql = "SELECT * FROM students ORDER BY student_id";
$result = $conn->query($sql);

// Fetch quiz performance per student
$performance_sql = "
    SELECT 
        qh.student_id,
        s.subject_name,
        COUNT(DISTINCT qh.quiz_id) AS subjects_attempted,
        SUM(CASE WHEN qh.is_correct = 1 THEN 1 ELSE 0 END) AS correct_answers,
        COUNT(qh.question_id) AS total_questions,
        CASE 
            WHEN SUM(CASE WHEN qh.is_correct = 1 THEN 1 ELSE 0 END) >= COUNT(qh.question_id) * 0.5 THEN 'Pass'
            ELSE 'Fail'
        END AS pass_fail
    FROM quiz_history qh
    INNER JOIN quizzes q ON qh.quiz_id = q.quiz_id
    INNER JOIN subjects s ON q.subject_id = s.subject_id
    GROUP BY qh.student_id, s.subject_name";
$performance_result = $conn->query($performance_sql);

$performance_data = [];
while ($row = $performance_result->fetch_assoc()) {
    $performance_data[$row['student_id']][$row['subject_name']] = [
        'correct' => $row['correct_answers'],
        'total' => $row['total_questions'],
        'pass_fail' => $row['pass_fail']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Activity Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            padding-top: 50px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
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
            padding: 20px;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-table th, .student-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .student-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .student-table tr:hover {
            background-color: #e9ecef;
        }
        .btn-action {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn-view {
            background-color: #28a745;
        }
        .btn-view:hover {
            background-color: #218838;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .home-btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .home-btn:hover {
            background-color: #0056b3;
        }
        .no-data-msg {
            text-align: center;
            font-size: 18px;
            color: #666;
            padding: 20px;
        }
        .performance-details {
            font-size: 12px;
            color: #555;
        }
        .pass {
            color: #28a745;
            font-weight: bold;
        }
        .fail {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            Student Activity Dashboard
        </div>
        <div class="card-body">
            <!-- Home Page Button -->
            <a href="admin_dashboard.php" class="home-btn">Home Page</a>
            
            <!-- Success/Error Messages -->
            <?php
            if (isset($_GET['message'])) {
                echo "<div class='alert alert-success'>" . htmlspecialchars($_GET['message']) . "</div>";
            } elseif (isset($_GET['error'])) {
                echo "<div class='alert alert-danger'>" . htmlspecialchars($_GET['error']) . "</div>";
            }
            ?>

            <?php if ($result->num_rows > 0): ?>
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Performance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td class="performance-details">
                                    <?php
                                    $student_id = $row['student_id'];
                                    if (isset($performance_data[$student_id])) {
                                        foreach ($performance_data[$student_id] as $subject => $data) {
                                            $status_class = $data['pass_fail'] === 'Pass' ? 'pass' : 'fail';
                                            echo "$subject: {$data['correct']}/{$data['total']} (<span class='$status_class'>{$data['pass_fail']}</span>)<br>";
                                        }
                                    } else {
                                        echo "No quiz attempts yet.";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="student_details.php?id=<?php echo $row['student_id']; ?>" class="btn-action btn-view">View Details</a>
                                    <a href="student.php?delete_id=<?php echo $row['student_id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this student? This will also delete their quiz history.');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data-msg">
                    No student data available.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>