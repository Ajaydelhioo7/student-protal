<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit;
}

include './database/db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $student_id = $_SESSION['student_id'];

    $stmt = $conn->prepare("UPDATE Students SET name = ?, email = ?, phone = ? WHERE id = ?");
    if (!$stmt) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    if (!$stmt->bind_param("sssi", $name, $email, $phone, $student_id)) {
        die('Bind param failed: ' . htmlspecialchars($stmt->error));
    }
    if (!$stmt->execute()) {
        $_SESSION['message'] = 'Error updating profile: ' . htmlspecialchars($stmt->error);
    } else {
        $_SESSION['message'] = 'Profile updated successfully!';
    }
    $stmt->close();

    // Redirect to prevent form resubmission
    header('Location: student_profile.php');
    exit();
}

include('./includes/header.php');

// Fetch the current details to show in the form
if (!isset($user)) {
    $stmt = $conn->prepare("SELECT name, email, phone FROM Students WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['student_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 600px;
            margin-top: 50px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h4>Update Profile</h4>
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
    }
    ?>
    <form action="student_profile.php" method="post">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="text" class="form-control" id="phone" name="phone" required value="<?php echo htmlspecialchars($user['phone']); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="slogout.php" class="btn btn-danger logout-button">Logout</a>
    </form>
</div>
<?php include './includes/footer.php'; ?>
</body>
</html>
