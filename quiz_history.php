<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['student_id'])) {
    die("Please log in to view your quiz history.");
}

$studentId = $_SESSION['student_id'];

// Fetch quiz history with question text
$query = "SELECT qh.history_id, qh.quiz_id, q.quiz_name, qh.question_id, qn.question_text, 
          qh.selected_answer, qh.correct_answer, qh.is_correct, qh.attempted_at 
          FROM quiz_history qh 
          JOIN quizzes q ON qh.quiz_id = q.quiz_id 
          JOIN questions qn ON qh.question_id = qn.question_id 
          WHERE qh.student_id = ? 
          ORDER BY qh.attempted_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 50px; 
            background-color: #f5f7fa; 
        }
        h1 { 
            color: #007BFF; 
            text-align: center; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background-color: #fff; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        }
        th, td { 
            padding: 12px; 
            border: 1px solid #ddd; 
            text-align: left; 
        }
        th { 
            background-color: #007BFF; 
            color: white; 
        }
        .correct { 
            color: green; 
            font-weight: bold; 
        }
        .incorrect { 
            color: red; 
            font-weight: bold; 
        }
        a { 
            display: inline-block; 
            margin-top: 20px; 
            padding: 10px 20px; 
            background-color: #007BFF; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
        }
        a:hover { 
            background-color: #0056b3; 
        }
    </style>
</head>
<body>
    <h1>Your Quiz History</h1>
    <table>
        <thead>
            <tr>
                <th>Quiz Name</th>
                <th>Question</th>
                <th>Your Answer</th>
                <th>Correct Answer</th>
                <th>Result</th>
                <th>Date Attempted</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $entry) { ?>
                <tr>
                    <td><?= htmlspecialchars($entry['quiz_name']) ?></td>
                    <td><?= htmlspecialchars($entry['question_text']) ?></td>
                    <td><?= htmlspecialchars($entry['selected_answer']) ?></td>
                    <td><?= htmlspecialchars($entry['correct_answer']) ?></td>
                    <td class="<?= $entry['is_correct'] ? 'correct' : 'incorrect' ?>">
                        <?= $entry['is_correct'] ? 'Correct' : 'Incorrect' ?>
                    </td>
                    <td><?= $entry['attempted_at'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="studentdashboard.php">Back to Dashboard</a>
</body>
</html>