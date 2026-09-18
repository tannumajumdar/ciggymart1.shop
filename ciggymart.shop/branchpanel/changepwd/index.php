<?php 
require('../../config.php');
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$branchId = isset($_SESSION['buser']) ? intval($_SESSION['buser']) : 0;
$ChangePwd = $db->ExecuteQuery("SELECT Password FROM tbl_branchs WHERE Branch_Id = $branchId");
$currPass = !empty($ChangePwd) && isset($ChangePwd[1]['Password']) ? $ChangePwd[1]['Password'] : '';
?>
<script type="text/javascript" src="pwd.js"></script>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-key text-primary"></i> Change Branch Password</h1>
        <span style="color:#64748b; font-size:13px;">Update the secure login credentials for this branch location</span>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-lock"></i> Security Credentials</h3>
                </div>
                <div class="erp-card-body">
                    <form role="form" id="changePassword" method="post">
                        <input type="hidden" id="password" value="<?php echo htmlspecialchars($currPass); ?>"/>
                        
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-weight:600; font-size:13px; color:#334155;">Current Password <span class="text-danger">*</span></label>
                            <input type="password" placeholder="Enter current password" id="old_pwd" name="old_pwd" class="form-control" required>
                        </div>

                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-weight:600; font-size:13px; color:#334155;">New Password <span class="text-danger">*</span></label>
                            <input type="password" placeholder="Enter new password (min 6 characters)" id="new_pwd" name="new_pwd" class="form-control" required>
                        </div>

                        <div class="form-group" style="margin-bottom:24px;">
                            <label style="font-weight:600; font-size:13px; color:#334155;">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" placeholder="Re-enter new password" id="con_pwd" name="con_pwd" class="form-control" required>
                        </div>

                        <button type="button" id="submit" class="btn btn-primary btn-block btn-lg" style="font-weight:700;">
                            <i class="fa fa-check-circle"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
