<?php
require('../config.php');
include_once(PATH_ADMIN_INCLUDE.'/header.php');
require_once(PATH_LIBRARIES.'/classes/CourierBillingEngine.php');

$db = new DBConn();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $settingsToUpdate = [
        'company_name' => isset($_POST['company_name']) ? trim($_POST['company_name']) : '',
        'company_tagline' => isset($_POST['company_tagline']) ? trim($_POST['company_tagline']) : '',
        'company_email' => isset($_POST['company_email']) ? trim($_POST['company_email']) : '',
        'company_phone' => isset($_POST['company_phone']) ? trim($_POST['company_phone']) : '',
        'company_address' => isset($_POST['company_address']) ? trim($_POST['company_address']) : '',
        'company_gstin' => isset($_POST['company_gstin']) ? trim($_POST['company_gstin']) : '',
        'company_pan' => isset($_POST['company_pan']) ? trim($_POST['company_pan']) : '',
        'volumetric_divisor' => isset($_POST['volumetric_divisor']) ? floatval($_POST['volumetric_divisor']) : 5000,
        'default_docket_charge' => isset($_POST['default_docket_charge']) ? floatval($_POST['default_docket_charge']) : 50.00,
        'default_pickup_charge' => isset($_POST['default_pickup_charge']) ? floatval($_POST['default_pickup_charge']) : 0.00,
        'default_door_delivery_charge' => isset($_POST['default_door_delivery_charge']) ? floatval($_POST['default_door_delivery_charge']) : 0.00,
        'default_oda_charge' => isset($_POST['default_oda_charge']) ? floatval($_POST['default_oda_charge']) : 150.00,
        'default_insurance_percent' => isset($_POST['default_insurance_percent']) ? floatval($_POST['default_insurance_percent']) : 2.00,
        'smtp_host' => isset($_POST['smtp_host']) ? trim($_POST['smtp_host']) : '',
        'smtp_port' => isset($_POST['smtp_port']) ? intval($_POST['smtp_port']) : 587,
        'smtp_user' => isset($_POST['smtp_user']) ? trim($_POST['smtp_user']) : '',
        'smtp_pass' => isset($_POST['smtp_pass']) ? trim($_POST['smtp_pass']) : '',
        'smtp_secure' => isset($_POST['smtp_secure']) ? trim($_POST['smtp_secure']) : 'tls',
        'smtp_from_email' => isset($_POST['smtp_from_email']) ? trim($_POST['smtp_from_email']) : '',
        'smtp_from_name' => isset($_POST['smtp_from_name']) ? trim($_POST['smtp_from_name']) : '',
        'email_auto_invoice' => isset($_POST['email_auto_invoice']) ? '1' : '0',
        'email_auto_payment' => isset($_POST['email_auto_payment']) ? '1' : '0'
    ];

    foreach ($settingsToUpdate as $key => $val) {
        $check = $db->ExecuteQuery("SELECT Setting_Id FROM tbl_settings WHERE Setting_Key='$key'");
        if (!empty($check) && count($check) > 0) {
            $db->query("UPDATE tbl_settings SET Setting_Value='".$db->escape($val)."' WHERE Setting_Key='$key'");
        } else {
            $db->query("INSERT INTO tbl_settings (Setting_Key, Setting_Value) VALUES ('$key', '".$db->escape($val)."')");
        }
    }
    $msg = "Settings updated successfully!";
}

// Fetch all settings
$res = $db->ExecuteQuery("SELECT Setting_Key, Setting_Value FROM tbl_settings");
$settings = [];
if (!empty($res)) {
    foreach ($res as $row) {
        $settings[$row['Setting_Key']] = $row['Setting_Value'];
    }
}
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-cogs text-primary"></i> ERP System Settings & Rules</h1>
        <span style="color:#64748b; font-size:13px;">Manage company profiles, volumetric calculations, default charge rates, and SMTP email settings</span>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <?php if (!empty($msg)) { ?>
        <div class="alert alert-success" style="border-radius: var(--radius-md); font-weight:600;">
            <i class="fa fa-check-circle"></i> <?php echo $msg; ?>
        </div>
    <?php } ?>

    <form method="POST" action="">
        <div class="row">
            <!-- Company & Billing Profile -->
            <div class="col-md-6">
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-building text-primary"></i> Company & Billing Profile</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="form-group custom-fg">
                            <label>Company Legal Name</label>
                            <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars(isset($settings['company_name']) ? $settings['company_name'] : 'Keshri Express Logistics'); ?>" required>
                        </div>
                        <div class="form-group custom-fg">
                            <label>Tagline / Description</label>
                            <input type="text" class="form-control" name="company_tagline" value="<?php echo htmlspecialchars(isset($settings['company_tagline']) ? $settings['company_tagline'] : ''); ?>">
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Corporate GSTIN</label>
                                <input type="text" class="form-control" name="company_gstin" value="<?php echo htmlspecialchars(isset($settings['company_gstin']) ? $settings['company_gstin'] : ''); ?>">
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Corporate PAN</label>
                                <input type="text" class="form-control" name="company_pan" value="<?php echo htmlspecialchars(isset($settings['company_pan']) ? $settings['company_pan'] : ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Support Phone</label>
                                <input type="text" class="form-control" name="company_phone" value="<?php echo htmlspecialchars(isset($settings['company_phone']) ? $settings['company_phone'] : ''); ?>">
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Support Email</label>
                                <input type="email" class="form-control" name="company_email" value="<?php echo htmlspecialchars(isset($settings['company_email']) ? $settings['company_email'] : ''); ?>">
                            </div>
                        </div>
                        <div class="form-group custom-fg">
                            <label>Registered Office Address</label>
                            <textarea class="form-control" name="company_address" rows="3"><?php echo htmlspecialchars(isset($settings['company_address']) ? $settings['company_address'] : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Weight & Default Charges -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-calculator text-success"></i> Weight Calculation & Default Charges</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="form-group custom-fg">
                            <label>Volumetric Weight Divisor (L x W x H in cm / Divisor)</label>
                            <input type="number" step="any" class="form-control" name="volumetric_divisor" value="<?php echo htmlspecialchars(isset($settings['volumetric_divisor']) ? $settings['volumetric_divisor'] : '5000'); ?>" required>
                            <small class="text-muted">Standard industry divisor: 5000 for centimeter dimensions.</small>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Default Docket Charge (&#8377;)</label>
                                <input type="number" step="0.01" class="form-control" name="default_docket_charge" value="<?php echo htmlspecialchars(isset($settings['default_docket_charge']) ? $settings['default_docket_charge'] : '50.00'); ?>">
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Default Pickup Charge (&#8377;)</label>
                                <input type="number" step="0.01" class="form-control" name="default_pickup_charge" value="<?php echo htmlspecialchars(isset($settings['default_pickup_charge']) ? $settings['default_pickup_charge'] : '0.00'); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Default Door Delivery Charge (&#8377;)</label>
                                <input type="number" step="0.01" class="form-control" name="default_door_delivery_charge" value="<?php echo htmlspecialchars(isset($settings['default_door_delivery_charge']) ? $settings['default_door_delivery_charge'] : '0.00'); ?>">
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Default ODA Charge (&#8377;)</label>
                                <input type="number" step="0.01" class="form-control" name="default_oda_charge" value="<?php echo htmlspecialchars(isset($settings['default_oda_charge']) ? $settings['default_oda_charge'] : '150.00'); ?>">
                            </div>
                        </div>
                        <div class="form-group custom-fg">
                            <label>Default Insurance Premium Rate (%)</label>
                            <input type="number" step="0.01" class="form-control" name="default_insurance_percent" value="<?php echo htmlspecialchars(isset($settings['default_insurance_percent']) ? $settings['default_insurance_percent'] : '2.00'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email & SMTP Automation Configuration -->
            <div class="col-md-6">
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-envelope-o text-info"></i> SMTP Email Automation Settings</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-8 form-group custom-fg">
                                <label>SMTP Host Server</label>
                                <input type="text" class="form-control" name="smtp_host" placeholder="smtp.gmail.com / mail.domain.com" value="<?php echo htmlspecialchars(isset($settings['smtp_host']) ? $settings['smtp_host'] : ''); ?>">
                            </div>
                            <div class="col-sm-4 form-group custom-fg">
                                <label>SMTP Port</label>
                                <input type="number" class="form-control" name="smtp_port" placeholder="587 / 465" value="<?php echo htmlspecialchars(isset($settings['smtp_port']) ? $settings['smtp_port'] : '587'); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>SMTP Username / Email</label>
                                <input type="text" class="form-control" name="smtp_user" value="<?php echo htmlspecialchars(isset($settings['smtp_user']) ? $settings['smtp_user'] : ''); ?>">
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>SMTP Password</label>
                                <input type="password" class="form-control" name="smtp_pass" value="<?php echo htmlspecialchars(isset($settings['smtp_pass']) ? $settings['smtp_pass'] : ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Encryption Protocol</label>
                                <select class="form-control" name="smtp_secure">
                                    <option value="tls" <?php echo (isset($settings['smtp_secure']) && $settings['smtp_secure'] == 'tls') ? 'selected' : ''; ?>>TLS (Port 587)</option>
                                    <option value="ssl" <?php echo (isset($settings['smtp_secure']) && $settings['smtp_secure'] == 'ssl') ? 'selected' : ''; ?>>SSL (Port 465)</option>
                                    <option value="none" <?php echo (isset($settings['smtp_secure']) && $settings['smtp_secure'] == 'none') ? 'selected' : ''; ?>>None (Port 25)</option>
                                </select>
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Sender Display Name</label>
                                <input type="text" class="form-control" name="smtp_from_name" value="<?php echo htmlspecialchars(isset($settings['smtp_from_name']) ? $settings['smtp_from_name'] : 'Keshri Express Billing'); ?>">
                            </div>
                        </div>
                        <div class="form-group custom-fg">
                            <label>Sender Email Address (From Email)</label>
                            <input type="email" class="form-control" name="smtp_from_email" value="<?php echo htmlspecialchars(isset($settings['smtp_from_email']) ? $settings['smtp_from_email'] : ''); ?>">
                        </div>
                        <hr>
                        <h4>Email Automation Triggers</h4>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="email_auto_invoice" value="1" <?php echo (!isset($settings['email_auto_invoice']) || $settings['email_auto_invoice'] == '1') ? 'checked' : ''; ?>>
                                <strong>Automatically send Invoice PDF</strong> to client upon invoice generation
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="email_auto_payment" value="1" <?php echo (!isset($settings['email_auto_payment']) || $settings['email_auto_payment'] == '1') ? 'checked' : ''; ?>>
                                <strong>Automatically send Payment Receipt PDF</strong> to client upon payment recording
                            </label>
                        </div>
                    </div>
                </div>

                <div style="text-align:right;">
                    <button type="submit" name="save_settings" class="btn btn-primary btn-lg" style="border-radius: var(--radius-sm); font-weight:700;">
                        <i class="fa fa-save"></i> Save All Configuration Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

