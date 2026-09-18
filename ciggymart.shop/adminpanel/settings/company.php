<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

if(isset($_POST['saveSettings'])) {
    foreach($_POST as $key => $value) {
        if($key != 'saveSettings') {
            $val = addslashes($value);
            $db->Execute("INSERT INTO tbl_settings (Setting_Key, Setting_Value) VALUES ('$key', '$val') ON DUPLICATE KEY UPDATE Setting_Value='$val'");
        }
    }
    echo "<script>alert('Settings saved successfully!'); window.location.href='company.php';</script>";
    exit;
}

$settingsRes = $db->ExecuteQuery("SELECT * FROM tbl_settings");
$settings = [];
if(!empty($settingsRes)) {
    foreach($settingsRes as $row) {
        $settings[$row['Setting_Key']] = $row['Setting_Value'];
    }
}
?>

<div class="modern-dashboard">
    <div class="module-header">
        <div class="header-left">
            <h2>Company Settings</h2>
            <p>Update global business details, branding, and contact information.</p>
        </div>
    </div>

    <div class="stat-card" style="max-width: 800px;">
        <form method="post" action="company.php" class="modern-form">
            <h5 class="mb-4 text-primary border-bottom pb-2">Business Profile</h5>
            
            <div class="form-group mb-3">
                <label>Company Name <span class="text-danger">*</span></label>
                <input type="text" name="COMPANY_NAME" class="modern-input" required value="<?php echo htmlspecialchars($settings['COMPANY_NAME'] ?? 'Keshri Express Courier ERP'); ?>">
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label>Support Email</label>
                    <input type="email" name="COMPANY_EMAIL" class="modern-input" value="<?php echo htmlspecialchars($settings['COMPANY_EMAIL'] ?? ''); ?>">
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label>Support Phone</label>
                    <input type="text" name="COMPANY_PHONE" class="modern-input" value="<?php echo htmlspecialchars($settings['COMPANY_PHONE'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group mb-3">
                <label>Registered Address</label>
                <textarea name="COMPANY_ADDRESS" class="modern-input" rows="3"><?php echo htmlspecialchars($settings['COMPANY_ADDRESS'] ?? ''); ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label>GST / Tax ID</label>
                    <input type="text" name="COMPANY_GST" class="modern-input" value="<?php echo htmlspecialchars($settings['COMPANY_GST'] ?? ''); ?>">
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label>Currency Symbol</label>
                    <input type="text" name="CURRENCY_SYMBOL" class="modern-input" value="<?php echo htmlspecialchars($settings['CURRENCY_SYMBOL'] ?? '₹'); ?>">
                </div>
            </div>
            
            <div class="form-actions mt-4 text-end">
                <button type="submit" name="saveSettings" class="btn-primary"><i class="fa fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>

