<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// Handle update if posted
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_taxes'])) {
    $igst = floatval($_POST['igst']);
    $cgst = floatval($_POST['cgst']);
    $sgst = floatval($_POST['sgst']);
    
    $check = $db->ExecuteQuery("SELECT Tax_Id FROM tbl_taxes LIMIT 1");
    if (!empty($check)) {
        $db->ExecuteQuery("UPDATE tbl_taxes SET IGST=$igst, CGST=$cgst, SGST=$sgst WHERE Tax_Id=".$check[1]['Tax_Id']);
    } else {
        $db->ExecuteQuery("INSERT INTO tbl_taxes (IGST, CGST, SGST) VALUES ($igst, $cgst, $sgst)");
    }
    $msg = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> GST tax configuration updated successfully!</div>';
}

$taxes = $db->ExecuteQuery("SELECT * FROM tbl_taxes LIMIT 1");
$t = !empty($taxes) ? $taxes[1] : ['IGST' => 18, 'CGST' => 9, 'SGST' => 9];
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-percent text-primary"></i> GST & Tax Configuration</h1>
        <span style="color:#64748b; font-size:13px;">Manage central goods and services tax slabs applied during invoice generation</span>
    </div>
    <div>
        <a href="<?php echo MASTERS_LINK_CONTROL; ?>/hsn/" class="btn btn-primary btn-sm"><i class="fa fa-tags"></i> HSN & SAC Master</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">
    <?php echo $msg; ?>
    <div class="row">
        <div class="col-md-5">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-calculator"></i> Default GST Tax Slabs</h3>
                </div>
                <div class="erp-card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="update_taxes" value="1">
                        <div class="form-group custom-fg">
                            <label style="font-weight:600; font-size:13px; color:#334155;">Integrated GST (IGST % - Interstate)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="igst" value="<?php echo htmlspecialchars(isset($t['IGST']) ? $t['IGST'] : 18); ?>" required>
                                <span class="input-group-addon">%</span>
                            </div>
                            <small class="text-muted">Applied when Client and Booking Branch are in different states</small>
                        </div>

                        <div class="form-group custom-fg" style="margin-top:16px;">
                            <label style="font-weight:600; font-size:13px; color:#334155;">Central GST (CGST % - Intrastate)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="cgst" value="<?php echo htmlspecialchars(isset($t['CGST']) ? $t['CGST'] : 9); ?>" required>
                                <span class="input-group-addon">%</span>
                            </div>
                            <small class="text-muted">Applied along with SGST for intrastate courier services</small>
                        </div>

                        <div class="form-group custom-fg" style="margin-top:16px;">
                            <label style="font-weight:600; font-size:13px; color:#334155;">State GST (SGST % - Intrastate)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="sgst" value="<?php echo htmlspecialchars(isset($t['SGST']) ? $t['SGST'] : 9); ?>" required>
                                <span class="input-group-addon">%</span>
                            </div>
                            <small class="text-muted">Applied along with CGST for intrastate courier services</small>
                        </div>

                        <div style="margin-top:24px;">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Save Tax Rates</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-info-circle"></i> GST Courier Billing Guidelines</h3>
                </div>
                <div class="erp-card-body" style="line-height:1.7; color:#475569; font-size:13.5px;">
                    <div style="background:#eff6ff; border-left:4px solid #2563eb; padding:12px 16px; border-radius:4px; margin-bottom:16px;">
                        <strong style="color:#1e3a8a;">Courier SAC Code 996812:</strong> Courier and express cargo transport services are subject to <strong>18% GST</strong> (9% CGST + 9% SGST for intra-state OR 18% IGST for inter-state).
                    </div>
                    <ul style="padding-left:20px;">
                        <li><strong>Within State Deliveries:</strong> System automatically calculates CGST (9%) + SGST (9%) on taxable freight amount when the client's registered state matches the booking branch.</li>
                        <li><strong>Outside State Deliveries:</strong> System applies full IGST (18%) when the client's state differs from the booking branch state.</li>
                        <li><strong>Taxable Components:</strong> Subtotal includes Freight Base Rate + Volumetric Overweight + Pickup Charges + Door Delivery + Docket Charges + ODA Surcharges + Fuel Surcharge + Transit Insurance.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
