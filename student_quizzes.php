<?php
// Start the session
session_start();

// Include database connection
include '../db_connect.php';

// Fetch student performance data from the student_dashboard_totals table
$query = "SELECT total_attempts, total_correct, total_incorrect, total_unanswered
          FROM student_dashboard_totals WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['student_id']); // Assuming 'student_id' is stored in session
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

// Prepare data for the chart
$totalAttempts = $data['total_attempts'] ?? 0;
$totalCorrect = $data['total_correct'] ?? 0;
$totalIncorrect = $data['total_incorrect'] ?? 0;
$totalUnanswered = $data['total_unanswered'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h3 {
            text-align: center;
            color: #333;
        }
        .summary {
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
            color: #555;
        }
        .summary p strong {
            color: #333;
        }
        .chart-container {
            width: 100%;
            height: 500px;
            position: relative;
        }
        .additional-info {
            margin-top: 30px;
            text-align: center;
            font-size: 16px;
            line-height: 1.6;
        }
        .additional-info span {
            display: inline-block;
            margin: 10px 15px;
            padding: 10px 20px;
            border-radius: 5px;
            background-color: #e9f5ff;
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Student Performance Overview</h3>
    <div class="summary">
        <p>Total Attempts: <strong><?php echo $totalAttempts; ?></strong></p>
        <p>Correct: <strong><?php echo $totalCorrect; ?></strong>, Incorrect: <strong><?php echo $totalIncorrect; ?></strong>, Unanswered: <strong><?php echo $totalUnanswered; ?></strong></p>
    </div>
    <div class="chart-container">
        <canvas id="performanceChart"></canvas>
    </div>
    <div class="additional-info">
        <p>Your overall performance:</p>
        <span>Total Correct Answers: <?php echo $totalCorrect; ?></span>
        <span>Improvement Needed: Focus on reducing incorrect and unanswered questions.</span>
    </div>
</div>

<script>
    // Data fetched dynamically from PHP
    const data = {
        labels: ['Correct', 'Incorrect', 'Unanswered', 'Total Attempts'],
        datasets: [
            {
                label: 'Correct',
                data: [<?php echo $totalCorrect; ?>, null, null, <?php echo $totalCorrect; ?>],
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Incorrect',
                data: [null, <?php echo $totalIncorrect; ?>, null, <?php echo $totalIncorrect; ?>],
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Unanswered',
                data: [null, null, <?php echo $totalUnanswered; ?>, <?php echo $totalUnanswered; ?>],
                borderColor: 'rgba(255, 206, 86, 1)',
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Total Attempts',
                data: [<?php echo $totalCorrect; ?>, <?php echo $totalIncorrect; ?>, <?php echo $totalUnanswered; ?>, <?php echo $totalAttempts; ?>],
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: true,
                tension: 0.4
            }
        ]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Questions'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Performance Categories'
                    }
                }
            }
        }
    };

    // Render the chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, config);
</script>

</body>
</html>
