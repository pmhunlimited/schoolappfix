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
        @page {
            size: A4;
            margin: 0;
        }
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
    <button onclick="printBulkReportCards()" class="button-box color-2 bg-4">Print All Report Cards</button>
</div>

<div id="bulk-printable-area">
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
							<!-- Name -->
							<span style="display: inline-block;" class="color-1 mobile-font-size-17 system-font-size-25"><?php echo $get_sch_name["school_name"]; ?></span><br>
							<span style="display: inline-block;" class="color-1 mobile-font-size-17 system-font-size-25"><?php echo $get_sch_name["school_address"].", ".$get_sch_name["city"]." ".$get_sch_name["state"]; ?></span><br>

							<!-- Title -->
							<span style="display: inline-block;" class="color-1 mobile-font-size-15 system-font-size-18">PROGRESS REPORT <?php echo strtoupper(termName($term_id_number, $school_id))." ".str_replace("-","/",$session_id); ?></span>

						</div>
					</div>
				</div>
				<div style="border:1px solid var(--color-4v);" class="container-box bg-3 mobile-width-96 system-width-96 mobile-margin-top-1 system-margin-top-1 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
					<div style="border:1px solid var(--color-4); text-align: left; display: flex; flex-direction: row;" id="student-detail-container" class="container-box bg-3 mobile-width-100 system-width-90 mobile-margin-top-1 system-margin-top-1 mobile-padding-top-0 system-padding-top-0 mobile-padding-bottom-0 system-padding-bottom-0">
						<div style="display: inline-block;" class="container-box bg-3 mobile-width-75 system-width-80 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-0 system-padding-top-0 mobile-padding-bottom-0 system-padding-bottom-0">
							<div style="display: inline-block; border-width: 0 0 1px 0; border-style: none none solid none; border-color: transparent transparent var(--color-4) transparent; text-align: center;" class="container-box bg-4 mobile-width-25 system-width-25 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Title Name -->
								<span style="display: inline-block;" margin: 0; class="color-2 mobile-font-size-14 system-font-size-16 text-bold-600">Full-Name</span>

							</div>
							<div style="display: inline-block; border-width: 0 1px 1px 0; border-style: none solid solid none; border-color: transparent var(--color-4) var(--color-4) transparent; text-align: ;" class="container-box bg-3 mobile-width-72 system-width-73 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Full-Name -->
								<span style="display: inline-block;" class="color-1 mobile-font-size-14 system-font-size-16"><?php echo strtoupper($get_student_details["lastname"]." ".$get_student_details["firstname"]." ".$get_student_details["othername"]); ?></span>

							</div><br>

							<div style="display: inline-block; border-width: 0 0 1px 0; border-style: none none solid none; border-color: transparent transparent var(--color-4) transparent; text-align: center;" class="container-box bg-4 mobile-width-25 system-width-25 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Title Name -->
								<span style="display: inline-block;" margin: 0; class="color-2 mobile-font-size-14 system-font-size-16 text-bold-600">Student ID</span>

							</div>
							<div style="display: inline-block; border-width: 0 1px 1px 0; border-style: none solid solid none; border-color: transparent var(--color-4) var(--color-4) transparent; text-align: ;" class="container-box bg-3 mobile-width-72 system-width-73 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Matric No -->
								<span style="display: inline-block;" class="color-1 mobile-font-size-14 system-font-size-16"><?php echo "ST/".$school_id."/".$admission_number; ?></span>

							</div><br>

							<div style="display: inline-block; border-width: 0 0 1px 0; border-style: none none solid none; border-color: transparent transparent var(--color-4) transparent; text-align: center;" class="container-box bg-4 mobile-width-25 system-width-25 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Class Name -->
								<span style="display: inline-block;" margin: 0; class="color-2 mobile-font-size-14 system-font-size-16 text-bold-600">Class</span>

							</div>
							<div style="display: inline-block; border-width: 0 1px 1px 0; border-style: none solid solid none; border-color: transparent var(--color-4) var(--color-4) transparent; text-align: ;" class="container-box bg-3 mobile-width-72 system-width-73 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Class-Name -->
								<span style="display: inline-block;" class="color-1 mobile-font-size-14 system-font-size-16"><?php echo ucwords(studentClassName($class_id,$school_id)); ?></span>

							</div><br>

							<div style="display: inline-block; border-width: 0 0 1px 0; border-style: none none solid none; border-color: transparent transparent var(--color-4) transparent; text-align: center;" class="container-box bg-4 mobile-width-25 system-width-25 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Term -->
								<span style="display: inline-block;" margin: 0; class="color-2 mobile-font-size-14 system-font-size-16 text-bold-600">Term</span>

							</div>
							<div style="display: inline-block; border-width: 0 1px 1px 0; border-style: none solid solid none; border-color: transparent var(--color-4) var(--color-4) transparent; text-align: ;" class="container-box bg-3 mobile-width-72 system-width-73 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Term -->
								<span style="display: inline-block;" class="color-1 mobile-font-size-14 system-font-size-16"><?php echo ucwords(termName($term_id_number, $school_id)); ?></span>

							</div>
							<div style="display: inline-block; border-width: 0 0 1px 0; border-style: none none solid none; border-color: transparent transparent var(--color-4) transparent; text-align: center;" class="container-box bg-4 mobile-width-25 system-width-25 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Gender -->
								<span style="display: inline-block;" margin: 0; class="color-2 mobile-font-size-14 system-font-size-16 text-bold-600">Gender</span>

							</div>
							<div style="display: inline-block; border-width: 0 1px 1px 0; border-style: none solid solid none; border-color: transparent var(--color-4) var(--color-4) transparent; text-align: ;" class="container-box bg-3 mobile-width-72 system-width-73 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Gender -->
								<span style="display: inline-block;" class="color-1 mobile-font-size-14 system-font-size-16"><?php echo ucwords($get_student_details["gender"]); ?></span>

							</div>
							<div style="display: inline-block; border-width: 0 0 1px 0; border-style: none none solid none; border-color: transparent transparent var(--color-4) transparent; text-align: center;" class="container-box bg-4 mobile-width-25 system-width-25 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Next Term Begins -->
								<span style="display: inline-block;" margin: 0; class="color-2 mobile-font-size-14 system-font-size-16 text-bold-600">Next Term Begins</span>

							</div>
							<div style="display: inline-block; border-width: 0 1px 1px 0; border-style: none solid solid none; border-color: transparent var(--color-4) var(--color-4) transparent; text-align: ;" class="container-box bg-3 mobile-width-72 system-width-73 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- Next Term Begins -->
								<span style="display: inline-block;" class="color-1 mobile-font-size-14 system-font-size-16"><?php echo $get_term_details["next_term_begins"]; ?></span>

							</div>
							<div style="display: inline-block; border-width: 0 0 1px 0; border-style: none none solid none; border-color: transparent transparent var(--color-4) transparent; text-align: center;" class="container-box bg-4 mobile-width-25 system-width-25 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- No of Days School Open -->
								<span style="display: inline-block;" margin: 0; class="color-2 mobile-font-size-14 system-font-size-16 text-bold-600">No of Days School Open</span>

							</div>
							<div style="display: inline-block; border-width: 0 1px 1px 0; border-style: none solid solid none; border-color: transparent var(--color-4) var(--color-4) transparent; text-align: ;" class="container-box bg-3 mobile-width-72 system-width-73 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
								<!-- No of Days School Open -->
								<span style="display: inline-block;" class="color-1 mobile-font-size-14 system-font-size-16"><?php echo $get_term_details["school_open_days"]; ?></span>

							</div>

						</div>

						<div style="display: inline-block; text-align: center;" id="student-passport-container" class="container-box bg-3 mobile-width-24 system-width-19 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-0 system-padding-top-0 mobile-padding-bottom-0 system-padding-bottom-0">
							<?php if(file_exists("dataimg/student_".$school_id."_".$admission_number.".png")){ ?>
							<img style="display: inline-block; object-fit: cover; height: 0px; margin: 0; padding: 0;" id="student-passport" class="mobile-margin-top-0 system-margin-top-0" src="dataimg/student_<?php echo $school_id.'_'.$admission_number; ?>.png" /><br>
							<?php }else{ ?>
							<img style="display: inline-block; object-fit: cover; height: 0px; margin: 0; padding: 0;" id="student-passport" class="mobile-margin-top-0 system-margin-top-0" src="imgfile/Student.png" /><br>
							<?php } ?>
						</div>
					</div>
				</div>
				<div style="border:1px solid var(--color-4v);" class="container-box bg-3 mobile-width-96 system-width-96 mobile-margin-top-1 system-margin-top-1 mobile-padding-top-1 system-padding-top-1 mobile-padding-bottom-1 system-padding-bottom-1">
					<div style="display: inline-block;" class="container-box bg-3 mobile-width-100 system-width-90 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-0 system-padding-top-0 mobile-padding-bottom-0 system-padding-bottom-0">
						<div class="scroll-box bg-2 mobile-width-100 system-width-100">
							<table class="result-table-tag mobile-font-size-12 system-font-size-14">
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

								if(mysqli_num_rows($search_student_to_results_in_database) > 0){
                                    mysqli_data_seek($search_student_to_results_in_database, 0);
									while($student_exam_subject_details = mysqli_fetch_array($search_student_to_results_in_database)){
										if($student_exam_subject_details["first_ca"] == ""){
											$first_ca = "-";
											$first_ca_mark = 0;
										}else{
											if($student_exam_subject_details["first_ca"] > 0){
												$first_ca = $student_exam_subject_details["first_ca"];
												$first_ca_mark = $student_exam_subject_details["first_ca"];
											}else{
												$first_ca = 0;
												$first_ca_mark = 0;
											}
										}

										if($student_exam_subject_details["second_ca"] == ""){
											$second_ca = "-";
											$second_ca_mark = 0;
										}else{
											if($student_exam_subject_details["second_ca"] > 0){
												$second_ca = $student_exam_subject_details["second_ca"];
												$second_ca_mark = $student_exam_subject_details["second_ca"];
											}else{
												$second_ca = 0;
												$second_ca_mark = 0;
											}
										}

										if($student_exam_subject_details["third_ca"] == ""){
											$third_ca = "-";
											$third_ca_mark = 0;
										}else{
											if($student_exam_subject_details["third_ca"] > 0){
												$third_ca = $student_exam_subject_details["third_ca"];
												$third_ca_mark = $student_exam_subject_details["third_ca"];
											}else{
												$third_ca = 0;
												$third_ca_mark = 0;
											}
										}

										if($student_exam_subject_details["exam"] == ""){
											$exam_mark = "-";
											$examination_mark = 0;
										}else{
											if($student_exam_subject_details["exam"] > 0){
												$exam_mark = $student_exam_subject_details["exam"];
												$examination_mark = $student_exam_subject_details["exam"];
											}else{
												$exam_mark = 0;
												$examination_mark = 0;
											}
										}

										if(($first_ca_mark+$second_ca_mark+$third_ca_mark+$examination_mark) === 0){
											$aggregate_score = 0;
										}else{
											$aggregate_score = $first_ca_mark+$second_ca_mark+$third_ca_mark+$examination_mark;
										}

										if($aggregate_score > 0){
											echo
											'<tr>
												<td>'.subjectName($student_exam_subject_details["subject_code"], $school_id).'</td>
												<td>'.$first_ca.'</td>
												<td>'.$second_ca.'</td>
												<td>'.$third_ca.'</td>
												<td>'.$exam_mark.'</td>
												<td>'.($aggregate_score).'</td>
												<td>100</td>
												<td>'.getScoreGrade($aggregate_score,'grade',$school_id).'</td>
												<td>'.getScoreGrade($aggregate_score,'remark',$school_id).'</td>
											</tr>';

										$subject_count += 1;
										$mark_obtained_count += $aggregate_score;
										$mark_obtainable_count += 100;
										}
									}
								}
							?>

							</table>
						</div>

					</div>

				</div>
				<div style="display: inline-block; text-align: left;" class="container-box bg-3 mobile-width-90 system-width-80 mobile-margin-top-0 system-margin-top-0 mobile-padding-top-0 system-padding-top-0 mobile-padding-bottom-0 system-padding-bottom-0">
					<!-- No of Subjects -->
					<span style="display: inline-block;" class="color-1 mobile-font-size-12 system-font-size-14 mobile-margin-top-2 system-margin-top-2 mobile-margin-right-30 system-margin-right-5">
						<strong>No. of Subject</strong>: <?php echo $subject_count; ?>
					</span>
					<?php
						$average_mark_obtained = ($mark_obtainable_count > 0) ? substr((($mark_obtained_count / $mark_obtainable_count) * 100),0,5) : 0;
					?>
					<!-- Average Mark -->
					<span style="display: inline-block;" class="color-1 mobile-font-size-12 system-font-size-14 mobile-margin-right-0 system-margin-right-5">
						<strong>Average Mark (%)</strong>: <?php echo $average_mark_obtained."%"; ?>
					</span>

					<!-- Principal Remark -->
					<span style="display: inline-block;" class="color-1 mobile-font-size-12 system-font-size-14 mobile-margin-top-2 system-margin-top-2">
						<strong>Principal Remark</strong>:
						<?php
							if(!empty(trim($search_student_to_result_remarks_in_database["principal_remark"]))){
								echo trim($search_student_to_result_remarks_in_database["principal_remark"]);
							}else{
								echo principalRemark($get_student_details["gender"],explode(".",trim($average_mark_obtained))[0]);
							}
						?>
					</span>

					<!-- Teacher's Remark -->
					<span style="display: inline-block;" class="color-1 mobile-font-size-12 system-font-size-14 mobile-margin-top-2 system-margin-top-2">
						<strong>Teacher's Remark</strong>:
						<?php
							if(!empty(trim($search_student_to_result_remarks_in_database["teacher_remark"]))){
								echo trim($search_student_to_result_remarks_in_database["teacher_remark"]);
							}
						?>
					</span>
				</div>
			</div>

		</center>
	</div>
</div>
<?php
            }
        }
    } else {
        echo "<p style='text-align:center;'>No students found for the selected class, session, and term.</p>";
    }
} else {
    echo "<p style='text-align:center;'>Please select a class, session, and term to generate report cards.</p>";
}
?>
</div>
<script>
    function printBulkReportCards() {
        var printContents = document.getElementById('bulk-printable-area').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }
</script>
</body>
</html>
