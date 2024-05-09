<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="../css/teacher_login.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            animation: fadeIn 1.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .container {
            background: white;
            padding: 25px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 340px;
            animation: popIn 0.5s ease-out;
        }
        @keyframes popIn {
            from { transform: scale(0.5); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        h4 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #FFA500; /* Orange border focus */
        }
        input[type="submit"] {
            background-color: #FFA500; /* Orange submit button */
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #cc8400;
        }
        .logo {
            display: block;
            margin: 10px auto 20px;
            width: 284px;
    margin-bottom: 38px;
        }
    </style>
   
</head>
<body>
<?php include '../includes/header.php'; ?>
   
  
    <div class="container">
    <!-- <h4>Student Login</h4> -->
        <img src="./assets/img/aladin.png" alt="Company Logo" class="logo"> <!-- Adjust the src attribute to your logo's path -->
        
        <form action="login_student.php" method="post">
            Roll No: <input type="text" name="rollno" required><br>
            Batch: <input type="text" name="batch" required><br>
            <input type="submit" name="submit" value="Login">
        </form>
    </div>

    <?php
session_start(); // Start the session at the very beginning
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './database/db.php'; // Database connection

if (isset($_POST['submit'])) {
    $rollno = $_POST['rollno'];
    $batch = $_POST['batch'];

    $stmt = $conn->prepare("SELECT * FROM Students WHERE rollno = ? AND batch = ?");
    if (false === $stmt) {
        echo "Prepare failed: " . $conn->error;
        exit;
    }

    $bind = $stmt->bind_param("ss", $rollno, $batch); // Assuming both are strings
    if (false === $bind) {
        echo "Bind failed: " . $stmt->error;
        exit;
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['student_name'] = $user['name'];
            header("Location: student_dashboard.php");
            exit();
        } else {
            echo "Invalid Roll No or Batch!";
        }
    } else {
        echo "Execute failed: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>

</body>
</html>
