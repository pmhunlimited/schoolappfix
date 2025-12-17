<?php
if (in_array($user_identifier_auth_id, ["mod_adm", "adm_staff", "teacher"])) {
?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="container-box bg-3 mobile-width-90 system-width-50 mobile-margin-top-2 system-margin-top-2 mobile-padding-top-2 mobile-padding-bottom-2">
            <h2 class="color-4">Bulk Print Report Cards</h2>
            <p class="color-5">Select a class, session, and term to print all report cards for that group.</p>

            <form method="get" action="/bc-bulk-results.php" target="_blank">
                <input type="hidden" name="school_id" value="<?php echo $get_logged_user_details['school_id_number']; ?>">
                <input type="hidden" name="action" value="bulk-print">


                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2 mobile-margin-left-2 system-margin-left-2 mobile-margin-right-2 system-margin-right-2">
                    <select name="class_id" class="form-select" required>
                        <option selected disabled hidden value="">Select Class</option>
                        <?php
                        $school_id = $get_logged_user_details['school_id_number'];
                        $query = "SELECT numeric_class_name, class_name FROM sm_classes WHERE school_id_number='$school_id' GROUP BY numeric_class_name, class_name ORDER BY class_name";
                        $result = mysqli_query($connection_server, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="' . $row['numeric_class_name'] . '">' . $row['class_name'] . '</option>';
                        }
                        ?>
                    </select>
                    <span class="form-span mobile-font-size-12 system-font-size-14">Class*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2 mobile-margin-left-2 system-margin-left-2 mobile-margin-right-2 system-margin-right-2">
                    <select name="session_id" class="form-select" required>
                        <option selected disabled hidden value="">Select Session</option>
                        <?php
                        $query = "SELECT session FROM sm_sessions WHERE school_id_number='$school_id' ORDER BY session DESC";
                        $result = mysqli_query($connection_server, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="' . $row['session'] . '">' . str_replace('-', '/', $row['session']) . '</option>';
                        }
                        ?>
                    </select>
                    <span class="form-span mobile-font-size-12 system-font-size-14">Session*</span>
                </div>

                <div class="form-group mobile-width-90 system-width-45 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2 mobile-margin-left-2 system-margin-left-2 mobile-margin-right-2 system-margin-right-2">
                    <select name="term_id" class="form-select" required>
                        <option selected disabled hidden value="">Select Term</option>
                        <?php
                        $query = "SELECT id_number, term_name FROM sm_terms WHERE school_id_number='$school_id' ORDER BY term_name";
                        $result = mysqli_query($connection_server, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="' . $row['id_number'] . '">' . $row['term_name'] . '</option>';
                        }
                        ?>
                    </select>
                    <span class="form-span mobile-font-size-12 system-font-size-14">Term*</span>
                </div>

                <button name="bulk-print-btn" type="submit" class="button-box color-2 bg-4 onhover-bg-color-7 mobile-font-size-14 system-font-size-16 mobile-width-93 system-width-46 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2">
                    Generate Report Cards
                </button>
            </form>
        </div>
    </center>
</div>
<?php } ?>
