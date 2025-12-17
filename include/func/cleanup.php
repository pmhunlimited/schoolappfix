<?php
if (isset($_POST["clean-orphaned-subjects"])) {
    $school_id = $get_logged_user_details["school_id_number"];

    // Get all subject codes from the results table for the current school
    $results_subjects_query = mysqli_query($connection_server, "SELECT DISTINCT subject_code FROM sm_results WHERE school_id_number='$school_id'");
    $results_subjects = [];
    while ($row = mysqli_fetch_assoc($results_subjects_query)) {
        $results_subjects[] = $row['subject_code'];
    }

    // Get all existing subject codes for the current school
    $subjects_query = mysqli_query($connection_server, "SELECT subject_code FROM sm_subjects WHERE school_id_number='$school_id'");
    $existing_subjects = [];
    while ($row = mysqli_fetch_assoc($subjects_query)) {
        $existing_subjects[] = $row['subject_code'];
    }

    // Find the orphaned subject codes
    $orphaned_subjects = array_diff($results_subjects, $existing_subjects);

    if (!empty($orphaned_subjects)) {
        $orphaned_subjects_str = "'" . implode("','", $orphaned_subjects) . "'";
        $delete_query = "DELETE FROM sm_results WHERE school_id_number='$school_id' AND subject_code IN ($orphaned_subjects_str)";

        if (mysqli_query($connection_server, $delete_query)) {
            $err_msg = "Successfully deleted " . mysqli_affected_rows($connection_server) . " orphaned subject records.";
        } else {
            $err_msg = "Error deleting orphaned records: " . mysqli_error($connection_server);
        }
    } else {
        $err_msg = "No orphaned subject records found.";
    }

    // Redirect back to the cleanup page with a message
    header("Location: /bc-admin.php?page=smgt_cleanup&tab=true&id=$school_id&err_msg=" . urlencode($err_msg));
    exit();
}
?>