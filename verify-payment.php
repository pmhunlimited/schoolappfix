<?php
session_start();
include("include/config-file.php");

if (!isset($_SESSION["mod_adm_session"])) {
    header("Location: /bc-login.php");
    exit();
}

if (!isset($_GET['reference'])) {
    die("No payment reference supplied.");
}

$reference = mysqli_real_escape_string($connection_server, $_GET['reference']);

// Fetch Paystack secret key from the database
$settings_query = mysqli_query($connection_server, "SELECT `value` FROM sm_sms_settings WHERE `key` = 'paystack_secret_key'");
$settings_data = mysqli_fetch_assoc($settings_query);
$paystack_sk = $settings_data ? $settings_data['value'] : '';

if (empty($paystack_sk)) {
    die("Paystack secret key is not configured.");
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer " . $paystack_sk,
        "Cache-Control: no-cache",
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    // Handle cURL error
    header("Location: /bc-admin.php?page=smgt_dashboard&status_msg=" . urlencode("Payment verification failed: " . $err));
    exit();
}

$result = json_decode($response);

if ($result->status == true && $result->data->status == 'success') {
    // Payment is successful
    $metadata = $result->data->metadata;
    $school_id = $metadata->school_id;
    $feature_name = $metadata->feature_name;
    $amount_paid = $result->data->amount / 100; // Amount is in kobo
    $transaction_ref = $result->data->reference;

    // Sanitize data before DB operation
    $school_id_safe = mysqli_real_escape_string($connection_server, $school_id);
    $feature_name_safe = mysqli_real_escape_string($connection_server, $feature_name);
    $transaction_ref_safe = mysqli_real_escape_string($connection_server, $transaction_ref);

    // Check if an activation record already exists
    $check_q = mysqli_query($connection_server, "SELECT * FROM sm_feature_activations WHERE school_id='$school_id_safe' AND feature_name='$feature_name_safe'");

    if (mysqli_num_rows($check_q) > 0) {
        // Update existing record
        $update_q = "UPDATE sm_feature_activations
                     SET activation_status='active',
                         payment_method='paystack',
                         payment_status='completed',
                         transaction_ref='$transaction_ref_safe',
                         activation_date=NOW(),
                         request_date=NOW()
                     WHERE school_id='$school_id_safe' AND feature_name='$feature_name_safe'";
        $query_result = mysqli_query($connection_server, $update_q);
    } else {
        // Insert new record
        $insert_q = "INSERT INTO sm_feature_activations (school_id, feature_name, activation_status, payment_method, payment_status, transaction_ref, activation_date, request_date)
                     VALUES ('$school_id_safe', '$feature_name_safe', 'active', 'paystack', 'completed', '$transaction_ref_safe', NOW(), NOW())";
        $query_result = mysqli_query($connection_server, $insert_q);
    }

    if ($query_result) {
        $status_msg = "Payment successful! The feature '" . htmlspecialchars($feature_name) . "' has been activated.";
        // Redirect to the feature's page. We need a mapping for this.
        $redirect_page = 'smgt_dashboard'; // Default
        if ($feature_name == 'bulk_print') $redirect_page = 'smgt_bulk_print';
        if ($feature_name == 'data_cleanup') $redirect_page = 'smgt_cleanup';
        if ($feature_name == 'cbt') $redirect_page = 'smgt_cbt';

        header("Location: /bc-admin.php?page=$redirect_page&status_msg=" . urlencode($status_msg));
        exit();
    } else {
        // Handle DB Error
        header("Location: /bc-admin.php?page=smgt_dashboard&status_msg=" . urlencode("Payment was successful but we couldn't activate the feature. Please contact support."));
        exit();
    }

} else {
    // Payment failed or was not successful
    $status_msg = "Payment verification failed. Reason: " . ($result->message ?? 'Unknown error');
    header("Location: /bc-admin.php?page=smgt_request_activation&feature=" . urlencode($result->data->metadata->feature_name ?? '') . "&status_msg=" . urlencode($status_msg) . "&status_type=error");
    exit();
}
