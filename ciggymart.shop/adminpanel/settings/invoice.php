<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

if(isset($_POST['saveInvoiceSettings'])) {
    foreach($_POST as $key => $value) {
        if($key != 'saveInvoiceSettings') {
            $val = addslashes($value);
            $db->Execute("INSERT INTO tbl_settings (Setting_Key, Setting_Value) VALUES ('$key', '$val') ON DUPLICATE KEY UPDATE Setting_Value='$val'");
        }
    }
    echo "<script>alert('Invoice Settings saved successfully!'); window.location.href='invoice.php';</script>";
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
            <h2>Invoice Settings</h2>
            <p>Configure billing prefixes, terms & conditions, and default due dates.</p>
        </div>
    </div>

    <div class="stat-card" style="max-width: 800px;">
        <form method="post" action="invoice.php" class="modern-form">
            <h5 class="mb-4 text-primary border-bottom pb-2">Billing Configuration</h5>
            
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label>Invoice Number Prefix <span class="text-danger">*</span></label>
                    <input type="text" name="INVOICE_PREFIX" class="modern-input" required value="<?php echo htmlspecialchars($settings['INVOICE_PREFIX'] ?? 'INV-'); ?>">
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label>Default Due Days</label>
                    <input type="number" name="INVOICE_DUE_DAYS" class="modern-input" value="<?php echo htmlspecialchars($settings['INVOICE_DUE_DAYS'] ?? '15'); ?>" placeholder="e.g. 15 or 30">
                </div>
            </div>

            <div class="form-group mb-4">
                <label>Terms & Conditions (Printed on Invoice)</label>
                <textarea name="INVOICE_TERMS" class="modern-input" rows="5" placeholder="1. Payment is due within standard terms. \n2. Late payment may incur interest."><?php echo htmlspecialchars($settings['INVOICE_TERMS'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group mb-4">
                <label>Bank Account Details (Printed on Invoice)</label>
                <textarea name="INVOICE_BANK_DETAILS" class="modern-input" rows="3" placeholder="Bank Name: SBI\nA/C No: 1234567890\nIFSC: SBIN0001234"><?php echo htmlspecialchars($settings['INVOICE_BANK_DETAILS'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions mt-4 text-end">
                <button type="submit" name="saveInvoiceSettings" class="btn-primary"><i class="fa fa-save"></i> Save Settings</button>
            </div>
        </form>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>

