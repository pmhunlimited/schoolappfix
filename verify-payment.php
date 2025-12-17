<?php
session_start();
include("include/config-file.php");

if (isset($_GET['reference'])) {
    $reference = $_GET['reference'];

    $settings_query = mysqli_query($connection_server, "SELECT paystack_secret_key FROM sm_sms_settings LIMIT 1");
    $settings = mysqli_fetch_assoc($settings_query);
    $secret_key = $settings['paystack_secret_key'] ?? '';

    if (empty($secret_key)) {
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
        "Authorization: Bearer " . $secret_key,
        "Cache-Control: no-cache",
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      $result = json_decode($response);
      if ($result->data->status == 'success') {
          $school_id = $result->data->metadata->school_id;
          $feature_name = $result->data->metadata->feature_name;

          $check_query = "SELECT * FROM sm_feature_activations WHERE school_id_number='$school_id' AND feature_name='$feature_name'";
          $check_result = mysqli_query($connection_server, $check_query);

          if(mysqli_num_rows($check_result) > 0) {
              $update_query = "UPDATE sm_feature_activations SET activation_status='active' WHERE school_id_number='$school_id' AND feature_name='$feature_name'";
          } else {
              $update_query = "INSERT INTO sm_feature_activations (school_id_number, feature_name, activation_status) VALUES ('$school_id', '$feature_name', 'active')";
          }

          if (mysqli_query($connection_server, $update_query)) {
              $redirect_page = 'smgt_' . $feature_name;
              header("Location: /bc-admin.php?page=$redirect_page&tab=true&status_msg=Activation successful!");
              exit();
          }
      }
    }
}
header("Location: /bc-admin.php?page=smgt_dashboard&status_msg=Payment verification failed.");
exit();
