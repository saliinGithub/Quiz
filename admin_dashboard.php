<?php
// Include database connection
include '../db_connect.php';

// Query to count total teachers
$teacher_query = "SELECT COUNT(*) AS total_teachers FROM teachers";
$teacher_result = $conn->query($teacher_query);
$teacher_count = $teacher_result->fetch_assoc()['total_teachers'];

// Query to count total students
$student_query = "SELECT COUNT(*) AS total_students FROM students";
$student_result = $conn->query($student_query);
$student_count = $student_result->fetch_assoc()['total_students'];

// Query to count total enquiries
$enquiry_query = "SELECT COUNT(*) AS total_enquiries FROM contact_messages"; // Updated table name
$enquiry_result = $conn->query($enquiry_query);
$enquiry_count = $enquiry_result->fetch_assoc()['total_enquiries'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: #f5f5f5;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background-color: #2c3e50;
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px 0;
      height: 100vh;
      position: fixed;
    }

    .sidebar .logo {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .sidebar img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin-bottom: 10px;
    }

    .sidebar .menu {
      width: 100%;
      list-style: none;
      margin-top: 20px;
    }

    .sidebar .menu li {
      width: 100%;
      padding: 15px 20px;
      text-align: left;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .sidebar .menu li:hover {
      background-color: #34495e;
    }

    .sidebar .menu li a {
      color: white;
      text-decoration: none;
    }

    /* Main Content */
    .main-content {
      margin-left: 250px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    /* Header */
    .header {
      height: 60px;
      background-color: #2c3e50;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
    }

    .header .logout {
      background-color: #3498db;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.3s;
    }

    .header .logout:hover {
      background-color: #2980b9;
    }

    /* Dashboard Cards */
    .dashboard {
      flex: 1;
      padding: 20px;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      height: calc(100vh - 60px); /* Adjust for header height */
    }

    /* Individual Cards */
    .dashboard .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 20px;
      transition: transform 0.3s, box-shadow 0.3s;
      height: 90%; /* Make cards stretch to fill available space */
      text-align: center; /* Ensure content is centered */
      text-decoration: none; /* Remove the default link styling */
    }

    .dashboard .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    .dashboard .card:nth-child(1) {
      background-color: #dc3545;
    }

    .dashboard .card:nth-child(2) {
      background-color: #28a745;
    }

    .dashboard .card:nth-child(3) {
        background-color: #0056b3;
        grid-column: span 1; /* Adjust this to span only 1 column */
        justify-self: stretch; /* Ensure the card stretches to fill the space */
    }

    .dashboard .card img {
      width: 40px;
      height: 40px;
      margin-bottom: 10px;
    }

    .dashboard .card h3 {
      margin-bottom: 5px;
      font-size: 16px;
      color: white;
    }

    .dashboard .card span {
      font-size: 20px;
      font-weight: bold;
      color: white;
    }

    .card1 {
      max-width: 70%;
    }

    /* Footer */
    .footer {
      background-color: #2c3e50;
      color: white;
      text-align: center;
      padding: 10px;
      font-size: 14px;
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 768px) {
      .sidebar {
        width: 200px;
      }

      .main-content {
        margin-left: 200px;
      }

      .dashboard {
        grid-template-columns: 1fr;
      }

      .dashboard .card {
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }

      .main-content {
        margin-left: 0;
      }

      .header {
        flex-direction: column;
        height: auto;
        padding: 10px;
      }

      .header .logout {
        margin-top: 10px;
      }

      .dashboard {
        grid-template-columns: 1fr;
        padding: 10px;
      }

      .dashboard .card {
        width: 100%;
        padding: 15px;
      }

      .sidebar img {
        width: 50px;
        height: 50px;
      }

      .sidebar .logo {
        font-size: 18px;
        margin-bottom: 10px;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">QUIZHUB</div>
    <img src="admin.jpg" alt="Admin Avatar">
    <div>Admin</div>
    <ul class="menu">
      <li><a href="#">Dashboard</a></li>
      <li><a href="teacher.php">Teacher</a></li>
      <li><a href="student.php">Student</a></li>
      <li><a href="enquiry.php">Enquiry</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <span>Admin Dashboard</span>
      <button class="logout" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard">
      <a href="teacher.php" class="card">
        <img src="teacher.jpg" alt="Teacher Logo">
        <h3>Total Teachers</h3>
        <span><?php echo $teacher_count; ?></span>
      </a>
      <a href="student.php" class="card">
        <img src="student.jpg" alt="Student Logo">
        <h3>Total Students</h3>
        <span><?php echo $student_count; ?></span>
      </a>
      <a href="enquiry.php" class="card">
        <img src="ask.png" alt="Enquiry Logo">
        <h3>Total Enquiries</h3>
        <span><?php echo $enquiry_count; ?></span>
      </a>
    </div>

    <!-- Footer -->
    <div class="footer">
      &copy; 2024 Online Quiz. All Rights Reserved.
    </div>
  </div>
</body>
</html>
