<?php
$school_id = $get_logged_user_details['school_id_number'];
$feature_name = 'data_cleanup';

// Check if the feature is activated for the school
if (!is_feature_active($school_id, $feature_name, $connection_server)) {
    header("Location: /bc-admin.php?page=smgt_request_activation&feature=" . $feature_name);
    exit();
}

if (isset($_POST["clean-orphaned-subjects"])) {
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
        $orphaned_subjects_str = "'" . implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($orphaned_subjects), $connection_server), $orphaned_subjects)) . "'";
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

$status = $_GET['status'] ?? '';
$status_msg = $_GET['status_msg'] ?? '';
?>

<div class="bc_heading">
    <div class="bc_heading_text">Data Cleanup</div>
</div>
<div class="bc_container">
    <div class="container-box bg-3 mobile-width-90 system-width-50" style="margin: auto; padding: 20px;">
        <h2 class="color-4">Orphaned Subject Score Cleanup</h2>
        <p class="color-5" style="margin-bottom: 20px;">
            This tool will find and delete any subject scores from the results table that do not correspond to a currently existing subject in your school. This can happen if a subject is deleted after scores have been entered.
        </p>

        <?php if ($status_msg): ?>
        <div class="bc-form-status-msg <?php echo $status === 'success' ? 'bc-form-success-msg' : ($status === 'error' ? 'bc-form-error-msg' : ''); ?>" style="display: block !important;">
            <?php echo htmlspecialchars($status_msg); ?>
        </div>
        <?php endif; ?>

        <form method="post" onsubmit="return confirm('Are you sure you want to permanently delete all orphaned subject scores? This action cannot be undone.');">
            <button name="clean-orphaned-subjects" type="submit" class="bc-btn-del" style="width: 100%;">
                Delete Orphaned Subject Scores
            </button>
        </form>
    </div>
</div>
