<?php
if ($user_identifier_auth_id == "super_mod") {
?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="container-box bg-3 mobile-width-90 system-width-80 mobile-margin-top-2 system-margin-top-2 mobile-padding-top-2 mobile-padding-bottom-2">
            <h2 class="color-4">Feature Activation Requests</h2>
            <p class="color-5">Review and approve pending activation requests from schools.</p>

            <?php if (isset($_GET['status_msg'])): ?>
                <p class="color-5"><?php echo htmlspecialchars($_GET['status_msg']); ?></p>
            <?php endif; ?>

            <table class="table-tag-borderless" style="width: 100%;">
                <tr>
                    <th>School Name</th>
                    <th>Feature</th>
                    <th>Status</th>
                    <th>Proof</th>
                    <th>Action</th>
                </tr>
                <?php
                $requests_query = mysqli_query($connection_server, "SELECT fa.*, sd.school_name FROM sm_feature_activations fa JOIN sm_school_details sd ON fa.school_id_number = sd.school_id_number WHERE fa.activation_status = 'pending'");
                while ($request = mysqli_fetch_assoc($requests_query)) {
                ?>
                <tr>
                    <td><?php echo $request['school_name']; ?></td>
                    <td><?php echo ucwords(str_replace('_', ' ', $request['feature_name'])); ?></td>
                    <td><?php echo $request['activation_status']; ?></td>
                    <td>
                        <?php if ($request['payment_proof']): ?>
                            <a href="/dataimg/<?php echo $request['payment_proof']; ?>" target="_blank">View Proof</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="activation_id" value="<?php echo $request['id']; ?>">
                            <button type="submit" name="approve-activation-btn" class="button-box color-2 bg-4">Approve</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </center>
</div>
<?php } ?>
