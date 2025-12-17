<?php
if ($user_identifier_auth_id == "super_mod" && isset($_POST['approve-activation-btn'])) {
    $activation_id = mysqli_real_escape_string($connection_server, $_POST['activation_id']);

    $update_query = "UPDATE sm_feature_activations SET activation_status='active' WHERE id='$activation_id'";

    if (mysqli_query($connection_server, $update_query)) {
        $status_msg = "Activation approved successfully.";
    } else {
        $status_msg = "An error occurred while approving the activation.";
    }

    header("Location: /bc-admin.php?page=smgt_feature_activations&tab=true&status_msg=" . urlencode($status_msg));
    exit();
}
