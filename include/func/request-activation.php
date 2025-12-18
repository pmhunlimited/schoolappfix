<?php
// Ensure this page is only accessible by school admins (moderators)
if ($user_identifier_auth_id != "mod_adm") {
    header("Location: /bc-admin.php?page=smgt_dashboard");
    exit();
}

$school_id_number = $get_logged_user_details['school_id_number'];
$feature_name = isset($_GET['feature']) ? mysqli_real_escape_string($connection_server, $_GET['feature']) : '';
$status_msg = isset($_GET['status_msg']) ? htmlspecialchars($_GET['status_msg']) : '';
$status_type = isset($_GET['status_type']) ? htmlspecialchars($_GET['status_type']) : 'info';

// Fetch feature price
$price_query = mysqli_query($connection_server, "SELECT price FROM sm_feature_prices WHERE feature_name='$feature_name'");
$feature_price_data = mysqli_fetch_assoc($price_query);
$feature_price = $feature_price_data ? $feature_price_data['price'] : null;

// Fetch payment settings from sm_sms_settings
$settings_query = mysqli_query($connection_server, "SELECT `item`, `value` FROM sm_sms_settings WHERE `item` IN ('paystack_public_key', 'bank_name', 'bank_account_name', 'bank_account_number')");
$settings = [];
while ($row = mysqli_fetch_assoc($settings_query)) {
    $settings[$row['item']] = $row['value'];
}
$paystack_pk = $settings['paystack_public_key'] ?? '';
$bank_name = $settings['bank_name'] ?? 'Not Set';
$bank_account_name = $settings['bank_account_name'] ?? 'Not Set';
$bank_account_number = $settings['bank_account_number'] ?? 'Not Set';


// Handle Bank Transfer submission
if (isset($_POST['submit-payment-proof-btn'])) {
    if (empty($_FILES["payment_proof"]["name"])) {
        $status_msg = "Please select a file to upload as proof of payment.";
        $status_type = "error";
    } else {
        // Handle file upload
        $target_dir = "dataimg/payment_proofs/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $filename = "proof_" . $school_id_number . "_" . $feature_name . "_" . time() . "." . strtolower(pathinfo($_FILES["payment_proof"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . $filename;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validations
        if ($_FILES["payment_proof"]["size"] > 2000000) { // 2MB
            $status_msg = "Sorry, your file is too large. Maximum size is 2MB.";
            $uploadOk = 0;
        } elseif (!in_array($imageFileType, ["jpg", "png", "jpeg", "pdf"])) {
            $status_msg = "Sorry, only JPG, JPEG, PNG & PDF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk && move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $target_file)) {
            // Check if a request already exists
            $check_q = mysqli_query($connection_server, "SELECT * FROM sm_feature_activations WHERE school_id_number='$school_id_number' AND feature_name='$feature_name'");
            if (mysqli_num_rows($check_q) > 0) {
                // Update existing request
                $update_q = "UPDATE sm_feature_activations SET activation_status='pending', payment_method='bank_transfer', payment_status='pending', payment_proof_path='$target_file', request_date=NOW() WHERE school_id_number='$school_id_number' AND feature_name='$feature_name'";
                $query_result = mysqli_query($connection_server, $update_q);
            } else {
                // Insert new request
                $insert_q = "INSERT INTO sm_feature_activations (school_id_number, feature_name, activation_status, payment_method, payment_status, payment_proof_path, request_date) VALUES ('$school_id_number', '$feature_name', 'pending', 'bank_transfer', 'pending', '$target_file', NOW())";
                $query_result = mysqli_query($connection_server, $insert_q);
            }

            if ($query_result) {
                $status_msg = "Your request has been submitted successfully. You will be notified once it is approved.";
                $status_type = "success";
            } else {
                $status_msg = "Database error: Could not submit your request.";
                $status_type = "error";
                unlink($target_file); // Clean up uploaded file on DB error
            }
        } else {
            if (empty($status_msg)) {
                 $status_msg = "Sorry, there was an error uploading your file.";
            }
            $status_type = "error";
        }
    }
    // Redirect to avoid form resubmission
    header("Location: /bc-admin.php?page=smgt_request_activation&feature=$feature_name&status_msg=" . urlencode($status_msg) . "&status_type=" . $status_type);
    exit();
}

$feature_display_name = ucwords(str_replace('_', ' ', $feature_name));
?>

<div class="bc_heading">
    <div class="bc_heading_text">Request Feature Activation</div>
</div>

<div class="bc_container">
    <div class="container-box bg-3 mobile-width-90 system-width-60" style="margin: auto; padding: 20px;">

        <?php if (!$feature_name || $feature_price === null): ?>
            <div class="bc-form-status-msg bc-form-error-msg" style="display: block !important;">
                Error: Invalid feature or price not set. Please contact the administrator.
            </div>
        <?php else: ?>
            <h2 class="color-4" style="text-align: center;">Activate: <?php echo htmlspecialchars($feature_display_name); ?></h2>
            <h3 class="color-5" style="text-align: center; margin-bottom: 20px;">Price: &#8358;<?php echo number_format($feature_price, 2); ?></h3>

            <?php if ($status_msg): ?>
            <div class="bc-form-status-msg <?php echo $status_type === 'success' ? 'bc-form-success-msg' : ($status_type === 'error' ? 'bc-form-error-msg' : ''); ?>" style="display: block !important;">
                <?php echo $status_msg; ?>
            </div>
            <?php endif; ?>

            <div class="tabs">
                <a href="javascript:void(0);" class="tab-link active" onclick="openTab(event, 'bank-transfer')">Pay via Bank Transfer</a>
                <a href="javascript:void(0);" class="tab-link" onclick="openTab(event, 'paystack')">Pay with Paystack</a>
            </div>

            <!-- Bank Transfer Tab -->
            <div id="bank-transfer" class="tab-content" style="display: block;">
                <h4>Bank Account Details</h4>
                <p><strong>Bank Name:</strong> <?php echo htmlspecialchars($bank_name); ?></p>
                <p><strong>Account Name:</strong> <?php echo htmlspecialchars($bank_account_name); ?></p>
                <p><strong>Account Number:</strong> <?php echo htmlspecialchars($bank_account_number); ?></p>
                <hr style="margin: 20px 0;">
                <p>Please make your payment to the account above and upload the proof of payment below.</p>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="feature_name" value="<?php echo htmlspecialchars($feature_name); ?>">

                    <div class="form-group">
                        <label for="payment_proof">Upload Proof of Payment (JPG, PNG, PDF)*</label>
                        <input type="file" name="payment_proof" id="payment_proof" class="form-control" required>
                    </div>

                    <button name="submit-payment-proof-btn" type="submit" class="bc-btn-add" style="width: 100%; margin-top: 20px;">
                        Submit Request
                    </button>
                </form>
            </div>

            <!-- Paystack Tab -->
            <div id="paystack" class="tab-content" style="display: none;">
                <p style="margin-bottom: 20px;">Click the button below to pay securely with Paystack. The feature will be activated automatically upon successful payment.</p>

                <button type="button" onclick="payWithPaystack()" class="bc-btn-add" style="width: 100%; background-color: #0d6efd;">
                    Pay with Paystack
                </button>
            </div>

        <?php endif; ?>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

function payWithPaystack() {
    var handler = PaystackPop.setup({
        key: '<?php echo htmlspecialchars($paystack_pk); ?>',
        email: '<?php echo htmlspecialchars($get_logged_user_details['email']); ?>',
        amount: <?php echo $feature_price * 100; ?>, // Amount in kobo
        currency: 'NGN',
        ref: '<?php echo $feature_name . '_' . $school_id_number . '_' . time(); ?>',
        metadata: {
            school_id_number: '<?php echo $school_id_number; ?>',
            feature_name: '<?php echo $feature_name; ?>'
        },
        callback: function(response) {
            // Redirect to a verification page
            window.location = '/verify-payment.php?reference=' + response.reference;
        },
        onClose: function() {
            alert('Transaction was not completed, window closed.');
        }
    });
    handler.openIframe();
}
</script>

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
    background-color: #f1f1f1;
}
.tab-link.active {
    border-color: #ccc;
    border-bottom: 2px solid white;
    background-color: white;
    font-weight: bold;
}
.tab-content {
    padding: 20px;
    border: 1px solid #ccc;
    border-top: none;
}
</style>
