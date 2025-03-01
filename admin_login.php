<?php
// Start session
session_start();

// Include database connection
include('../db_connect.php');

// Initialize variables
$username = $password = '';
$errorMessage = '';

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        $errorMessage = 'Both username and password are required.';
    } else {
        // Check if username exists in the database
        $query = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            // Username found, fetch the admin data
            $admin = $result->fetch_assoc();

            // Verify the password using password_verify()
            if (password_verify($password, $admin['password'])) {
                // Password is correct, set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];

                // Redirect to the admin dashboard
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $errorMessage = 'Incorrect password. Please try again.';
            }
        } else {
            $errorMessage = 'No account found with that username.';
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
  <title>Admin Panel Login</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #1b1b2f;
      color: #ffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-box {
      background-color: #162447;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      text-align: center;
      width: 300px;
    }

    .icon {
      margin-bottom: 20px;
    }

    .icon img {
      width: 40px;
      height: 40px;
    }

    h2 {
      font-size: 24px;
      margin-bottom: 20px;
    }

    .input-box {
      margin-bottom: 20px;
      text-align: left;
    }

    .input-box label {
      display: block;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .input-box input {
      width: 100%;
      padding: 10px;
      border: none;
      border-bottom: 2px solid #00d9ff;
      background: none;
      color: white;
      font-size: 14px;
    }

    .input-box input:focus {
      outline: none;
      border-bottom: 2px solid #00ffff;
    }

    button {
      padding: 10px 20px;
      background-color: #00d9ff;
      color: #000;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #00aaff;
    }

    .error-message {
      color: red;
      font-size: 14px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <div class="icon">
        <!-- Replace with your desired icon -->
        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%2300d9ff' viewBox='0 0 24 24'%3E%3Cpath d='M17 8A5 5 0 1 0 7 8a5 5 0 0 0 10 0zm-7 0a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm8 6h2v8h-8v-8h2v-2h-6v2h2v8H2v-8h2v-2H0v-3a6 6 0 1 1 12 0v3H9v-2h6v2h-3v8h6v-8z'/%3E%3C/svg%3E" alt="Key Icon">
      </div>
      <h2>Admin Panel</h2>

      <!-- Display error message if exists -->
      <?php if ($errorMessage): ?>
        <div class="error-message"><?= $errorMessage; ?></div>
      <?php endif; ?>

      <form method="POST" action="admin_login.php">
        <div class="input-box">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="input-box">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
      </form>
    </div>
  </div>
</body>
</html>
