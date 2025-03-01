<?php
// Include database connection
include '../db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the message data
    $sql = "SELECT * FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $message = $result->fetch_assoc();
    } else {
        echo "Message not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Message Details</h2>
        <div class="card">
            <div class="card-header">
                <strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($message['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?></p>
                <p><strong>Message:</strong></p>
                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
            </div>
            <div class="card-footer text-muted">
                <p><strong>Created At:</strong> <?php echo $message['created_at']; ?></p>
            </div>
        </div>
        <a href="enquiry.php" class="btn btn-primary mt-3">Back to Messages</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
