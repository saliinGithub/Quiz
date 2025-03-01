<?php
session_start();
include '../db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../studentlogin/student-login.html");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch quiz history for the student
$query = "SELECT qh.history_id, qh.quiz_id, qh.question_id, qh.selected_answer, qh.correct_answer, qh.is_correct, qh.attempted_at, 
          q.quiz_name 
          FROM quiz_history qh 
          JOIN quizzes q ON qh.quiz_id = q.quiz_id 
          WHERE qh.student_id = ? 
          ORDER BY qh.attempted_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    // Fetch the question text for each quiz history entry
    $question_query = "SELECT question_text FROM questions WHERE question_id = ?";
    $question_stmt = $conn->prepare($question_query);
    $question_stmt->bind_param("i", $row['question_id']);
    $question_stmt->execute();
    $question_result = $question_stmt->get_result();
    $question_row = $question_result->fetch_assoc();
    
    $row['question_text'] = $question_row['question_text'] ?? 'Question not found';
    $history[] = $row;

    $question_stmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .sidebar {
            height: 100vh;
            background-color: #2c2c2c;
            color: #fff;
            padding-top: 20px;
            position: fixed;
            width: 250px;
            left: 0;
        }
        .sidebar h2 {
            padding: 10px;
            text-align: center;
            font-size: 1.5rem;
        }
        .sidebar a {
            color: #fff;
            padding: 15px;
            display: block;
            font-size: 1rem;
            text-decoration: none;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background-color: #575757;
            text-decoration: none;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .header {
            background-color: #fff;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        .header h4 {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
            font-size: 2rem;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .correct {
            color: green;
            font-weight: bold;
        }
        .incorrect {
            color: red;
            font-weight: bold;
        }
        .no-history {
            text-align: center;
            font-size: 1.2rem;
            color: #555;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Quizhub</h2>
        <a href="studentdashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="studentdashboard.php#subjects"><i class="fas fa-file-alt"></i> Exam</a>
        <a href="calender.html"><i class="fas fa-calendar-alt"></i> Calendar</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="student_quizzes.php"><i class="fas fa-chart-line"></i> Performance</a>
        <a href="history.php"><i class="fas fa-sign-out-alt"></i> History</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h4>Welcome, <?php echo htmlspecialchars($_SESSION['student_username']); ?></h4>
            <button class="btn btn-danger" onclick="window.location.href='logout.php'">Logout</button>
        </div>

        <div class="container">
            <h1>Your Quiz History</h1>
            <?php if (empty($history)): ?>
                <p class="no-history">You have not attempted any quizzes yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Quiz Subject</th>
                            <th>Question</th>
                            <th>Your Answer</th>
                            <th>Correct Answer</th>
                            <th>Result</th>
                            <th>Attempted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['quiz_name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['question_text']); ?></td>
                                <td><?php echo htmlspecialchars($entry['selected_answer']); ?></td>
                                <td><?php echo htmlspecialchars($entry['correct_answer']); ?></td>
                                <td class="<?php echo $entry['is_correct'] ? 'correct' : 'incorrect'; ?>">
                                    <?php echo $entry['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                                </td>
                                <td><?php echo htmlspecialchars($entry['attempted_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>