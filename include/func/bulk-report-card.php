<div class="row">
    <div class="col-md-12">
        <h2>Bulk Report Card Printing</h2>
        <form method="post" action="/bc-bulk-results.php" target="_blank">
            <div class="form-group">
                <label for="class">Select Class</label>
                <select name="class" id="class" class="form-control">
                    <?php
                    $classes_query = mysqli_query($connection_server, "SELECT * FROM sm_classes WHERE school_id_number='" . $get_logged_user_details['school_id_number'] . "'");
                    while ($class = mysqli_fetch_array($classes_query)) {
                        echo "<option value='" . $class['numeric_class_name'] . "'>" . $class['class_name'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="session">Select Session</label>
                <select name="session" id="session" class="form-control">
                    <?php
                    $sessions_query = mysqli_query($connection_server, "SELECT * FROM sm_sessions WHERE school_id_number='" . $get_logged_user_details['school_id_number'] . "'");
                    while ($session = mysqli_fetch_array($sessions_query)) {
                        echo "<option value='" . $session['session'] . "'>" . $session['session'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="print_bulk_reports" class="btn btn-primary">Print Report Cards</button>
        </form>
    </div>
</div>
