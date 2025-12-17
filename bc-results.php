<?php session_start(); error_reporting(0);
	
	include("include/config-file.php");
	include("include/func/helpers.php");
	
	function checkPayment($student_info, $class_info, $session_info, $school_id){
		global $connection_server;
		$feeTypeArray = array();
		$select_fee_list = mysqli_query($connection_server, "SELECT * FROM sm_fee_lists WHERE school_id_number='$school_id' && numeric_class_name='$class_info' && session='$session_info'");
		
		if(mysqli_num_rows($select_fee_list) > 0){
			while($fee_list_details = mysqli_fetch_assoc($select_fee_list)){
				$feeTypeArray[] = $fee_list_details['fee_type_id'];
			}
		}
		
		if(count($feeTypeArray) > 0){
			foreach($feeTypeArray as $fee_type_id){
				$feeTypeId .= "fee_type_id='".$fee_type_id."' ";
			}
			$fee_type_id_statement = "(".str_replace(" ", " OR ", trim($feeTypeId)).")";
			
			$search_fee_payment_list = mysqli_query($connection_server, "SELECT * FROM sm_fee_payment_lists WHERE school_id_number='$school_id' && ".$fee_type_id_statement." && admission_number='$student_info' && numeric_class_name='$class_info' && session='$session_info'");
			
			if(count($feeTypeArray) === mysqli_num_rows($search_fee_payment_list)){
				$fee_payment_validation = true;
				$fee_bill_overdue = array();
			}else{
				if(count($feeTypeArray) > mysqli_num_rows($search_fee_payment_list)){
					$fee_payment_validation = false;
					$feeTypeArrayAltered = array();
					if(mysqli_num_rows($search_fee_payment_list) > 0){
						foreach($feeTypeArray as $feeTypeIds){
							$search_fee_payment_list_check = mysqli_query($connection_server, "SELECT * FROM sm_fee_payment_lists WHERE school_id_number='$school_id' && fee_type_id='$feeTypeIds' && admission_number='$student_info' && numeric_class_name='$class_info' && session='$session_info'");
							if(mysqli_num_rows($search_fee_payment_list_check) == 0){
								$feeTypeArrayAltered[] = $feeTypeIds;
							}
						}
					}else{
						$feeTypeArrayAltered = $feeTypeArray;
					}
					$fee_bill_overdue = $feeTypeArrayAltered;
				}
			}
	    
	    }else{
	    	$fee_payment_validation = true;
	    	$fee_bill_overdue = array();
	    }
	    
	    return 	json_encode(array("fee_status"=>$fee_payment_validation, "fee_overdue"=>$fee_bill_overdue),true);
	}

	if(isset($_GET["view"]) && (!empty(trim(strip_tags($_GET["view"]))))){
		$result_unique_id = mysqli_real_escape_string($connection_server, trim(strip_tags($_GET["view"])));
		$search_student_to_result_list_database = mysqli_query($connection_server, "SELECT * FROM sm_result_lists WHERE result_ref='$result_unique_id'");
		if(mysqli_num_rows($search_student_to_result_list_database) == 1){
			$get_student_result_details = mysqli_fetch_array($search_student_to_result_list_database);
			$numeric_class = mysqli_real_escape_string($connection_server, trim(strip_tags($get_student_result_details["numeric_class_name"])));
			$session = mysqli_real_escape_string($connection_server, trim(strip_tags($get_student_result_details["session"])));
			$term_id_number = mysqli_real_escape_string($connection_server, trim(strip_tags($get_student_result_details["term_id_number"])));
			$admission_number = mysqli_real_escape_string($connection_server, trim(strip_tags($get_student_result_details["admission_number"])));
			$school_id = mysqli_real_escape_string($connection_server, trim(strip_tags($get_student_result_details["school_id_number"])));
			
			$get_sch_name = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_school_details WHERE school_id_number='$school_id' LIMIT 1"));
			
			$get_student_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_students WHERE school_id_number='$school_id' && admission_number='$admission_number'"));
			$search_student_to_results_in_database = mysqli_query($connection_server, "SELECT * FROM sm_results WHERE school_id_number='$school_id' && numeric_class_name='$numeric_class' && session='$session' && term_id_number='$term_id_number' && admission_number='$admission_number' ORDER BY subject_code ASC");
			$search_student_to_result_remarks_in_database = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_result_remarks WHERE school_id_number='$school_id' && numeric_class_name='$numeric_class' && session='$session' && term_id_number='$term_id_number' && admission_number='$admission_number' LIMIT 1"));
			$get_result_release_dates = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_result_release_dates WHERE school_id_number='$school_id' && numeric_class_name='$numeric_class' && term_id_number='$term_id_number' && session='$session'"));
			$get_term_details = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_terms WHERE school_id_number='$school_id' && id_number='$term_id_number'"));
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
<title></title>
<meta charset="UTF-8" />
<meta name="description" content="" />
<meta http-equiv="Content-Type" content="text/html; " />
<meta name="theme-color" content="black" />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="stylesheet" href="cssfile/font-family.css">
<link rel="stylesheet" href="cssfile/portal.css">
<script src="js/popup.js"></script>

</head>
<body>

<?php
$result_release_date = new DateTime($get_result_release_dates["release_date"]);
$today_date = new DateTime();
	if(mysqli_num_rows($search_student_to_results_in_database) > 0){
		if((json_decode(checkPayment($admission_number, $numeric_class, $session, $school_id),true)["fee_status"] == true) || (1 == 1)){
?>

<?php include 'include/templates/report-card.php'; ?>

	<center>
		<span style="display: inline-block; text-decoration: underline; cursor: pointer;" class="color-4 mobile-font-size-14 system-font-size-16 noprint" onclick="printPage();">Print Result</span>
						
		<script>
			
			function printPage(){
	
				var receiptDiv = document.getElementById("printDiv").innerHTML;
				const html = [];
				html.push('<html><head>');
				html.push('<link rel="stylesheet" href="cssfile/portal.css">');
				html.push('</head><body onload="window.focus(); window.print()"><div>');
				html.push(receiptDiv);
				html.push('</div></body></html>');
				
				var mywindow = window.open('', '', 'width=640,height=480');
				mywindow.document.open("text/html");
				mywindow.document.write(html.join(""));
				mywindow.document.close();
				
			}

			window.addEventListener('keydown', function(e){
				if(e.ctrlKey && e.keyCode == 80){
					e.preventDefault();
					printPage();
				}
			});
		</script>
		
<?php
		}else{
		$outstandingFeeTypeArr = json_decode(checkPayment($admission_number, $numeric_class, $session, $school_id),true)["fee_overdue"];
?>
			<center>
				<div style="border:1px solid var(--color-4); " class="container-box bg-2 mobile-width-96 system-width-70 mobile-margin-top-1 system-margin-top-1 mobile-padding-top-2 system-padding-top-2 mobile-padding-bottom-2 system-padding-bottom-2">
					<span class="mobile-font-size-30 system-font-size-40 text-bold-700">Action Required: Some bills remain unpaid. Please address this promptly.</span><br>
					<span class="mobile-font-size-18 system-font-size-25 text-bold-700">Pay all Outstanding Fees below:</span><br>
					<div style="text-align: left; " class="container-box bg-2 mobile-width-96 system-width-70">
					<span class="m-font-size-16 s-font-size-20 text-bold-700">
						<ol>
						<?php
							foreach($outstandingFeeTypeArr as $feeTypeId){
								$feeTypeName = mysqli_fetch_array(mysqli_query($connection_server, "SELECT * FROM sm_fee_type WHERE school_id_number='$school_id' && id_number='$feeTypeId' LIMIT 1"));
								echo '<li class="mobile-margin-top-1">'.$feeTypeName["fee_name"].'</li>';
							}
						?>
						</ol>
					</span>
					</div>
					<span class="mobile-font-size-14 system-font-size-20 text-bold-700">Pay all the above fee to view result</span><br>
				</div>
			</center>
<?php
		}
	}
?>
</body>
</html>
