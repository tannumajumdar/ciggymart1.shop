<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name, Company_Name, Pickup_Charge, Docket_Charge, Door_Delivery_Charge FROM tbl_clients ORDER BY Client_Name ASC");
$destinations = $db->ExecuteQuery("SELECT Destination_Id, Destination_Name, Destination_Code, Door_Delivery_Charge, ODA_Charge, Is_ODA FROM tbl_destinations ORDER BY Destination_Name ASC");
$settings = $db->ExecuteQuery("SELECT Setting_Key, Setting_Value FROM tbl_settings");
$setMap = [];
foreach ($settings as $s) {
    $setMap[$s['Setting_Key']] = $s['Setting_Value'];
}
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-calculator text-primary"></i> Logistics Surcharges & Rates Master</h1>
        <span style="color:#64748b; font-size:13px;">Manage client-specific and destination-specific Pickup, Door Delivery, Docket, and ODA charges</span>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <!-- Default Rates Overview Banner -->
    <div class="stat-card-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <div class="stat-icon-box stat-icon-blue"><i class="fa fa-file-text-o"></i></div>
            <div class="stat-content">
                <div class="stat-label">Default Docket</div>
                <div class="stat-value">₹ <?php echo isset($setMap['default_docket_charge']) ? $setMap['default_docket_charge'] : '50.00'; ?></div>
                <div class="stat-sub">Global Default</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-green"><i class="fa fa-truck"></i></div>
            <div class="stat-content">
                <div class="stat-label">Default Pickup</div>
                <div class="stat-value">₹ <?php echo isset($setMap['default_pickup_charge']) ? $setMap['default_pickup_charge'] : '0.00'; ?></div>
                <div class="stat-sub">Global Default</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-amber"><i class="fa fa-home"></i></div>
            <div class="stat-content">
                <div class="stat-label">Default Door Delivery</div>
                <div class="stat-value">₹ <?php echo isset($setMap['default_door_delivery_charge']) ? $setMap['default_door_delivery_charge'] : '0.00'; ?></div>
                <div class="stat-sub">Global Default</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-rose"><i class="fa fa-compass"></i></div>
            <div class="stat-content">
                <div class="stat-label">Default ODA Charge</div>
                <div class="stat-value">₹ <?php echo isset($setMap['default_oda_charge']) ? $setMap['default_oda_charge'] : '150.00'; ?></div>
                <div class="stat-sub">Remote Pincodes</div>
            </div>
        </div>
    </div>

    <!-- Client Specific Charges Table -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-users text-primary"></i> Client-Specific Surcharges (Pickup, Docket, Door Delivery)</h3>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th width="60">S.No</th>
                        <th>Client Code</th>
                        <th>Client Name</th>
                        <th>Company</th>
                        <th style="text-align:right;">Pickup Charge (₹)</th>
                        <th style="text-align:right;">Docket Charge (₹)</th>
                        <th style="text-align:right;">Door Delivery (₹)</th>
                        <th width="100" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clients) && count($clients) > 0) {
                        $i = 1;
                        foreach ($clients as $c) { ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($c['Client_Code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($c['Client_Name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['Company_Name'] ? $c['Company_Name'] : '-'); ?></td>
                                <td style="text-align:right;">
                                    <input type="number" step="0.01" class="form-control input-sm text-right client-pickup" data-id="<?php echo $c['Client_Id']; ?>" value="<?php echo floatval($c['Pickup_Charge']); ?>" style="width: 100px; display:inline-block;">
                                </td>
                                <td style="text-align:right;">
                                    <input type="number" step="0.01" class="form-control input-sm text-right client-docket" data-id="<?php echo $c['Client_Id']; ?>" value="<?php echo floatval($c['Docket_Charge']); ?>" style="width: 100px; display:inline-block;">
                                </td>
                                <td style="text-align:right;">
                                    <input type="number" step="0.01" class="form-control input-sm text-right client-door" data-id="<?php echo $c['Client_Id']; ?>" value="<?php echo floatval($c['Door_Delivery_Charge']); ?>" style="width: 100px; display:inline-block;">
                                </td>
                                <td align="center">
                                    <button type="button" class="btn btn-xs btn-success save-client-charges" data-id="<?php echo $c['Client_Id']; ?>"><i class="fa fa-save"></i> Save</button>
                                </td>
                            </tr>
                        <?php $i++; }
                    } else { ?>
                        <tr>
                            <td colspan="8" align="center" style="padding:24px; color:#94a3b8;">No clients registered yet.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(".save-client-charges").click(function() {
        var btn = $(this);
        var clientId = btn.data("id");
        var pickup = btn.closest("tr").find(".client-pickup").val();
        var docket = btn.closest("tr").find(".client-docket").val();
        var door = btn.closest("tr").find(".client-door").val();

        btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: "charges_curd.php",
            type: "POST",
            data: {
                type: "saveClientCharges",
                client_id: clientId,
                pickup_charge: pickup,
                docket_charge: docket,
                door_charge: door
            },
            success: function(response) {
                btn.html('<i class="fa fa-check"></i> Saved');
                setTimeout(function() {
                    btn.html('<i class="fa fa-save"></i> Save');
                }, 1500);
            }
        });
    });
});
</script>
