<?php
if (isset($_POST["clean-orphaned-subjects"])) {
    $school_id = $get_logged_user_details["school_id_number"];
    $status = 'info';
    $status_msg = 'No orphaned subject records found.';

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
            $affected_rows = mysqli_affected_rows($connection_server);
            if ($affected_rows > 0) {
                $status = 'success';
                $status_msg = "Successfully deleted " . $affected_rows . " orphaned subject records.";
            }
        } else {
            $status = 'error';
            $status_msg = "Error deleting orphaned records: " . mysqli_error($connection_server);
        }
    }

    // Redirect back to the cleanup page with a message
    header("Location: /bc-admin.php?page=smgt_cleanup&status=$status&status_msg=" . urlencode($status_msg));
    exit();
}
?>