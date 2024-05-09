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
$stmt = $conn->prepare("SELECT DISTINCT testname FROM Test_Scores WHERE rollno = ?");
$stmt->bind_param("s", $student_rollno);
$stmt->execute();
$testResult = $stmt->get_result();
while ($testRow = $testResult->fetch_assoc()) {
    $testNames[] = $testRow['testname'];
}
$stmt->close();

$selectedTest = $_GET['testname'] ?? '';
$highestMarks = $averageMarks = 0;
$scores = [];

if (!empty($selectedTest)) {
    $stmt = $conn->prepare("
        SELECT rollno, batch, testname, right_question, wrong_question, not_attempted, max_marks, marks_obtained, percentage, award_for_wrong, award_for_right
        FROM Test_Scores
        WHERE rollno = ? AND testname = ?
    ");
    $stmt->bind_param("ss", $student_rollno, $selectedTest);
    $stmt->execute();
    $result = $stmt->get_result();
    $scores = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fetch highest marks achieved on this test among all users
    $stmt = $conn->prepare("
        SELECT MAX(marks_obtained) AS highest_marks, AVG(marks_obtained) AS average_marks
        FROM Test_Scores
        WHERE testname = ?
    ");
    $stmt->bind_param("s", $selectedTest);
    $stmt->execute();
    $marksResult = $stmt->get_result();
    if ($marksRow = $marksResult->fetch_assoc()) {
        $highestMarks = $marksRow['highest_marks'];
        $averageMarks = $marksRow['average_marks'];
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include './includes/header.php'; ?>
    <h4 class="text-dark">Pre Test Results</h4>
    <main class="container mt-4">
        <form action="" method="GET" class="test-selector-form mb-5">
            <select name="testname" onchange="this.form.submit()" class="form-select">
                <option value="">Select a Test</option>
                <?php foreach ($testNames as $name): ?>
                    <option value="<?php echo htmlspecialchars($name); ?>" <?php echo ($selectedTest == $name) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="row">
            <?php foreach ($scores as $score): ?>
                <div class="col-md-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title text-center">Test Name : <?php echo htmlspecialchars($score['testname']); ?></h5>
                            <p class="card-text">
                                <strong>Batch:</strong> <?php echo htmlspecialchars($score['batch']); ?><br>
                                <strong>Right Questions:</strong> <?php echo htmlspecialchars($score['right_question']); ?><br>
                                <strong>Wrong Questions:</strong> <?php echo htmlspecialchars($score['wrong_question']); ?><br>
                                <strong>Not Attempted:</strong> <?php echo htmlspecialchars($score['not_attempted']); ?><br>
                                <strong>Max Marks:</strong> <?php echo htmlspecialchars($score['max_marks']); ?><br>
                                <strong>Marks Obtained:</strong> <?php echo htmlspecialchars($score['marks_obtained']); ?><br>
                                <strong>Percentage:</strong> <?php echo number_format($score['percentage'], 2); ?>%<br>
                                <strong>Award for Wrong:</strong> <?php echo htmlspecialchars($score['award_for_wrong']); ?><br>
                                <strong>Award for Right:</strong> <?php echo htmlspecialchars($score['award_for_right']); ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <form action="generate_pdf.php" method="post" class="d-inline">
                                <input type="hidden" name="data" value='<?php echo json_encode($score); ?>'>
                                <button type="submit" class="btn btn-warning">Download PDF</button>
                            </form>
                            <button onclick="showAnalytics('<?php echo $highestMarks; ?>', '<?php echo $averageMarks; ?>', '<?php echo $score['marks_obtained']; ?>')" class="btn btn-info">Analytics</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="analyticsModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Test Analytics</h5>
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                </div>
                <div class="modal-body">
                    <canvas id="analyticsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        var chart; // Global variable to hold the chart instance

        function showAnalytics(highestMarks, averageMarks, userMarks) {
            const ctx = document.getElementById('analyticsChart').getContext('2d');
            if (chart) {
                chart.destroy(); // Destroy the existing chart instance if exists
            }
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Highest Marks', 'Average Marks', 'Your Marks'],
                    datasets: [{
                        label: 'Test Performance',
                        data: [highestMarks, averageMarks, userMarks],
                        backgroundColor: ['rgba(54, 162, 235, 0.6)', 'rgba(255, 206, 86, 0.6)', 'rgba(255, 99, 132, 0.6)'],
                        borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(255, 99, 132, 1)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            $('#analyticsModal').modal('show');
        }
    </script>
</body>
</html>
