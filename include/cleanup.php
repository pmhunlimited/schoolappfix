<?php
if (($user_identifier_auth_id != "super_mod") && ($user_identifier_auth_id == "mod_adm") || ($user_identifier_auth_id == "adm_staff") && ($user_identifier_auth_id != "teacher") && ($user_identifier_auth_id != "stu_par") && ($user_identifier_auth_id != "stu")) {
?>
<div class="container-box bg-2 mobile-width-100 system-width-100 mobile-margin-top-1 system-margin-top-1">
    <center>
        <div class="container-box bg-3 mobile-width-90 system-width-50 mobile-margin-top-2 system-margin-top-2 mobile-padding-top-2 mobile-padding-bottom-2">
            <h2 class="color-4">Data Cleanup Tool</h2>
            <p class="color-5">Use this tool to clean up orphaned data in your school's database.</p>
            <div class="container-box bg-10 mobile-width-90 system-width-90 mobile-padding-top-2 mobile-padding-bottom-2">
                <h3 class="color-4">Clean Orphaned Subject Records</h3>
                <p class="color-5">This action will permanently delete score records for subjects that no longer exist in your school's subject list. This can happen if a subject was deleted after scores had already been entered for it.</p>
                <p class="color-4 text-bold-600">This action cannot be undone.</p>
                <form method="post" onsubmit="return confirm('Are you sure you want to delete all orphaned subject records? This action cannot be undone.');">
                    <button name="clean-orphaned-subjects" type="submit" class="button-box color-2 bg-4 onhover-bg-color-7 mobile-font-size-14 system-font-size-16 mobile-width-93 system-width-46 mobile-margin-top-2 system-margin-top-2 mobile-margin-bottom-2 system-margin-bottom-2 mobile-margin-left-1 system-margin-left-3 mobile-margin-right-1 system-margin-right-1">
                        Clean Orphaned Subject Records
                    </button>
                </form>
            </div>
        </div>
    </center>
</div>
<?php } ?>