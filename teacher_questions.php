<?php
include('../db_connect.php');

// Check if teacher_id is provided
if (!isset($_GET['teacher_id']) || empty($_GET['teacher_id'])) {
    echo "Teacher ID not specified.";
    exit;
}

$teacher_id = $_GET['teacher_id'];

// Fetch teacher info including their subject_id
$teacher_sql = "SELECT t.username, t.first_name, t.last_name, t.subject_id, s.subject_name AS teacher_subject 
                FROM teachers t 
                LEFT JOIN subjects s ON t.subject_id = s.subject_id 
                WHERE t.teacher_id = ?";
$teacher_stmt = $conn->prepare($teacher_sql);
$teacher_stmt->bind_param("i", $teacher_id);
$teacher_stmt->execute();
$teacher_result = $teacher_stmt->get_result();
$teacher = $teacher_result->fetch_assoc();
$teacher_stmt->close();

// Fetch questions set by this teacher, restricted to their subject
$sql = "SELECT q.question_id, q.quiz_id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_answer, 
               s.subject_name 
        FROM questions q 
        INNER JOIN quizzes z ON q.quiz_id = z.quiz_id 
        INNER JOIN subjects s ON z.subject_id = s.subject_id 
        WHERE z.teacher_id = ? AND z.subject_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teacher_id, $teacher['subject_id']);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questions by Teacher <?php echo htmlspecialchars($teacher['username']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background-color: #1976D2;
        }
    </style>
</head>
<body>
    <h1>Questions Set by <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?> (<?php echo htmlspecialchars($teacher['username']); ?>)</h1>
    
    <a href="teacher.php" class="back-btn">Back to Teacher List</a>
    
    <?php if (count($questions) > 0): ?>
        <table>
            <tr>
                <th>Question ID</th>
                <th>Quiz ID</th>
                <th>Subject</th>
                <th>Question Text</th>
                <th>Option A</th>
                <th>Option B</th>
                <th>Option C</th>
                <th>Option D</th>
                <th>Correct Answer</th>
            </tr>
            <?php foreach ($questions as $question): ?>
                <tr>
                    <td><?php echo htmlspecialchars($question['question_id']); ?></td>
                    <td><?php echo htmlspecialchars($question['quiz_id']); ?></td>
                    <td><?php echo htmlspecialchars($question['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_a']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_b']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_c']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_d']); ?></td>
                    <td><?php echo htmlspecialchars($question['correct_answer']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No questions found for this teacher in their assigned subject (<?php echo htmlspecialchars($teacher['teacher_subject']); ?>).</p>
    <?php endif; ?>
</body>
</html>