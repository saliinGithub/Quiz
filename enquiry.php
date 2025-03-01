<?php
// Include database connection
include '../db_connect.php';

// Fetch data from the contact_message table
$sql = "SELECT * FROM contact_messages";
$result = $conn->query($sql);

// Check if the query returned any results
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Homepage Button -->
        <div class="d-flex justify-content-between mb-4">
            <h2 class="text-center">Contact Messages</h2>
            <a href="admin_dashboard.php" class="btn btn-primary">Homepage</a>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['message'], 0, 50)); ?>...</td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <a href="read_message.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Read</a>
                                <a href="delete_message.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No messages found.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
