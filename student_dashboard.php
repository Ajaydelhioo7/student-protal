<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit;
}

include './database/db.php'; // Ensure this path is correct.

$student = [];
$preRank = 0;
$mainsRank = 0;

// Fetch student details
$stmt = $conn->prepare("SELECT * FROM Students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "No student found with this ID.";
}
$stmt->close();

// Fetch Pre Rank for the same batch
$stmt = $conn->prepare("SELECT percentage, FIND_IN_SET( percentage, (
    SELECT GROUP_CONCAT( percentage ORDER BY percentage DESC ) 
    FROM Test_Scores 
    WHERE batch = ?
) ) AS rank FROM Test_Scores WHERE rollno = ? AND batch = ?");
$stmt->bind_param("sis", $student['batch'], $student['rollno'], $student['batch']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $preRank = $row['rank'];
}
$stmt->close();

// Fetch Mains Rank for the same batch
$stmt = $conn->prepare("SELECT percentage, FIND_IN_SET( percentage, (
    SELECT GROUP_CONCAT( percentage ORDER BY percentage DESC ) 
    FROM mains_test_score 
    WHERE batch = ?
) ) AS rank FROM mains_test_score WHERE rollno = ? AND batch = ?");
$stmt->bind_param("sis", $student['batch'], $student['rollno'], $student['batch']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $mainsRank = $row['rank'];
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include './includes/header.php'; ?> 
    <h5>Welcome, <?php echo htmlspecialchars($student['name']); ?></h5>
    <div class="container mb-5">
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark text-center"><h5>Pre Rank</h5></div>
                    <div class="card-body">
                        <h3 class="card-title text-center text-primary"><?php echo $preRank; ?></h3>
                        <p class="card-text text-dark text-center">Prelims : Cumulative Rank Till Now</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-success text-dark text-center"><h5>Mains Rank</h5></div>
                    <div class="card-body">
                        <h3 class="card-title text-center text-primary"><?php echo $mainsRank; ?></h3>
                        <p class="card-text text-dark text-center">Mains : Cumulative Rank Till Now</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
