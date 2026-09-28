<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name, Company_Name, Pickup_Charge, Docket_Charge, Door_Delivery_Charge FROM tbl_clients ORDER BY Client_Name ASC");
$zones = $db->ExecuteQuery("SELECT Zone_Id, Zone_Code, Zone_Name FROM tbl_zones WHERE Is_Active=1 ORDER BY Zone_Id ASC");

// One charge-setup row per company, created by the migration.
// Driven from the company list so a newly created company shows up here even
// before its charge row exists; saving creates the row.
$chargeRows = $db->ExecuteQuery("SELECT B.Branch_Id, B.Branch_Name, B.Branch_Code, B.Franchise_Name,
    IFNULL(C.Fuel_Percent,0)      AS Fuel_Percent,
    IFNULL(C.Insurance_Percent,0) AS Insurance_Percent,
    IFNULL(C.Insurance_Minimum,0) AS Insurance_Minimum,
    IFNULL(C.FOV_Percent,0)       AS FOV_Percent,
    IFNULL(C.FOV_Minimum,0)       AS FOV_Minimum,
    IFNULL(C.Urgent_Percent,0)    AS Urgent_Percent,
    IFNULL(C.Urgent_Minimum,0)    AS Urgent_Minimum
    FROM tbl_branchs B
    LEFT JOIN tbl_charge_master C ON C.Branch_Id = B.Branch_Id
    ORDER BY B.Branch_Name ASC");

// Existing zone-wise pickup rates, keyed client|zone for a quick lookup below.
$zoneRates = array();
$zpRows = $db->ExecuteQuery("SELECT Client_Id, Zone_Id, Default_Charge FROM tbl_pickup_charges WHERE Zone_Id IS NOT NULL AND Zone_Id > 0 AND Is_Active=1");
if (!empty($zpRows)) {
    foreach ($zpRows as $zr) {
        $zoneRates[$zr['Client_Id'] . '|' . $zr['Zone_Id']] = $zr['Default_Charge'];
    }
}
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

    <!-- Company charge setup: fuel, insurance, FOV, urgent -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-sliders text-primary"></i> Charge Setup (Fuel / Insurance / FOV / Urgent)</h3>
            <span style="color:#64748b; font-size:12.5px;">
                Set once per company. Percentages apply to the freight; the minimum is charged when the
                calculated amount falls below it. Leave 0 to switch a charge off.
            </span>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th style="text-align:right;">Fuel %</th>
                        <th style="text-align:right;">Insurance %</th>
                        <th style="text-align:right;">Ins. Min (&#8377;)</th>
                        <th style="text-align:right;">FOV %</th>
                        <th style="text-align:right;">FOV Min (&#8377;)</th>
                        <th style="text-align:right;">Urgent %</th>
                        <th style="text-align:right;">Urgent Min (&#8377;)</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($chargeRows)) { foreach ($chargeRows as $cm) { ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars((string)$cm['Branch_Name']); ?></strong><br>
                            <small style="color:#94a3b8;"><?php echo htmlspecialchars((string)$cm['Branch_Code']); ?></small>
                        </td>
                        <?php
                        $cells = array(
                            'fuel_percent'      => $cm['Fuel_Percent'],
                            'insurance_percent' => $cm['Insurance_Percent'],
                            'insurance_minimum' => $cm['Insurance_Minimum'],
                            'fov_percent'       => $cm['FOV_Percent'],
                            'fov_minimum'       => $cm['FOV_Minimum'],
                            'urgent_percent'    => $cm['Urgent_Percent'],
                            'urgent_minimum'    => $cm['Urgent_Minimum'],
                        );
                        foreach ($cells as $field => $value) { ?>
                            <td style="text-align:right;">
                                <input type="number" step="0.01" min="0"
                                       class="form-control input-sm text-right charge-field"
                                       data-field="<?php echo $field; ?>"
                                       value="<?php echo number_format(floatval($value), 2, '.', ''); ?>"
                                       style="width:85px; display:inline-block;">
                            </td>
                        <?php } ?>
                        <td style="text-align:center;">
                            <button class="btn btn-primary btn-xs save-charge-setup" data-id="<?php echo $cm['Branch_Id']; ?>">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </td>
                    </tr>
                <?php } } else { ?>
                    <tr>
                        <td colspan="9" align="center" style="padding:24px; color:#94a3b8;">
                            No companies registered yet - add one in Branch / Company Master first.
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Zone-wise pickup rates -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-map-signs text-primary"></i> Zone-wise Pickup Rates</h3>
            <span style="color:#64748b; font-size:12.5px;">
                Set once per client and zone. A booking picks the rate for its destination zone automatically;
                blank falls back to the client's flat pickup charge above.
            </span>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <?php foreach ($zones as $z) { ?>
                            <th style="text-align:right;">
                                <?php echo htmlspecialchars((string)$z['Zone_Name']); ?>
                                <?php echo !empty($z['Zone_Code']) ? '<br><small style="color:#94a3b8;">' . htmlspecialchars((string)$z['Zone_Code']) . '</small>' : ''; ?>
                            </th>
                        <?php } ?>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($clients) && !empty($zones)) { foreach ($clients as $c) { ?>
                    <tr data-client="<?php echo $c['Client_Id']; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars((string)$c['Client_Name']); ?></strong><br>
                            <small style="color:#94a3b8;"><?php echo htmlspecialchars((string)$c['Client_Code']); ?></small>
                        </td>
                        <?php foreach ($zones as $z) {
                            $key = $c['Client_Id'] . '|' . $z['Zone_Id'];
                            $val = isset($zoneRates[$key]) ? floatval($zoneRates[$key]) : '';
                        ?>
                            <td style="text-align:right;">
                                <input type="number" step="0.01" min="0"
                                       class="form-control input-sm text-right zone-pickup"
                                       data-zone="<?php echo $z['Zone_Id']; ?>"
                                       value="<?php echo $val === '' ? '' : number_format($val, 2, '.', ''); ?>"
                                       placeholder="-" style="width:90px; display:inline-block;">
                            </td>
                        <?php } ?>
                        <td style="text-align:center;">
                            <button class="btn btn-primary btn-xs save-zone-pickup" data-id="<?php echo $c['Client_Id']; ?>">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </td>
                    </tr>
                <?php } } else { ?>
                    <tr>
                        <td colspan="9" align="center" style="padding:24px; color:#94a3b8;">
                            <?php echo empty($zones) ? 'No zones configured yet - add zones in Zone Master first.' : 'No clients registered yet.'; ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    $(".save-charge-setup").click(function() {
        var btn = $(this);
        var payload = { type: "saveChargeSetup", branch_id: btn.data("id") };
        btn.closest("tr").find(".charge-field").each(function() {
            payload[$(this).data("field")] = $(this).val();
        });

        btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: "charges_curd.php",
            type: "POST",
            data: payload,
            success: function() {
                btn.html('<i class="fa fa-check"></i> Saved');
                setTimeout(function() { btn.html('<i class="fa fa-save"></i> Save'); }, 1500);
            },
            error: function() {
                btn.html('<i class="fa fa-times"></i> Failed');
                setTimeout(function() { btn.html('<i class="fa fa-save"></i> Save'); }, 2000);
            }
        });
    });

    $(".save-zone-pickup").click(function() {
        var btn = $(this);
        var row = btn.closest("tr");
        var rates = {};
        row.find(".zone-pickup").each(function() {
            rates[$(this).data("zone")] = $(this).val();
        });

        btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: "charges_curd.php",
            type: "POST",
            data: {
                type: "saveZonePickup",
                client_id: btn.data("id"),
                rates: rates
            },
            success: function() {
                btn.html('<i class="fa fa-check"></i> Saved');
                setTimeout(function() { btn.html('<i class="fa fa-save"></i> Save'); }, 1500);
            },
            error: function() {
                btn.html('<i class="fa fa-times"></i> Failed');
                setTimeout(function() { btn.html('<i class="fa fa-save"></i> Save'); }, 2000);
            }
        });
    });

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
