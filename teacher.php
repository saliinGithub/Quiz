<?php
include('../db_connect.php');

// Fetch all teachers with their subject names by joining with the subjects table
$sql = "SELECT t.teacher_id, t.username, t.email, t.first_name, t.last_name, t.contact_no, t.qualification, 
               s.subject_name, t.status 
        FROM teachers t 
        LEFT JOIN subjects s ON t.subject_id = s.subject_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .btn {
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            margin: 5px;
        }
        .approve-btn {
            background-color: #4CAF50;
        }
        .reject-btn {
            background-color: #f44336;
        }
        .approved-btn {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .view-questions-btn {
            background-color: #2196F3; /* Blue color for View Questions */
        }
        .homepage-btn {
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Admin Portal - Manage Teacher Registrations</h1>
    
    <!-- Homepage button -->
    <a href="admin_dashboard.php" class="homepage-btn">Go to Homepage</a>
    
    <!-- Display success/error messages -->
    <?php
    if (isset($_GET['message'])) {
        echo "<p style='color: green;'>" . htmlspecialchars($_GET['message']) . "</p>";
    } elseif (isset($_GET['error'])) {
        echo "<p style='color: red;'>" . htmlspecialchars($_GET['error']) . "</p>";
    }
    ?>
    
    <table>
        <tr>
            <th>Teacher ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Contact No</th>
            <th>Qualification</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['teacher_id'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "<td>" . $row['first_name'] . "</td>";
                echo "<td>" . $row['last_name'] . "</td>";
                echo "<td>" . $row['contact_no'] . "</td>";
                echo "<td>" . $row['qualification'] . "</td>";
                echo "<td>" . ($row['subject_name'] ? $row['subject_name'] : 'Not Assigned') . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td>";
                
                // Approval/Rejection buttons
                if ($row['status'] === 'approved') {
                    echo "<span class='btn approved-btn'>Approved</span>";
                } else {
                    echo "<a class='btn approve-btn' href='admin_actions.php?action=approve&id=" . $row['teacher_id'] . "'>Approve</a>";
                }
                echo "<a class='btn reject-btn' href='admin_actions.php?action=reject&id=" . $row['teacher_id'] . "'>Reject</a>";
                
                // Add View Questions button
                echo "<a class='btn view-questions-btn' href='teacher_questions.php?teacher_id=" . $row['teacher_id'] . "'>View Questions</a>";
                
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10'>No teachers found.</td></tr>";
        }
        ?>
    </table>
</body>
</html>
<?php $conn->close(); ?>