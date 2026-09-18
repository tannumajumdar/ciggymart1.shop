<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();
$branchId = intval($_SESSION['buser']);
$conId = intval($_GET['id']);

$sql = "SELECT CO.*, DATE_FORMAT(CO.Date_Of_Submit,'%d-%m-%Y') AS Date, 
D.Destination_Id, D.Destination_Code, D.Destination_Name, D.Is_ODA AS Dest_Is_ODA, D.ODA_Charge AS Dest_ODA_Charge, D.Door_Delivery_Charge AS Dest_Door_Charge,
C.Client_Id, C.Client_Code, C.Client_Name, C.Company_Name, C.Address, C.Billing_Address, C.Contact_No, C.Email, C.GSTIN_No, C.PAN_No, C.Fuel_Surcharge, C.Insurance_Percent, C.Pickup_Charge AS Client_Pickup_Charge, C.Docket_Charge AS Client_Docket_Charge
FROM tbl_consignments CO
LEFT JOIN tbl_destinations D ON D.Destination_Id = CO.Destination_Id
LEFT JOIN tbl_clients C ON C.Client_id = CO.Client_id
WHERE CO.Branch_Id=$branchId AND CO.Consignment_Id=$conId";

$res = $db->ExecuteQuery($sql);

if (empty($res)) {
    echo "<div class='container'><div class='alert alert-danger'>Consignment not found.</div></div>";
    exit();
}

$c = $res[1];

$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name, Company_Name, Address, Billing_Address, Contact_No, Email, GSTIN_No, PAN_No, Fuel_Surcharge, Insurance_Percent, Pickup_Charge, Docket_Charge, Door_Delivery_Charge FROM tbl_clients WHERE Branch_Id=$branchId AND Is_Active=1 ORDER BY Client_Name ASC");
$destinations = $db->ExecuteQuery("SELECT D.Destination_Id, D.Destination_Name, D.Destination_Code, D.Pincode, D.Is_ODA, D.ODA_Charge, D.Door_Delivery_Charge, S.State_Name, S.Zone_Id FROM tbl_destinations D INNER JOIN tbl_states S ON S.State_Id = D.State_Id ORDER BY D.Destination_Name ASC");
$hsnList = $db->ExecuteQuery("SELECT HSN_Code, Description, GST_Percent FROM tbl_hsn_master WHERE Is_Active=1");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-edit text-primary"></i> Edit Consignment #<?php echo htmlspecialchars($c['Consignment_No']); ?></h1>
        <span style="color:#64748b; font-size:13px;">Modify shipment details, parcel specifications, dimensional weights, and charge calculations</span>
    </div>
    <div>
        <a href="index.php" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Consignments</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">

    <form id="editConsignmentForm" autocomplete="off">
        <input type="hidden" name="consignment_id" value="<?php echo $c['Consignment_Id']; ?>">
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-7">
                
                <!-- Card 1: Core Shipment Info -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-file-text-o text-primary"></i> Shipment Information</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label>Booking Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="date" id="booking_date" value="<?php echo $c['Date']; ?>" required>
                            </div>
                            <div class="col-sm-5 form-group">
                                <label>Consignment / AWB No. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="consignment_no" id="consignment_no" value="<?php echo htmlspecialchars($c['Consignment_No']); ?>" required>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>Send By Mode <span class="text-danger">*</span></label>
                                <select class="form-control" name="send_by" id="send_by" required>
                                    <option value="1" <?php echo ($c['Send_By'] == 1) ? 'selected' : ''; ?>>Surface</option>
                                    <option value="2" <?php echo ($c['Send_By'] == 2) ? 'selected' : ''; ?>>Air</option>
                                    <option value="3" <?php echo ($c['Send_By'] == 3) ? 'selected' : ''; ?>>Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Client Info -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-user text-primary"></i> Client (Consignor) Information</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="form-group">
                            <label>Client <span class="text-danger">*</span></label>
                            <select class="form-control" name="client_id" id="client_id" required>
                                <?php foreach ($clients as $cl) { ?>
                                    <option value="<?php echo $cl['Client_Id']; ?>" 
                                            <?php echo ($cl['Client_Id'] == $c['Client_id']) ? 'selected' : ''; ?>
                                            data-company="<?php echo htmlspecialchars($cl['Company_Name']); ?>"
                                            data-address="<?php echo htmlspecialchars($cl['Address']); ?>"
                                            data-contact="<?php echo htmlspecialchars($cl['Contact_No']); ?>"
                                            data-email="<?php echo htmlspecialchars($cl['Email']); ?>"
                                            data-gstin="<?php echo htmlspecialchars($cl['GSTIN_No']); ?>"
                                            data-pan="<?php echo htmlspecialchars($cl['PAN_No']); ?>"
                                            data-fuel="<?php echo floatval($cl['Fuel_Surcharge']); ?>"
                                            data-ins="<?php echo floatval($cl['Insurance_Percent']); ?>"
                                            data-pickup="<?php echo floatval($cl['Pickup_Charge']); ?>"
                                            data-docket="<?php echo floatval($cl['Docket_Charge']); ?>">
                                        <?php echo htmlspecialchars($cl['Client_Name']); ?> (Code: <?php echo htmlspecialchars($cl['Client_Code']); ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Destination & Consignee Details -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-map-marker text-primary"></i> Destination & Consignee</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-7 form-group">
                                <label>Destination City <span class="text-danger">*</span></label>
                                <select class="form-control" name="dest_id" id="dest_id" required>
                                    <?php foreach ($destinations as $dst) { ?>
                                        <option value="<?php echo $dst['Destination_Id']; ?>"
                                                <?php echo ($dst['Destination_Id'] == $c['Destination_Id']) ? 'selected' : ''; ?>
                                                data-name="<?php echo htmlspecialchars($dst['Destination_Name']); ?>"
                                                data-pincode="<?php echo htmlspecialchars($dst['Pincode']); ?>"
                                                data-is-oda="<?php echo $dst['Is_ODA']; ?>"
                                                data-oda-charge="<?php echo floatval($dst['ODA_Charge']); ?>"
                                                data-door-charge="<?php echo floatval($dst['Door_Delivery_Charge']); ?>"
                                                data-zone="<?php echo $dst['Zone_Id']; ?>">
                                            <?php echo htmlspecialchars($dst['Destination_Name']); ?> (<?php echo htmlspecialchars($dst['State_Name']); ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-5 form-group">
                                <label>ODA Status</label>
                                <div id="oda_badge_display" style="padding-top:4px;">
                                    <?php if (!empty($c['ODA_Charge']) && $c['ODA_Charge'] > 0) { ?>
                                        <span class="badge-pill-modern badge-oda-yes"><i class="fa fa-exclamation-triangle"></i> ODA (+₹<?php echo floatval($c['ODA_Charge']); ?>)</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-oda-no"><i class="fa fa-check-circle"></i> STANDARD (NO ODA)</span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Consignee / Receiver Name</label>
                                <input type="text" class="form-control" name="consignee_name" id="consignee_name" value="<?php echo htmlspecialchars(isset($c['Consignee_Name']) ? $c['Consignee_Name'] : ''); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label>Receiver Mobile No.</label>
                                <input type="text" class="form-control" name="consignee_mobile" id="consignee_mobile" value="<?php echo htmlspecialchars(isset($c['Consignee_Mobile']) ? $c['Consignee_Mobile'] : ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-8 form-group">
                                <label>Delivery Address</label>
                                <input type="text" class="form-control" name="consignee_address" id="consignee_address" value="<?php echo htmlspecialchars(isset($c['Consignee_Address']) ? $c['Consignee_Address'] : ''); ?>">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label>Pincode</label>
                                <input type="text" class="form-control" name="consignee_pincode" id="consignee_pincode" value="<?php echo htmlspecialchars(isset($c['Consignee_Pincode']) ? $c['Consignee_Pincode'] : ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Parcel Specifications & Insurance -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-cubes text-primary"></i> Parcel Specifications & Transit Cover</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-3 form-group">
                                <label>Mode</label>
                                <select class="form-control" name="mode" id="mode">
                                    <option value="1" <?php echo ($c['Mode'] == 1) ? 'selected' : ''; ?>>Document (Dox)</option>
                                    <option value="2" <?php echo ($c['Mode'] == 2) ? 'selected' : ''; ?>>Non-Document</option>
                                </select>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>No. of Pieces</label>
                                <input type="number" min="1" class="form-control" name="pieces" id="pieces" value="<?php echo intval($c['No_Of_Pieces'] ? $c['No_Of_Pieces'] : 1); ?>" required>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>Package Type</label>
                                <select class="form-control" name="package_type">
                                    <option value="Box" <?php echo (isset($c['Package_Type']) && $c['Package_Type'] == 'Box') ? 'selected' : ''; ?>>Box / Carton</option>
                                    <option value="Envelope" <?php echo (isset($c['Package_Type']) && $c['Package_Type'] == 'Envelope') ? 'selected' : ''; ?>>Envelope / Flyer</option>
                                    <option value="Packet" <?php echo (isset($c['Package_Type']) && $c['Package_Type'] == 'Packet') ? 'selected' : ''; ?>>Packet</option>
                                    <option value="Wooden Crate" <?php echo (isset($c['Package_Type']) && $c['Package_Type'] == 'Wooden Crate') ? 'selected' : ''; ?>>Wooden Crate</option>
                                    <option value="Bag" <?php echo (isset($c['Package_Type']) && $c['Package_Type'] == 'Bag') ? 'selected' : ''; ?>>Bag / Gunny</option>
                                </select>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>HSN Code</label>
                                <select class="form-control" name="hsn_code" id="hsn_code">
                                    <?php foreach ($hsnList as $h) { ?>
                                        <option value="<?php echo htmlspecialchars($h['HSN_Code']); ?>" <?php echo (isset($c['HSN_Code']) && $c['HSN_Code'] == $h['HSN_Code']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($h['HSN_Code']); ?> (<?php echo $h['GST_Percent']; ?>%)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Commodity / Description</label>
                            <input type="text" class="form-control" name="commodity_type" value="<?php echo htmlspecialchars(isset($c['Commodity_Type']) ? $c['Commodity_Type'] : ''); ?>">
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label>Transit Insurance</label>
                                <select class="form-control" name="is_insured" id="is_insured">
                                    <option value="0" <?php echo (empty($c['Is_Insured']) || $c['Is_Insured'] == 0) ? 'selected' : ''; ?>>Not Insured</option>
                                    <option value="1" <?php echo (!empty($c['Is_Insured']) && $c['Is_Insured'] == 1) ? 'selected' : ''; ?>>Insured</option>
                                </select>
                            </div>
                            <div class="col-sm-4 form-group insurance-field" style="<?php echo (!empty($c['Is_Insured']) && $c['Is_Insured'] == 1) ? '' : 'display:none;'; ?>">
                                <label>Declared Value (₹)</label>
                                <input type="number" step="0.01" class="form-control" name="insured_value" id="insured_value" value="<?php echo floatval($c['Insured_Value']); ?>">
                            </div>
                            <div class="col-sm-4 form-group insurance-field" style="<?php echo (!empty($c['Is_Insured']) && $c['Is_Insured'] == 1) ? '' : 'display:none;'; ?>">
                                <label>Insurance Charge (₹)</label>
                                <input type="text" class="form-control" id="insurance_premium_display" readonly value="₹ <?php echo floatval(isset($c['Insurance_Charge']) ? $c['Insurance_Charge'] : 0); ?>">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Weight & Charge Breakdown -->
            <div class="col-md-5">

                <!-- Weight Calculator -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-balance-scale text-primary"></i> Weight & Dimensions</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Actual Weight (KG) <span class="text-danger">*</span></label>
                                <input type="number" step="any" min="0.01" class="form-control" name="actual_weight" id="actual_weight" value="<?php echo floatval($c['Total_Weight_In_KG']); ?>" required style="font-size:16px; font-weight:700;">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label>Dimensions (cm)</label>
                                <div style="display:flex; gap:6px;">
                                    <input type="number" step="any" class="form-control input-sm" name="length" id="dim_l" placeholder="L" value="<?php echo floatval(isset($c['Length_CM']) ? $c['Length_CM'] : 0); ?>">
                                    <input type="number" step="any" class="form-control input-sm" name="width" id="dim_w" placeholder="W" value="<?php echo floatval(isset($c['Width_CM']) ? $c['Width_CM'] : 0); ?>">
                                    <input type="number" step="any" class="form-control input-sm" name="height" id="dim_h" placeholder="H" value="<?php echo floatval(isset($c['Height_CM']) ? $c['Height_CM'] : 0); ?>">
                                </div>
                            </div>
                        </div>

                        <div style="background:#f1f5f9; border-radius: var(--radius-sm); padding: 14px; margin-top:8px;">
                            <div class="row text-center">
                                <div class="col-xs-4">
                                    <span style="font-size:11px; color:#64748b; font-weight:600;">ACTUAL</span><br>
                                    <strong id="display_actual_wt" style="font-size:15px; color:#0f172a;"><?php echo floatval($c['Total_Weight_In_KG']); ?> kg</strong>
                                </div>
                                <div class="col-xs-4">
                                    <span style="font-size:11px; color:#64748b; font-weight:600;">VOLUMETRIC</span><br>
                                    <strong id="display_vol_wt" style="font-size:15px; color:#64748b;"><?php echo floatval(isset($c['Volumetric_Weight']) ? $c['Volumetric_Weight'] : 0); ?> kg</strong>
                                </div>
                                <div class="col-xs-4">
                                    <span style="font-size:11px; color:#2563eb; font-weight:700;">CHARGEABLE</span><br>
                                    <strong id="display_chargeable_wt" style="font-size:16px; color:#2563eb;"><?php echo floatval(isset($c['Chargeable_Weight']) && $c['Chargeable_Weight'] > 0 ? $c['Chargeable_Weight'] : $c['Total_Weight_In_KG']); ?> kg</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charges Breakdown -->
                <div class="erp-card" style="border-top: 3px solid #2563eb;">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-calculator text-primary"></i> Rate & Charge Breakup</h3>
                    </div>
                    <div class="erp-card-body" style="padding:16px 20px;">
                        
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="display:flex; justify-content:space-between;">
                                <span>Base Freight / Slab Amount:</span>
                                <span id="lbl_base_freight" style="font-weight:700;">₹ <?php echo number_format($c['Subtotal'], 2); ?></span>
                            </label>
                            <input type="hidden" name="subtotal" id="subtotal" value="<?php echo floatval($c['Subtotal']); ?>">
                        </div>

                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Pickup Charge (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="pickup_charge" id="pickup_charge" value="<?php echo floatval(isset($c['Pickup_Charge']) ? $c['Pickup_Charge'] : 0); ?>">
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Door Delivery (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="door_charge" id="door_charge" value="<?php echo floatval(isset($c['Door_Delivery_Charge']) ? $c['Door_Delivery_Charge'] : 0); ?>">
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Docket Charge (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="docket_charge" id="docket_charge" value="<?php echo floatval(isset($c['Docket_Charge']) ? $c['Docket_Charge'] : 0); ?>">
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">ODA Surcharge (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="oda_charge" id="oda_charge" value="<?php echo floatval(isset($c['ODA_Charge']) ? $c['ODA_Charge'] : 0); ?>">
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Insurance Charge (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="insurance_charge" id="insurance_charge" value="<?php echo floatval(isset($c['Insurance_Charge']) ? $c['Insurance_Charge'] : 0); ?>" readonly>
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Other Charges (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="other_charges" id="other_charges" value="<?php echo floatval(isset($c['Other_Charges']) ? $c['Other_Charges'] : 0); ?>">
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:14px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Discount (%)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="discount_percent" id="discount_percent" value="<?php echo floatval($c['Discount_Percent']); ?>">
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Discount (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="discount_rs" id="discount_rs" value="<?php echo floatval($c['Discount_Rs']); ?>">
                            </div>
                        </div>

                        <div style="background:#eff6ff; border: 2px dashed #93c5fd; border-radius: var(--radius-md); padding:16px; text-align:center; margin-bottom:20px;">
                            <span style="font-size:12px; font-weight:700; color:#1e40af; text-transform:uppercase; letter-spacing:0.5px;">Total Consignment Amount</span>
                            <div id="display_grand_total" style="font-size:28px; font-weight:800; color:#1d4ed8; line-height:1.2; margin-top:4px;">₹ <?php echo number_format($c['Total_Amount'], 2); ?></div>
                            <input type="hidden" name="total_amt" id="total_amt" value="<?php echo floatval($c['Total_Amount']); ?>">
                        </div>

                        <button type="submit" id="btnUpdateBooking" class="btn btn-primary btn-block btn-lg" style="border-radius: var(--radius-sm); font-weight:700; padding:12px;">
                            <i class="fa fa-save"></i> Update Consignment Booking
                        </button>
                        <a href="index.php" class="btn btn-default btn-block btn-sm" style="margin-top:8px;">Cancel</a>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $("#booking_date").datepicker({ dateFormat: "dd-mm-yy" });

    // Client change
    $("#client_id").on("change", function() {
        var opt = $(this).find("option:selected");
        if (opt.val()) {
            if (opt.data("pickup") !== undefined) $("#pickup_charge").val(opt.data("pickup"));
            if (opt.data("docket") !== undefined) $("#docket_charge").val(opt.data("docket"));
            recalculateBooking();
        }
    });

    // Destination change
    $("#dest_id").on("change", function() {
        var opt = $(this).find("option:selected");
        if (opt.val()) {
            var isOda = opt.data("is-oda") == 1;
            var odaCharge = parseFloat(opt.data("oda-charge")) || 0;
            var doorCharge = parseFloat(opt.data("door-charge")) || 0;

            if (isOda) {
                $("#oda_badge_display").html('<span class="badge-pill-modern badge-oda-yes"><i class="fa fa-exclamation-triangle"></i> ODA (+₹' + odaCharge.toFixed(2) + ')</span>');
                $("#oda_charge").val(odaCharge.toFixed(2));
            } else {
                $("#oda_badge_display").html('<span class="badge-pill-modern badge-oda-no"><i class="fa fa-check-circle"></i> STANDARD (NO ODA)</span>');
                $("#oda_charge").val("0.00");
            }
            if (doorCharge > 0) $("#door_charge").val(doorCharge.toFixed(2));
            recalculateBooking();
        }
    });

    // Insurance toggle
    $("#is_insured").on("change", function() {
        if ($(this).val() == "1") {
            $(".insurance-field").slideDown(200);
            calculateInsurance();
        } else {
            $(".insurance-field").slideUp(200);
            $("#insured_value").val(0);
            $("#insurance_charge").val(0);
            $("#insurance_premium_display").val("₹ 0.00");
            recalculateBooking();
        }
    });

    $("#insured_value").on("input", function() {
        calculateInsurance();
    });

    function calculateInsurance() {
        var clientOpt = $("#client_id").find("option:selected");
        var insRate = parseFloat(clientOpt.data("ins")) || 2.0;
        var val = parseFloat($("#insured_value").val()) || 0;
        var prem = (val * insRate) / 100;
        $("#insurance_charge").val(prem.toFixed(2));
        $("#insurance_premium_display").val("₹ " + prem.toFixed(2));
        recalculateBooking();
    }

    // Weight change
    $("#actual_weight, #dim_l, #dim_w, #dim_h").on("input", function() {
        var actual = parseFloat($("#actual_weight").val()) || 0;
        var l = parseFloat($("#dim_l").val()) || 0;
        var w = parseFloat($("#dim_w").val()) || 0;
        var h = parseFloat($("#dim_h").val()) || 0;

        var vol = 0;
        if (l > 0 && w > 0 && h > 0) {
            vol = (l * w * h) / 5000;
        }
        var chargeable = Math.max(actual, vol);

        $("#display_actual_wt").text(actual.toFixed(3) + " kg");
        $("#display_vol_wt").text(vol.toFixed(3) + " kg");
        $("#display_chargeable_wt").text(chargeable.toFixed(3) + " kg");

        recalculateBooking();
    });

    $("#pickup_charge, #door_charge, #docket_charge, #oda_charge, #other_charges, #discount_percent, #discount_rs, #send_by").on("input change", function() {
        recalculateBooking();
    });

    function recalculateBooking() {
        var formData = $("#editConsignmentForm").serialize() + "&type=computeCharges";
        $.ajax({
            url: "consignment_curd.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(resp) {
                if (resp && resp.status === "success") {
                    var c = resp.calc;
                    $("#subtotal").val(c.base_freight);
                    $("#lbl_base_freight").text("₹ " + parseFloat(c.base_freight).toFixed(2));
                    $("#total_amt").val(c.total_amount);
                    $("#display_grand_total").text("₹ " + parseFloat(c.total_amount).toFixed(2));
                }
            }
        });
    }

    $("#editConsignmentForm").submit(function(e) {
        e.preventDefault();
        var btn = $("#btnUpdateBooking");
        btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

        var formData = $(this).serialize() + "&type=editConsignment";
        $.ajax({
            url: "consignment_curd.php",
            type: "POST",
            data: formData,
            success: function(resp) {
                if (resp.trim() === "1") {
                    alert("Consignment updated successfully!");
                    window.location.href = "index.php";
                } else {
                    alert("Error updating consignment: " + resp);
                    btn.prop("disabled", false).html('<i class="fa fa-save"></i> Update Consignment Booking');
                }
            }
        });
    });
});
</script>