<?php
if (isset($_POST['submit-payment-proof-btn'])) {
    $school_id = $get_logged_user_details['school_id_number'];
    $feature_name = mysqli_real_escape_string($connection_server, $_POST['feature_name']);

    // Handle file upload
    $target_dir = "dataimg/";
    $target_file = $target_dir . basename($_FILES["payment_proof"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        $status_msg = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["payment_proof"]["size"] > 500000) {
        $status_msg = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $status_msg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        // Handle error
    } else {
        if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $target_file)) {
            $payment_proof_filename = basename($_FILES["payment_proof"]["name"]);

            $insert_query = "INSERT INTO sm_feature_activations (school_id_number, feature_name, activation_status, payment_proof) VALUES ('$school_id', '$feature_name', 'pending', '$payment_proof_filename')";

            if (mysqli_query($connection_server, $insert_query)) {
                $status_msg = "Your request has been submitted. You will be notified once it is approved.";
            } else {
                $status_msg = "An error occurred while submitting your request.";
            }
        } else {
            $status_msg = "Sorry, there was an error uploading your file.";
        }
    }
    header("Location: /bc-admin.php?page=smgt_request_activation&feature=$feature_name&status_msg=" . urlencode($status_msg));
    exit();
}
