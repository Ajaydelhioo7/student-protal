<?php
session_start();
session_destroy(); // Destroy all session data
header("Location: login_student.php"); // Redirect to login page
exit();
?>
