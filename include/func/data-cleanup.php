<div class="row">
    <div class="col-md-12">
        <h2>Data Cleanup</h2>
        <form method="post" action="">
            <p>This tool will remove all students who are not in the current session. This action cannot be undone.</p>
            <button type="submit" name="cleanup_data" class="btn btn-danger">Clean Up Data</button>
        </form>
    </div>
</div>

<?php
if (isset($_POST['cleanup_data'])) {
    $current_session_query = mysqli_query($connection_server, "SELECT session FROM sm_sessions WHERE school_id_number='" . $get_logged_user_details['school_id_number'] . "' ORDER BY session DESC LIMIT 1");
    $current_session_row = mysqli_fetch_array($current_session_query);
    $current_session = $current_session_row['session'];

    $delete_query = "DELETE FROM sm_students WHERE school_id_number='" . $get_logged_user_details['school_id_number'] . "' AND session != '$current_session'";
    if (mysqli_query($connection_server, $delete_query)) {
        echo "<div class='alert alert-success'>Data cleanup successful.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error cleaning up data: " . mysqli_error($connection_server) . "</div>";
    }
}
?>
