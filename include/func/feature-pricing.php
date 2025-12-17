<?php
if ($user_identifier_auth_id == "super_mod" && isset($_POST['update-prices-btn'])) {
    $features = ['bulk_print', 'data_cleanup', 'cbt'];
    $all_updated = true;

    foreach ($features as $feature_name) {
        $price = mysqli_real_escape_string($connection_server, $_POST['price_' . $feature_name]);

        $check_query = mysqli_query($connection_server, "SELECT * FROM sm_feature_prices WHERE feature_name='$feature_name'");
        if (mysqli_num_rows($check_query) > 0) {
            $update_query = "UPDATE sm_feature_prices SET price='$price' WHERE feature_name='$feature_name'";
            if (!mysqli_query($connection_server, $update_query)) {
                $all_updated = false;
            }
        } else {
            $insert_query = "INSERT INTO sm_feature_prices (feature_name, price) VALUES ('$feature_name', '$price')";
            if (!mysqli_query($connection_server, $insert_query)) {
                $all_updated = false;
            }
        }
    }

    $status_msg = $all_updated ? "Prices updated successfully." : "An error occurred while updating prices.";
    header("Location: /bc-admin.php?page=smgt_feature_pricing&tab=true&status_msg=" . urlencode($status_msg));
    exit();
}
