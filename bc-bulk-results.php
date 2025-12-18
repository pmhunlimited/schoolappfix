<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("include/config-file.php");
include("include/func/helpers.php");

if (isset($_POST['print_bulk_reports'])) {
    if(isset($_SESSION["mod_adm_session"])){
        $get_logged_user_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_moderators WHERE school_id_number='".$_SESSION["mod_adm_session"]."'"));
    }else if(isset($_SESSION["adm_staff_session"])){
        $get_logged_user_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_admin_staffs WHERE school_id_number='".$_SESSION["school_id"]."' && id_number='".$_SESSION["adm_staff_session"]."'"));
    }else if(isset($_SESSION["teacher_session"])){
        $get_logged_user_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_teachers WHERE school_id_number='".$_SESSION["school_id"]."' && id_number='".$_SESSION["teacher_session"]."'"));
    } else {
        die("Unauthorized access.");
    }

    $class_id = $_POST['class'];
    $session = $_POST['session'];
    $school_id = $get_logged_user_details['school_id_number'];

    // Sanitize inputs
    $class_id = mysqli_real_escape_string($connection_server, $class_id);
    $session = mysqli_real_escape_string($connection_server, $session);
    $school_id = mysqli_real_escape_string($connection_server, $school_id);

    $students_query = "SELECT * FROM sm_students WHERE school_id_number = '$school_id' AND current_class = '$class_id' AND session = '$session'";
    $students_result = mysqli_query($connection_server, $students_query);

    if (!$students_result) {
        die("Error fetching students: " . mysqli_error($connection_server));
    }

    $get_sch_name = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_school_details WHERE school_id_number='$school_id' LIMIT 1"));
    $term_id_number = mysqli_fetch_array(mysqli_query($connection_server, "SELECT term_id_number FROM sm_results WHERE school_id_number='$school_id' AND numeric_class_name='$class_id' AND session='$session' LIMIT 1"))['term_id_number'];
    $get_term_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='$school_id' AND id_number='$term_id_number'"));

    while ($student = mysqli_fetch_assoc($students_result)) {
        $admission_number = $student['admission_number'];
        $search_student_to_results_in_database = mysqli_query($connection_server, "SELECT * FROM sm_results WHERE school_id_number='$school_id' AND numeric_class_name='$class_id' AND session='$session' AND admission_number='$admission_number' ORDER BY subject_code ASC");
        $search_student_to_result_remarks_in_database = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_result_remarks WHERE school_id_number='$school_id' AND numeric_class_name='$class_id' AND session='$session' AND admission_number='$admission_number' LIMIT 1"));

        // Report card HTML starts here
        ?>
        <div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
            <center>
                <div style="border:1px solid var(--color-4); " class="container-box bg-2 mobile-width-96 system-width-70 mobile-margin-top-1 system-margin-top-1 mobile-padding-top-2 system-padding-top-2 mobile-padding-bottom-2 system-padding-bottom-2">
                    <p style="font-size: 24px; font-weight: bold;"><?php echo $get_sch_name['school_name']; ?></p>
                    <p>Report Card for <?php echo $student['firstname'] . ' ' . $student['lastname']; ?></p>
                    <table class="result-table-tag">
                        <tr>
                            <th>Subject</th>
                            <th>1st C.A</th>
                            <th>2nd C.A</th>
                            <th>3rd C.A</th>
                            <th>Exam</th>
                            <th>Total Mark</th>
                            <th>Mark Obtainable</th>
                            <th>Grade</th>
                            <th>Remark</th>
                        </tr>
                        <?php
                        $subject_count = 0;
                        $mark_obtained_count = 0;
                        $mark_obtainable_count = 0;

                        while ($result = mysqli_fetch_assoc($search_student_to_results_in_database)) {
                            $total_mark = $result['first_ca'] + $result['second_ca'] + $result['third_ca'] + $result['exam'];
                            $subject_count++;
                            $mark_obtained_count += $total_mark;
                            $mark_obtainable_count += 100;
                            ?>
                            <tr>
                                <td><?php echo subjectName($result['subject_code'], $school_id); ?></td>
                                <td><?php echo $result['first_ca']; ?></td>
                                <td><?php echo $result['second_ca']; ?></td>
                                <td><?php echo $result['third_ca']; ?></td>
                                <td><?php echo $result['exam']; ?></td>
                                <td><?php echo $total_mark; ?></td>
                                <td>100</td>
                                <td><?php echo getScoreGrade($total_mark, 'grade', $school_id); ?></td>
                                <td><?php echo getScoreGrade($total_mark, 'remark', $school_id); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                    <p><strong>Average Mark:</strong> <?php echo ($mark_obtainable_count > 0) ? round(($mark_obtained_count / $mark_obtainable_count) * 100, 2) : 0; ?>%</p>
                    <p><strong>Principal's Remark:</strong> <?php echo principalRemark($student['gender'], ($mark_obtainable_count > 0) ? ($mark_obtained_count / $mark_obtainable_count) * 100 : 0); ?></p>
                </div>
            </center>
        </div>
        <?php
        // Report card HTML ends here
    }
}
?>
