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
        .report-card-wrapper {
            border: 2px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="noprint" style="text-align: center; padding: 10px;">
    <button onclick="window.print()" class="button-box color-2 bg-4">Print All Report Cards</button>
</div>

<?php
if (isset($_POST['bulk-print-btn']) && isset($_POST['class_id']) && isset($_POST['session_id'])) {
    $school_id = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST['school_id'])));
    $class_id = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST['class_id'])));
    $session_id = mysqli_real_escape_string($connection_server, trim(strip_tags($_POST['session_id'])));

    // Fetch all students in the selected class and session
    $students_query = mysqli_query($connection_server, "SELECT admission_number FROM sm_students WHERE school_id_number='$school_id' AND current_class='$class_id' AND session='$session_id'");

    if (mysqli_num_rows($students_query) > 0) {
        while ($student = mysqli_fetch_assoc($students_query)) {
            $admission_number = $student['admission_number'];

            // Fetch latest term for the session
            $term_query = mysqli_query($connection_server, "SELECT term_id_number FROM sm_results WHERE school_id_number='$school_id' AND admission_number='$admission_number' AND numeric_class_name='$class_id' AND session='$session_id' ORDER BY term_id_number DESC LIMIT 1");
            if(mysqli_num_rows($term_query) > 0) {
                $term_data = mysqli_fetch_assoc($term_query);
                $term_id_number = $term_data['term_id_number'];

                // Now fetch all the data for this student's report card
                $get_sch_name = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_school_details WHERE school_id_number='$school_id' LIMIT 1"));
                $get_student_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_students WHERE school_id_number='$school_id' && admission_number='$admission_number'"));
                $search_student_to_results_in_database = mysqli_query($connection_server, "SELECT * FROM sm_results WHERE school_id_number='$school_id' && numeric_class_name='$class_id' && session='$session_id' && term_id_number='$term_id_number' && admission_number='$admission_number' ORDER BY subject_code ASC");
                $search_student_to_result_remarks_in_database = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_result_remarks WHERE school_id_number='$school_id' && numeric_class_name='$class_id' && session='$session_id' && term_id_number='$term_id_number' && admission_number='$admission_number' LIMIT 1"));
                $get_term_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='$school_id' && id_number='$term_id_number'"));

                if(mysqli_num_rows($search_student_to_results_in_database) > 0) {
                    // Render the report card HTML (copied and adapted from bc-results.php)
                    ?>
                    <div class="page-break">
                        <div id="printDiv" class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
                            <center>
                                <div style="border:1px solid var(--color-4); " class="container-box bg-2 mobile-width-96 system-width-70 mobile-margin-top-1 system-margin-top-1 mobile-padding-top-2 system-padding-top-2 mobile-padding-bottom-2 system-padding-bottom-2">
                                    <div style="border:1px solid var(--color-4v); text-align: left;" class="container-box bg-3 mobile-width-96 system-width-96 mobile-margin-top-1 system-margin-top-1 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
                                        <div style="display: flex; flex-direction: row; align-items: center;" class="container-box bg-3 mobile-width-100 system-width-100">
                                            <div style="text-align: left;" class="container-box bg-3 mobile-width-30 system-width-30">
                                                <?php if(file_exists("dataimg/school_".$school_id.".png")){ ?>
                                                <img style="display: inline-block;" class="mobile-width-100 system-width-100" src="dataimg/school_<?php echo $school_id; ?>.png" />
                                                <?php }else{ ?>
                                                <img style="display: inline-block;" class="mobile-width-100 system-width-100" src="imgfile/logo.png" />
                                                <?php } ?>
                                            </div>
                                            <div style="display: inline-block; text-align: center;" class="container-box bg-3 mobile-width-70 system-width-70">
                                                <span style="display: inline-block;" class="color-1 mobile-font-size-17 system-font-size-25"><?php echo $get_sch_name["school_name"]; ?></span><br>
                                                <span style="display: inline-block;" class="color-1 mobile-font-size-17 system-font-size-25"><?php echo $get_sch_name["school_address"].", ".$get_sch_name["city"]." ".$get_sch_name["state"]; ?></span><br>
                                                <span style="display: inline-block;" class="color-1 mobile-font-size-15 system-font-size-18">PROGRESS REPORT <?php echo strtoupper(termName($term_id_number, $school_id))." ".str_replace("-","/",$session_id); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Student Details & Results Table -->
                                    <div style="border:1px solid var(--color-4v);" class="container-box bg-3 mobile-width-96 system-width-96 mobile-margin-top-1 system-margin-top-1 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
                                        <p><strong>Full Name:</strong> <?php echo strtoupper($get_student_details["lastname"]." ".$get_student_details["firstname"]." ".$get_student_details["othername"]); ?></p>
                                        <p><strong>Student ID:</strong> <?php echo "ST/".$school_id."/".$admission_number; ?></p>
                                        <p><strong>Class:</strong> <?php echo ucwords(studentClassName($class_id,$school_id)); ?></p>

                                        <table class="result-table-tag mobile-font-size-12 system-font-size-14" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>1st C.A</th>
                                                    <th>2nd C.A</th>
                                                    <th>3rd C.A</th>
                                                    <th>Exam</th>
                                                    <th>Total</th>
                                                    <th>Grade</th>
                                                    <th>Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                $subject_count = 0;
                                                $mark_obtained_count = 0;
                                                $mark_obtainable_count = 0;

                                                mysqli_data_seek($search_student_to_results_in_database, 0); // Reset pointer
                                                while($student_exam_subject_details = mysqli_fetch_array($search_student_to_results_in_database)){
                                                    $aggregate_score = $student_exam_subject_details["first_ca"] + $student_exam_subject_details["second_ca"] + $student_exam_subject_details["third_ca"] + $student_exam_subject_details["exam"];
                                                    if($aggregate_score > 0) {
                                                        echo '<tr>
                                                            <td>'.subjectName($student_exam_subject_details["subject_code"], $school_id).'</td>
                                                            <td>'.$student_exam_subject_details["first_ca"].'</td>
                                                            <td>'.$student_exam_subject_details["second_ca"].'</td>
                                                            <td>'.$student_exam_subject_details["third_ca"].'</td>
                                                            <td>'.$student_exam_subject_details["exam"].'</td>
                                                            <td>'.$aggregate_score.'</td>
                                                            <td>'.getScoreGrade($aggregate_score,'grade',$school_id).'</td>
                                                            <td>'.getScoreGrade($aggregate_score,'remark',$school_id).'</td>
                                                        </tr>';
                                                        $subject_count++;
                                                        $mark_obtained_count += $aggregate_score;
                                                        $mark_obtainable_count += 100; // Assuming each subject is out of 100
                                                    }
                                                }
                                            ?>
                                            </tbody>
                                        </table>

                                        <?php $average_mark_obtained = ($mark_obtainable_count > 0) ? substr((($mark_obtained_count / $mark_obtainable_count) * 100),0,5) : 0; ?>
                                        <p><strong>Average Mark:</strong> <?php echo $average_mark_obtained . "%"; ?></p>
                                        <p><strong>Principal's Remark:</strong> <?php echo !empty(trim($search_student_to_result_remarks_in_database["principal_remark"])) ? trim($search_student_to_result_remarks_in_database["principal_remark"]) : principalRemark($get_student_details["gender"], $average_mark_obtained); ?></p>
                                        <p><strong>Teacher's Remark:</strong> <?php echo !empty(trim($search_student_to_result_remarks_in_database["teacher_remark"])) ? trim($search_student_to_result_remarks_in_database["teacher_remark"]) : ''; ?></p>
                                    </div>

                                </div>
                            </center>
                        </div>
                    </div>
                    <?php
                }
            }
        }
    } else {
        echo "<p style='text-align:center;'>No students found for the selected class and session.</p>";
    }
} else {
    echo "<p style='text-align:center;'>Please select a class and session to generate report cards.</p>";
}
?>
</body>
</html>
