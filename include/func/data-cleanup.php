<div class="row">
    <div class="col-md-12">
        <h2>Data Cleanup</h2>
        <p>This tool helps you remove orphaned data from your database. Please use with caution, as this action cannot be undone.</p>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Orphaned Students</h3>
            </div>
            <div class="panel-body">
                <p>This will remove all students whose assigned class no longer exists.</p>
                <form method="post" action="">
                    <button type="submit" name="cleanup_students" class="btn btn-danger">Remove Orphaned Students</button>
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Orphaned Subject Scores</h3>
            </div>
            <div class="panel-body">
                <p>This will remove all subject scores for subjects that no longer exist.</p>
                <form method="post" action="">
                    <button type="submit" name="cleanup_subjects" class="btn btn-danger">Remove Orphaned Subject Scores</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_POST['cleanup_students'])) {
    $school_id = $get_logged_user_details['school_id_number'];
    $delete_query = "DELETE FROM sm_students WHERE school_id_number = '$school_id' AND current_class NOT IN (SELECT numeric_class_name FROM sm_classes WHERE school_id_number = '$school_id')";
    if (mysqli_query($connection_server, $delete_query)) {
        echo "<div class='alert alert-success'>Orphaned students removed successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error removing orphaned students: " . mysqli_error($connection_server) . "</div>";
    }
}

if (isset($_POST['cleanup_subjects'])) {
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
            $affected_rows = mysqli_affected_rows($connection_server);
            echo "<div class='alert alert-success'>Successfully deleted " . $affected_rows . " orphaned subject records.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting orphaned records: " . mysqli_error($connection_server) . "</div>";
        }
    } else {
        echo "<div class='alert alert-info'>No orphaned subject records found.</div>";
    }
}
?>
