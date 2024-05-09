<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="../css/teacher_login.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 300px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
    </style>
   
</head>
<body>
<?php include '../includes/header.php'; ?>
   
    <div class="container">
    <h1>Student Login</h1>
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
