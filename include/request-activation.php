<?php
$feature_name = $_GET['feature'] ?? 'Unknown Feature';
$display_feature_name = ucwords(str_replace('_', ' ', $feature_name));

$price_query = mysqli_query($connection_server, "SELECT price FROM sm_feature_prices WHERE feature_name='$feature_name'");
$price_result = mysqli_fetch_assoc($price_query);
$price = $price_result['price'] ?? 'Not Set';

$settings_query = mysqli_query($connection_server, "SELECT * FROM sm_sms_settings LIMIT 1");
$settings = mysqli_fetch_assoc($settings_query);
$paystack_public_key = $settings['paystack_public_key'] ?? '';
$bank_name = $settings['bank_name'] ?? '[Bank Name Not Set]';
$account_number = $settings['account_number'] ?? '[Account Number Not Set]';
$account_name = $settings['account_name'] ?? '[Account Name Not Set]';

?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="container-box bg-3 mobile-width-90 system-width-50 mobile-margin-top-2 system-margin-top-2 mobile-padding-top-2 mobile-padding-bottom-2">
            <h2 class="color-4">Activate <?php echo $display_feature_name; ?></h2>
            <p class="color-5">This feature is not currently active for your school. Please make a payment to activate it.</p>

            <h3 class="color-4">Price: <?php echo $price; ?></h3>

            <div style="text-align: left; padding: 10px;">
                <h4 class="color-4">Bank Transfer Details</h4>
                <p class="color-5">Please make a payment to the following bank account:</p>
                <p class="color-5"><strong>Bank Name:</strong> <?php echo $bank_name; ?></p>
                <p class="color-5"><strong>Account Number:</strong> <?php echo $account_number; ?></p>
                <p class="color-5"><strong>Account Name:</strong> <?php echo $account_name; ?></p>
            </div>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="feature_name" value="<?php echo $feature_name; ?>">
                <div class="form-group mobile-width-90 system-width-80">
                    <label class="color-4">Upload Proof of Payment</label>
                    <input type="file" name="payment_proof" class="form-input" required>
                </div>

                <button name="submit-payment-proof-btn" type="submit" class="button-box color-2 bg-4 onhover-bg-color-7">
                    Submit for Activation
                </button>
            </form>

            <button id="pay-with-paystack" class="button-box color-2 bg-4 onhover-bg-color-7" style="margin-top: 10px;">
                Pay with Paystack
            </button>
        </div>
    </center>
</div>
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('pay-with-paystack').addEventListener('click', function() {
    var handler = PaystackPop.setup({
        key: '<?php echo $paystack_public_key; ?>',
        email: '<?php echo $get_logged_user_details["email"]; ?>',
        amount: <?php echo ($price * 100); ?>,
        currency: 'NGN', // Or your currency
        ref: ''+Math.floor((Math.random() * 1000000000) + 1),
        metadata: {
            feature_name: '<?php echo $feature_name; ?>',
            school_id: '<?php echo $school_id; ?>'
        },
        callback: function(response) {
            window.location.href = '/verify-payment.php?reference=' + response.reference;
        },
        onClose: function() {
            alert('Window closed.');
        }
    });
    handler.openIframe();
});
</script>
