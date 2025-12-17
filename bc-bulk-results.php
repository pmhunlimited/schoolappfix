<?php
session_start();
error_reporting(0);
include("include/config-file.php");
include("include/func/helpers.php");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Bulk Report Cards</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="stylesheet" href="cssfile/font-family.css">
    <link rel="stylesheet" href="cssfile/portal.css">
    <style>
        @media print {
            .noprint {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

<div class="noprint" style="text-align: center; padding: 10px;">
    <button onclick="window.print()" class="button-box color-2 bg-4">Print All Report Cards</button>
</div>

<?php
if (isset($_GET['action']) && $_GET['action'] == 'bulk-print' && isset($_GET['class_id']) && isset($_GET['session_id']) && isset($_GET['term_id'])) {
    $school_id = mysqli_real_escape_string($connection_server, trim(strip_tags($_GET['school_id'])));
    $class_id = mysqli_real_escape_string($connection_server, trim(strip_tags($_GET['class_id'])));
    $session_id = mysqli_real_escape_string($connection_server, trim(strip_tags($_GET['session_id'])));
    $term_id_number = mysqli_real_escape_string($connection_server, trim(strip_tags($_GET['term_id'])));

    $students_query = mysqli_query($connection_server, "SELECT admission_number FROM sm_students WHERE school_id_number='$school_id' AND current_class='$class_id' AND session='$session_id'");

    if (mysqli_num_rows($students_query) > 0) {
        while ($student = mysqli_fetch_assoc($students_query)) {
            $admission_number = $student['admission_number'];

            $get_sch_name = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_school_details WHERE school_id_number='$school_id' LIMIT 1"));
            $get_student_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_students WHERE school_id_number='$school_id' && admission_number='$admission_number'"));
            $search_student_to_results_in_database = mysqli_query($connection_server, "SELECT * FROM sm_results WHERE school_id_number='$school_id' && numeric_class_name='$class_id' && session='$session_id' && term_id_number='$term_id_number' && admission_number='$admission_number' ORDER BY subject_code ASC");
            $search_student_to_result_remarks_in_database = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_result_remarks WHERE school_id_number='$school_id' && numeric_class_name='$class_id' && session='$session_id' && term_id_number='$term_id_number' && admission_number='$admission_number' LIMIT 1"));
            $get_term_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='$school_id' && id_number='$term_id_number'"));

            if (mysqli_num_rows($search_student_to_results_in_database) > 0) {
                include 'include/templates/report-card.php';
            }
        }
    } else {
        echo "<p style='text-align:center;'>No students found for the selected class, session, and term.</p>";
    }
} else {
    echo "<p style='text-align:center;'>Please select a class, session, and term to generate report cards.</p>";
}
?>
</body>
</html>
