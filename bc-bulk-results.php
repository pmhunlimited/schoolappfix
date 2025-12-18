<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("include/config-file.php");

if (isset($_POST['print_bulk_reports'])) {
    $class_id = $_POST['class'];
    $session = $_POST['session'];
    $school_id = $_SESSION['school_id'];

    // Sanitize inputs
    $class_id = mysqli_real_escape_string($connection_server, $class_id);
    $session = mysqli_real_escape_string($connection_server, $session);
    $school_id = mysqli_real_escape_string($connection_server, $school_id);

    $students_query = "SELECT * FROM sm_students WHERE school_id_number = '$school_id' AND current_class = '$class_id' AND session = '$session'";
    $students_result = mysqli_query($connection_server, $students_query);

    if (!$students_result) {
        die("Error fetching students: " . mysqli_error($connection_server));
    }

    while ($student = mysqli_fetch_assoc($students_result)) {
        // Generate a report card for each student
        echo "Generating report card for " . $student['firstname'] . " " . $student['lastname'] . "<br>";
        // You would typically include the report card generation logic here
    }
}
?>
