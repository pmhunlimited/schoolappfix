<?php
if ($user_identifier_auth_id != "super_mod") {
    // Redirect non-superadmins away
    header("Location: /bc-admin.php?page=smgt_dashboard");
    exit();
}

$status_msg = '';
if (isset($_GET['status_msg'])) {
    $status_msg = htmlspecialchars($_GET['status_msg']);
}

// Handle feature enable/disable actions
if (isset($_POST['toggle_feature_btn'])) {
    $school_id = mysqli_real_escape_string($connection_server, $_POST['school_id']);
    $feature_name = mysqli_real_escape_string($connection_server, $_POST['feature_name']);
    $action = mysqli_real_escape_string($connection_server, $_POST['action']);

    $check_exists_q = mysqli_query($connection_server, "SELECT * FROM sm_feature_activations WHERE school_id='$school_id' AND feature_name='$feature_name'");

    if ($action == 'enable') {
        if (mysqli_num_rows($check_exists_q) > 0) {
            // Update existing record
            $update_q = "UPDATE sm_feature_activations SET activation_status='active', payment_method='manual', payment_status='completed', activation_date=NOW() WHERE school_id='$school_id' AND feature_name='$feature_name'";
            if (mysqli_query($connection_server, $update_q)) {
                $status_msg = "Feature enabled successfully.";
            } else {
                $status_msg = "Error enabling feature.";
            }
        } else {
            // Insert new record
            $insert_q = "INSERT INTO sm_feature_activations (school_id, feature_name, activation_status, payment_method, payment_status, activation_date, request_date) VALUES ('$school_id', '$feature_name', 'active', 'manual', 'completed', NOW(), NOW())";
            if (mysqli_query($connection_server, $insert_q)) {
                $status_msg = "Feature enabled successfully.";
            } else {
                $status_msg = "Error enabling feature.";
            }
        }
    } elseif ($action == 'disable') {
        // We only update, as there must be a record to disable
        $update_q = "UPDATE sm_feature_activations SET activation_status='inactive' WHERE school_id='$school_id' AND feature_name='$feature_name'";
        if (mysqli_query($connection_server, $update_q)) {
            $status_msg = "Feature disabled successfully.";
        } else {
            $status_msg = "Error disabling feature.";
        }
    }
    header("Location: /bc-admin.php?page=smgt_feature_activations&status_msg=" . urlencode($status_msg));
    exit();
}


// Handle approval of bank transfers
if (isset($_POST['approve-activation-btn'])) {
    $activation_id = mysqli_real_escape_string($connection_server, $_POST['activation_id']);

    $update_query = "UPDATE sm_feature_activations SET activation_status='active', payment_status='completed', activation_date=NOW() WHERE id='$activation_id'";

    if (mysqli_query($connection_server, $update_query)) {
        $status_msg = "Activation approved successfully.";
    } else {
        $status_msg = "An error occurred while approving the activation.";
    }

    header("Location: /bc-admin.php?page=smgt_feature_activations&tab=pending&status_msg=" . urlencode($status_msg));
    exit();
}

$active_tab = isset($_GET['tab']) && $_GET['tab'] == 'pending' ? 'pending' : 'manage';
$features = ['bulk_print', 'data_cleanup', 'cbt'];

?>

<div class="bc_heading">
    <div class="bc_heading_text">Feature Activations</div>
</div>

<div class="bc_container">
    <?php if ($status_msg): ?>
    <div class="bc-form-status-msg" style="display: block !important;"><?php echo $status_msg; ?></div>
    <?php endif; ?>

    <div class="tabs">
        <a href="?page=smgt_feature_activations&tab=manage" class="tab-link <?php echo $active_tab == 'manage' ? 'active' : ''; ?>">Manage Activations</a>
        <a href="?page=smgt_feature_activations&tab=pending" class="tab-link <?php echo $active_tab == 'pending' ? 'active' : ''; ?>">Pending Requests</a>
    </div>

    <div class="tab-content">
        <?php if ($active_tab == 'manage'): ?>
        <div id="manage-activations">
            <table class="bc-table">
                <thead>
                    <tr>
                        <th>School Name</th>
                        <th>Bulk Print</th>
                        <th>Data Cleanup</th>
                        <th>CBT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $schools_query = mysqli_query($connection_server, "SELECT * FROM sm_school_details ORDER BY school_name ASC");
                    if (mysqli_num_rows($schools_query) > 0) {
                        while ($school = mysqli_fetch_assoc($schools_query)) {
                            $school_id = $school['school_id_number'];

                            // Get all activations for this school at once
                            $activations = [];
                            $act_query = mysqli_query($connection_server, "SELECT feature_name, activation_status FROM sm_feature_activations WHERE school_id='$school_id'");
                            while($row = mysqli_fetch_assoc($act_query)) {
                                $activations[$row['feature_name']] = $row['activation_status'];
                            }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($school['school_name']); ?></td>
                        <?php foreach ($features as $feature):
                            $status = isset($activations[$feature]) ? $activations[$feature] : 'inactive';
                            $is_active = ($status === 'active');
                        ?>
                        <td>
                            <span class="status-<?php echo $is_active ? 'active' : 'inactive'; ?>"><?php echo ucfirst($status); ?></span>
                            <form method="post" style="display: inline-block; margin-left: 10px;">
                                <input type="hidden" name="school_id" value="<?php echo $school_id; ?>">
                                <input type="hidden" name="feature_name" value="<?php echo $feature; ?>">
                                <?php if ($is_active): ?>
                                    <input type="hidden" name="action" value="disable">
                                    <button type="submit" name="toggle_feature_btn" class="bc-btn-del">Disable</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="enable">
                                    <button type="submit" name="toggle_feature_btn" class="bc-btn-add">Enable</button>
                                <?php endif; ?>
                            </form>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='4'>No schools found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div id="pending-requests">
            <table class="bc-table">
                <thead>
                    <tr>
                        <th>School Name</th>
                        <th>Feature</th>
                        <th>Date Requested</th>
                        <th>Proof of Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pending_query = mysqli_query($connection_server, "
                        SELECT fa.*, sd.school_name
                        FROM sm_feature_activations fa
                        JOIN sm_school_details sd ON fa.school_id = sd.school_id_number
                        WHERE fa.payment_method = 'bank_transfer' AND fa.activation_status = 'pending'
                        ORDER BY fa.request_date DESC
                    ");
                    if (mysqli_num_rows($pending_query) > 0) {
                        while ($request = mysqli_fetch_assoc($pending_query)) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['school_name']); ?></td>
                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $request['feature_name']))); ?></td>
                        <td><?php echo date("Y-m-d H:i", strtotime($request['request_date'])); ?></td>
                        <td>
                            <?php if (!empty($request['payment_proof_path'])): ?>
                                <a href="/<?php echo htmlspecialchars($request['payment_proof_path']); ?>" target="_blank">View Proof</a>
                            <?php else: ?>
                                No proof uploaded
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="activation_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="approve-activation-btn" class="bc-btn-add">Approve</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='5'>No pending activation requests.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.tabs {
    display: flex;
    border-bottom: 2px solid #ccc;
    margin-bottom: 20px;
}
.tab-link {
    padding: 10px 20px;
    cursor: pointer;
    border: 1px solid transparent;
    border-bottom: none;
    margin-bottom: -2px;
    text-decoration: none;
    color: #333;
}
.tab-link.active {
    border-color: #ccc;
    border-bottom: 2px solid white;
    background-color: white;
    font-weight: bold;
}
.tab-content {
    padding: 10px;
}
.status-active {
    color: green;
    font-weight: bold;
}
.status-inactive {
    color: red;
    font-weight: bold;
}
</style>
