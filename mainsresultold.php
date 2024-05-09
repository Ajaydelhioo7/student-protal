<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit();
}

require_once './database/db.php'; // Database connection

$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT rollno FROM Students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $student_rollno = $row['rollno'];
} else {
    echo "Student roll number not found.";
    exit;
}
$stmt->close();

// Fetch all test names for dropdown
$testNames = [];
$stmt = $conn->prepare("SELECT DISTINCT testname FROM mains_test_score WHERE rollno = ?");
$stmt->bind_param("s", $student_rollno);
$stmt->execute();
$testResult = $stmt->get_result();
while ($testRow = $testResult->fetch_assoc()) {
    $testNames[] = $testRow['testname'];
}
$stmt->close();

$selectedTest = $_GET['testname'] ?? '';

$scores = [];
if (!empty($selectedTest)) {
    $stmt = $conn->prepare("SELECT * FROM mains_test_score WHERE rollno = ? AND testname = ?");
    $stmt->bind_param("ss", $student_rollno, $selectedTest);
} else {
    $stmt = $conn->prepare("SELECT * FROM mains_test_score WHERE rollno = ?");
    $stmt->bind_param("s", $student_rollno);
}
$stmt->execute();
$result = $stmt->get_result();
$scores = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Main Results</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include './includes/header.php'; ?>

    <main>
        <form action="" method="GET" class="test-selector-form">
            <select name="testname" onchange="this.form.submit()" class="test-selector form-select">
                <option value="">Select a Test</option>
                <?php foreach ($testNames as $name): ?>
                    <option value="<?php echo htmlspecialchars($name); ?>" <?php echo ($selectedTest == $name) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <section class="scores">
            <h4 class="my-4">Detailed Results for <?php echo htmlspecialchars($_SESSION['student_name']); ?></h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="thead-dark">
                        <tr class="bg-warning">
                            <th scope="col">Roll No</th>
                            <th scope="col">Batch</th>
                            <th scope="col">Test Name</th>
                            <th scope="col">Max Marks</th>
                            <th scope="col">Marks Obtained</th>
                            <th scope="col">Percentage</th>
                            <th scope="col">Download PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scores as $score): ?>
                        <tr class="table-light">
                            <td><?php echo htmlspecialchars($score['rollno']); ?></td>
                            <td><?php echo htmlspecialchars($score['batch']); ?></td>
                            <td><?php echo htmlspecialchars($score['testname']); ?></td>
                            <td><?php echo htmlspecialchars($score['max_marks']); ?></td>
                            <td><?php echo htmlspecialchars($score['marks_obtained']); ?></td>
                            <td><?php echo number_format($score['percentage'], 2); ?>%</td>
                            <td>
                                <form action="generate_pdf.php" method="post">
                                    <input type="hidden" name="data" value='<?php echo json_encode($score); ?>'>
                                    <button type="submit" class="btn btn-warning text-dark">Download PDF</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <?php include './includes/footer.php'; ?>
</body>
</html>
