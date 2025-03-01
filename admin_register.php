<?php
// Include database connection
include('../db_connect.php');

// Initialize variables
$username = $email = $password = $confirmPassword = '';
$errorMessage = $successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate form data
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errorMessage = 'All fields are required.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } else {
        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if the username or email already exists
        $query = $conn->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
        $query->bind_param("ss", $username, $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = 'Username or Email already exists.';
        } else {
            // Insert new admin data into the database
            $insertQuery = $conn->prepare("INSERT INTO admins (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $insertQuery->bind_param("sss", $username, $email, $hashedPassword);

            if ($insertQuery->execute()) {
                $successMessage = 'Admin registered successfully. You can now log in.';
            } else {
                $errorMessage = 'Error occurred. Please try again.';
            }

            $insertQuery->close();
        }

        $query->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <style>
        /* Add your custom CSS styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .register-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .input-box {
            margin-bottom: 20px;
        }

        .input-box label {
            display: block;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .input-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .input-box input:focus {
            border-color: #00d9ff;
            outline: none;
        }

        .button {
            width: 100%;
            padding: 10px;
            background-color: #00d9ff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #00aaff;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .success-message {
            color: green;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Admin Registration</h2>

        <!-- Display error or success message -->
        <?php if ($errorMessage): ?>
            <div class="error-message"><?= $errorMessage; ?></div>
        <?php elseif ($successMessage): ?>
            <div class="success-message"><?= $successMessage; ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_register.php">
            <div class="input-box">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>
            </div>

            <div class="input-box">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required>
            </div>

            <div class="input-box">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="input-box">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="button">Register</button>
        </form>
    </div>
</body>
</html>
