<?php
include('../db_connect.php');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $teacher_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        // Update status to 'approved'
        $sql = "UPDATE `teachers` SET `status` = 'approved' WHERE `teacher_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
        
        if ($stmt->execute()) {
            header("Location: teacher.php?message=Teacher approved successfully");
        } else {
            header("Location: teacher.php?error=Failed to approve teacher");
        }
        $stmt->close();
    } elseif ($action === 'reject') {
        // Step 1: Delete from quiz_history where linked to teacher's quizzes
        $sql_dep1 = "DELETE qh FROM `quiz_history` qh
                     INNER JOIN `questions` q ON qh.question_id = q.question_id
                     INNER JOIN `quizzes` qu ON q.quiz_id = qu.quiz_id
                     WHERE qu.teacher_id = ?";
        $stmt_dep1 = $conn->prepare($sql_dep1);
        $stmt_dep1->bind_param("i", $teacher_id);
        $stmt_dep1->execute();
        $stmt_dep1->close();

        // Step 2: Delete from questions where linked to teacher's quizzes
        $sql_dep2 = "DELETE q FROM `questions` q
                     INNER JOIN `quizzes` qu ON q.quiz_id = qu.quiz_id
                     WHERE qu.teacher_id = ?";
        $stmt_dep2 = $conn->prepare($sql_dep2);
        $stmt_dep2->bind_param("i", $teacher_id);
        $stmt_dep2->execute();
        $stmt_dep2->close();

        // Step 3: Delete from quizzes
        $sql_dep3 = "DELETE FROM `quizzes` WHERE `teacher_id` = ?";
        $stmt_dep3 = $conn->prepare($sql_dep3);
        $stmt_dep3->bind_param("i", $teacher_id);
        $stmt_dep3->execute();
        $stmt_dep3->close();

        // Step 4: Delete the teacher
        $sql = "DELETE FROM `teachers` WHERE `teacher_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
        
        if ($stmt->execute()) {
            header("Location: teacher.php?message=Teacher rejected and deleted successfully");
        } else {
            header("Location: teacher.php?error=Failed to reject teacher: " . $conn->error);
        }
        $stmt->close();
    } else {
        header("Location: teacher.php?error=Invalid action");
    }
} else {
    header("Location: teacher.php?error=Missing parameters");
}

$conn->close();
exit();
?>