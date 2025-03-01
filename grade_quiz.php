<?php
session_start();
include '../db_connect.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    die("Error: Student not logged in.");
}

$studentId = $_SESSION['student_id'];
$quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : null;

if (!$quizId) {
    die("Error: Quiz ID not provided.");
}

// Initialize counters
$correctCount = 0;
$incorrectCount = 0;
$unansweredCount = 0;
$questionsWithAnswers = [];

// Loop through submitted answers
foreach ($_POST as $key => $value) {
    if (strpos($key, 'question_') === 0) {
        $question_id = (int)str_replace('question_', '', $key);
        $userAnswerOption = trim($value);
        $correctAnswerKey = "correct_" . $question_id;
        $correctAnswerText = isset($_POST[$correctAnswerKey]) ? trim($_POST[$correctAnswerKey]) : null;

        if (!$correctAnswerText) {
            error_log("Correct answer not found for question_id: $question_id");
            continue;
        }

        // Fetch question details
        $query = "SELECT question_text, option_a, option_b, option_c, option_d 
                  FROM questions WHERE question_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
        $stmt->close();

        if (!$question) {
            error_log("Question not found for question_id: $question_id");
            continue;
        }

        $options = [
            'A' => $question['option_a'],
            'B' => $question['option_b'],
            'C' => $question['option_c'],
            'D' => $question['option_d']
        ];

        $isCorrect = false;
        $userAnswerText = isset($options[$userAnswerOption]) ? $options[$userAnswerOption] : 'Not answered';

        if ($userAnswerText === $correctAnswerText) {
            $correctCount++;
            $isCorrect = 1;
        } elseif ($userAnswerText === 'Not answered') {
            $unansweredCount++;
            $isCorrect = 0;
        } else {
            $incorrectCount++;
            $isCorrect = 0;
        }

        $score = $isCorrect ? 1 : 0;

        // Insert into quiz_history
        $query = "INSERT INTO quiz_history (student_id, quiz_id, question_id, selected_answer, correct_answer, is_correct, score) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiissii", $studentId, $quizId, $question_id, $userAnswerText, $correctAnswerText, $isCorrect, $score);
        if (!$stmt->execute()) {
            error_log("Failed to insert into quiz_history: " . $stmt->error);
        }
        $stmt->close();

        $questionsWithAnswers[] = [
            'question_text' => $question['question_text'],
            'user_answer' => $userAnswerText,
            'correct_answer' => $correctAnswerText,
            'is_correct' => $isCorrect
        ];
    }
}

$totalQuestions = $correctCount + $incorrectCount + $unansweredCount;

// Update student_dashboard_totals
$query = "INSERT INTO student_dashboard_totals (student_id, total_attempts, total_correct, total_incorrect, total_unanswered) 
          VALUES (?, ?, ?, ?, ?) 
          ON DUPLICATE KEY UPDATE 
          total_attempts = total_attempts + ?, 
          total_correct = total_correct + ?, 
          total_incorrect = total_incorrect + ?, 
          total_unanswered = total_unanswered + ?";
$stmt = $conn->prepare($query);
$attempts = 1; // Increment by 1 for this attempt
$stmt->bind_param("iiiiiiiii", $studentId, $attempts, $correctCount, $incorrectCount, $unansweredCount, $attempts, $correctCount, $incorrectCount, $unansweredCount);
$stmt->execute();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #007bff;
            font-size: 2rem;
            margin: 0;
        }
        .dashboard-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .dashboard-btn:hover {
            background-color: #0056b3;
        }
        .summary {
            text-align: center;
            margin: 30px 0;
        }
        .summary p {
            font-size: 1.2rem;
        }
        .pass-status {
            font-size: 1.4rem;
            font-weight: bold;
            color: <?= $totalQuestions > 0 && $correctCount / $totalQuestions >= 0.5 ? 'green' : 'red' ?>;
        }
        .question {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f1f3f5;
            border-radius: 8px;
            position: relative;
        }
        .question h4 {
            margin: 0 0 5px;
        }
        .user-answer {
            color: red;
            font-weight: bold;
            display: inline-block;
        }
        .correct-answer {
            color: green;
            font-weight: bold;
        }
        .icon {
            margin-left: 10px;
            font-size: 1.5rem;
        }
        .icon.correct {
            color: green;
        }
        .icon.incorrect {
            color: red;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quiz Results</h1>
            <a href="studentdashboard.php" class="dashboard-btn">Return to Dashboard</a>
        </div>
        
        <h2>Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Total Questions</th>
                    <th>Correct Answers</th>
                    <th>Incorrect Answers</th>
                    <th>Unanswered Questions</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $totalQuestions ?></td>
                    <td><?= $correctCount ?></td>
                    <td><?= $incorrectCount ?></td>
                    <td><?= $unansweredCount ?></td>
                    <td class="pass-status"><?= $totalQuestions > 0 && $correctCount / $totalQuestions >= 0.5 ? 'Pass' : 'Fail' ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="summary">
            <p><strong>Details of the quiz attempt:</strong></p>
        </div>

        <div class="questions">
            <?php foreach ($questionsWithAnswers as $qna) { ?>
                <div class="question">
                    <h4>Question: <?= htmlspecialchars($qna['question_text']) ?></h4>
                    <p>Your Answer: <span class="user-answer"><?= htmlspecialchars($qna['user_answer']) ?></span>
                    <?php if ($qna['is_correct']) { ?>
                        <i class="fas fa-check-circle icon correct"></i>
                    <?php } else { ?>
                        <i class="fas fa-times-circle icon incorrect"></i>
                    <?php } ?>
                    </p>
                    <p>Correct Answer: <span class="correct-answer"><?= htmlspecialchars($qna['correct_answer']) ?></span></p>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>