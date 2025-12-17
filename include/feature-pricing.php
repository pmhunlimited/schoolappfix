<?php
if ($user_identifier_auth_id == "super_mod") {
?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="container-box bg-3 mobile-width-90 system-width-50 mobile-margin-top-2 system-margin-top-2 mobile-padding-top-2 mobile-padding-bottom-2">
            <h2 class="color-4">Feature Pricing</h2>
            <p class="color-5">Set the price for premium features. Enter 0 for a feature to make it free.</p>

            <?php if (isset($_GET['status_msg'])): ?>
                <p class="color-5"><?php echo htmlspecialchars($_GET['status_msg']); ?></p>
            <?php endif; ?>

            <form method="post">
                <?php
                $features = ['bulk_print' => 'Bulk Print Results', 'data_cleanup' => 'Data Cleanup Tool', 'cbt' => 'CBT'];
                foreach ($features as $feature_name => $display_name) {
                    $price_query = mysqli_query($connection_server, "SELECT price FROM sm_feature_prices WHERE feature_name='$feature_name'");
                    $price_result = mysqli_fetch_assoc($price_query);
                    $price = $price_result['price'] ?? 0;
                ?>
                <div class="form-group mobile-width-90 system-width-80">
                    <label class="color-4"><?php echo $display_name; ?> Price</label>
                    <input type="number" name="price_<?php echo $feature_name; ?>" value="<?php echo $price; ?>" class="form-input" step="0.01" required>
                </div>
                <?php } ?>

                <button name="update-prices-btn" type="submit" class="button-box color-2 bg-4 onhover-bg-color-7">
                    Update Prices
                </button>
            </form>
        </div>
    </center>
</div>
<?php } ?>
