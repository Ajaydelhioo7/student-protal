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
$highestMarks = $averageMarks = 0;
$scores = [];

if (!empty($selectedTest)) {
    $stmt = $conn->prepare("SELECT * FROM mains_test_score WHERE rollno = ? AND testname = ?");
    $stmt->bind_param("ss", $student_rollno, $selectedTest);
    $stmt->execute();
    $result = $stmt->get_result();
    $scores = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fetch highest and average marks for the test
    $stmt = $conn->prepare("
        SELECT MAX(marks_obtained) AS highest_marks, AVG(marks_obtained) AS average_marks
        FROM mains_test_score
        WHERE testname = ?
    ");
    $stmt->bind_param("s", $selectedTest);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($marksRow = $result->fetch_assoc()) {
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
    <title>Student Main Results</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include './includes/header.php'; ?>
<h4 class="text-dark">Mains Test Results</h4>
    <main class="container mt-4">
        <form action="" method="GET" class="mb-3 test-selector-form mb-5">
            <select name="testname" onchange="this.form.submit()" class="form-select">
                <option value="">Select a Test</option>
                <?php foreach ($testNames as $name): ?>
                    <option value="<?php echo htmlspecialchars($name); ?>" <?php echo ($selectedTest == $name) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php foreach ($scores as $score): ?>
<div class="card mb-3">
    <div class="card-header">
        Test Details: <?php echo htmlspecialchars($score['testname']); ?>
    </div>
    <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($score['batch']); ?></h5>
        <p class="card-text">
            Roll No: <?php echo htmlspecialchars($score['rollno']); ?><br>
            Max Marks: <?php echo htmlspecialchars($score['max_marks']); ?><br>
            Marks Obtained: <?php echo htmlspecialchars($score['marks_obtained']); ?><br>
            Percentage: <?php echo number_format($score['percentage'], 2); ?>%
        </p>
        <div class="card-footer">
            <form action="generate_pdf.php" method="post" style="display:inline;">
                <input type="hidden" name="data" value='<?php echo json_encode($score); ?>'>
                <button type="submit" class="btn btn-warning">Download PDF</button>
            </form>
            <button onclick="showAnalytics(<?php echo $highestMarks; ?>, <?php echo $averageMarks; ?>, <?php echo $score['marks_obtained']; ?>)" class="btn btn-info">Analytics</button>
        </div>
    </div>
</div>
<?php endforeach; ?>


        <div id="analyticsModal" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Test Analytics</h5>
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

    </main>

    <script>
        var chart; // Global variable to hold the chart instance

        function showAnalytics(highestMarks, averageMarks, userMarks) {
            const ctx = document.getElementById('analyticsChart').getContext('2d');
            if (chart) {
                chart.destroy();
            }
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Highest Marks', 'Average Marks', 'Your Marks'],
                    datasets: [{
                        label: 'Performance',
                        data: [highestMarks, averageMarks, userMarks],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(255, 206, 86, 0.6)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <?php include './includes/footer.php'; ?>
</body>
</html>
