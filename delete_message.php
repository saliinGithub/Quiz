<?php
// Include database connection
include '../db_connect.php';

// Check if the `id` is set in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Get the ID and ensure it is an integer

    // Prepare the SQL DELETE statement
    $sql = "DELETE FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect to enquiry.php with a success message
        header("Location: enquiry.php?status=success&msg=Message+deleted+successfully");
    } else {
        // Redirect to enquiry.php with an error message
        header("Location: enquiry.php?status=error&msg=Failed+to+delete+the+message");
    }

    // Close the statement
    $stmt->close();
} else {
    // If no ID is set, redirect back with an error message
    header("Location: enquiry.php?status=error&msg=Invalid+message+ID");
}

// Close the database connection
$conn->close();
?>
