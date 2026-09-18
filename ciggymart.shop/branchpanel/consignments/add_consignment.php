<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();
$branchId = intval($_SESSION['buser']);

$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name, Company_Name, Address, Billing_Address, Contact_No, Email, GSTIN_No, PAN_No, Fuel_Surcharge, Insurance_Percent, Pickup_Charge, Docket_Charge, Door_Delivery_Charge FROM tbl_clients WHERE Branch_Id=$branchId AND Is_Active=1 ORDER BY Client_Name ASC");
$destinations = $db->ExecuteQuery("SELECT D.Destination_Id, D.Destination_Name, D.Destination_Code, D.Pincode, D.Is_ODA, D.ODA_Charge, D.Door_Delivery_Charge, S.State_Name, S.Zone_Id FROM tbl_destinations D INNER JOIN tbl_states S ON S.State_Id = D.State_Id ORDER BY D.Destination_Name ASC");
$hsnList = $db->ExecuteQuery("SELECT HSN_Code, Description, GST_Percent FROM tbl_hsn_master WHERE Is_Active=1");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-barcode text-primary"></i> New Consignment / AWB Booking</h1>
        <span style="color:#64748b; font-size:13px;">Fast booking with client auto-fill, dimensional weight calculation, ODA automation & instant charge breakdown</span>
    </div>
    <div>
        <a href="index.php" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Consignment List</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">

    <form id="consignmentForm" autocomplete="off">
        <div class="row">
            <!-- Left Column: Booking & Consignor/Consignee Info -->
            <div class="col-md-7">
                
                <!-- Card 1: Consignment Core Details -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-file-text-o text-primary"></i> AWB & Shipment Information</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label>Booking Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="date" id="booking_date" value="<?php echo date('d-m-Y'); ?>" required>
                            </div>
                            <div class="col-sm-5 form-group">
                                <label>Consignment / AWB No. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="consignment_no" id="consignment_no" placeholder="e.g. KE1002345" required>
                                <small id="awb_status_msg" style="display:none;"></small>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>Send By Mode <span class="text-danger">*</span></label>
                                <select class="form-control" name="send_by" id="send_by" required>
                                    <option value="1">Surface</option>
                                    <option value="2">Air</option>
                                    <option value="3">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Client (Consignor) with Instant Auto-Fill -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-user text-primary"></i> Client (Consignor) Information</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="form-group">
                            <label>Select Registered Client <span class="text-danger">*</span></label>
                            <select class="form-control" name="client_id" id="client_id" required>
                                <option value="">-- Choose Client --</option>
                                <?php foreach ($clients as $cl) { ?>
                                    <option value="<?php echo $cl['Client_Id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($cl['Client_Name']); ?>"
                                            data-company="<?php echo htmlspecialchars($cl['Company_Name']); ?>"
                                            data-address="<?php echo htmlspecialchars($cl['Address']); ?>"
                                            data-billing="<?php echo htmlspecialchars($cl['Billing_Address']); ?>"
                                            data-contact="<?php echo htmlspecialchars($cl['Contact_No']); ?>"
                                            data-email="<?php echo htmlspecialchars($cl['Email']); ?>"
                                            data-gstin="<?php echo htmlspecialchars($cl['GSTIN_No']); ?>"
                                            data-pan="<?php echo htmlspecialchars($cl['PAN_No']); ?>"
                                            data-fuel="<?php echo floatval($cl['Fuel_Surcharge']); ?>"
                                            data-ins="<?php echo floatval($cl['Insurance_Percent']); ?>"
                                            data-pickup="<?php echo floatval($cl['Pickup_Charge']); ?>"
                                            data-docket="<?php echo floatval($cl['Docket_Charge']); ?>">
                                        <?php echo htmlspecialchars($cl['Client_Name']); ?> (Code: <?php echo htmlspecialchars($cl['Client_Code']); ?><?php echo !empty($cl['Company_Name']) ? ' - ' . htmlspecialchars($cl['Company_Name']) : ''; ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Auto-filled Client Card Info -->
                        <div id="client_preview_card" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius: var(--radius-sm); padding:12px 16px; margin-top:10px; font-size:12.5px;">
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Company:</strong> <span id="cp_company">-</span><br>
                                    <strong>Address:</strong> <span id="cp_address">-</span><br>
                                    <strong>Contact:</strong> <span id="cp_contact">-</span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>GSTIN:</strong> <span id="cp_gstin">-</span><br>
                                    <strong>PAN:</strong> <span id="cp_pan">-</span><br>
                                    <strong>Email:</strong> <span id="cp_email">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Destination & Consignee Details -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-map-marker text-primary"></i> Destination & Consignee Details</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-7 form-group">
                                <label>Destination City <span class="text-danger">*</span></label>
                                <select class="form-control" name="dest_id" id="dest_id" required>
                                    <option value="">-- Select Destination City --</option>
                                    <?php foreach ($destinations as $dst) { ?>
                                        <option value="<?php echo $dst['Destination_Id']; ?>" 
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
                                <label>ODA Status Check</label>
                                <div id="oda_badge_display" style="padding-top:4px;">
                                    <span class="badge-pill-modern badge-info">Select Destination</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Consignee / Receiver Name</label>
                                <input type="text" class="form-control" name="consignee_name" id="consignee_name" placeholder="Receiver full name or firm">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label>Receiver Mobile No.</label>
                                <input type="text" class="form-control" name="consignee_mobile" id="consignee_mobile" placeholder="10-digit mobile number">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-8 form-group">
                                <label>Delivery Address</label>
                                <input type="text" class="form-control" name="consignee_address" id="consignee_address" placeholder="Complete street address">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label>Pincode</label>
                                <input type="text" class="form-control" name="consignee_pincode" id="consignee_pincode" placeholder="6-digit pincode">
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
                                    <option value="1">Document (Dox)</option>
                                    <option value="2">Non-Document</option>
                                </select>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>No. of Pieces</label>
                                <input type="number" min="1" class="form-control" name="pieces" id="pieces" value="1" required>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>Package Type</label>
                                <select class="form-control" name="package_type">
                                    <option value="Box">Box / Carton</option>
                                    <option value="Envelope">Envelope / Flyer</option>
                                    <option value="Packet">Packet</option>
                                    <option value="Wooden Crate">Wooden Crate</option>
                                    <option value="Bag">Bag / Gunny</option>
                                </select>
                            </div>
                            <div class="col-sm-3 form-group">
                                <label>HSN / SAC Code</label>
                                <select class="form-control" name="hsn_code" id="hsn_code">
                                    <?php foreach ($hsnList as $h) { ?>
                                        <option value="<?php echo htmlspecialchars($h['HSN_Code']); ?>">
                                            <?php echo htmlspecialchars($h['HSN_Code']); ?> (<?php echo $h['GST_Percent']; ?>%)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Commodity / Goods Description</label>
                            <input type="text" class="form-control" name="commodity_type" placeholder="e.g. Commercial Documents, Electronic Spares, Garments">
                        </div>

                        <hr>
                        <!-- Insurance Section -->
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label>Transit Insurance</label>
                                <select class="form-control" name="is_insured" id="is_insured">
                                    <option value="0">Not Insured</option>
                                    <option value="1">Insured (Transit Cover)</option>
                                </select>
                            </div>
                            <div class="col-sm-4 form-group insurance-field" style="display:none;">
                                <label>Declared Value (₹)</label>
                                <input type="number" step="0.01" class="form-control" name="insured_value" id="insured_value" value="0">
                            </div>
                            <div class="col-sm-4 form-group insurance-field" style="display:none;">
                                <label>Insurance Premium (₹)</label>
                                <input type="text" class="form-control" id="insurance_premium_display" readonly style="font-weight:bold; color:#059669; background:#ecfdf5;" value="₹ 0.00">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Weight Calculator & Live Charge Summary -->
            <div class="col-md-5">

                <!-- Card 5: Weight & Volumetric Calculator -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-balance-scale text-primary"></i> Weight & Dimensions</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Actual Weight (KG) <span class="text-danger">*</span></label>
                                <input type="number" step="any" min="0.01" class="form-control" name="actual_weight" id="actual_weight" placeholder="0.500" required style="font-size:16px; font-weight:700;">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label>Dimensions (cm)</label>
                                <div style="display:flex; gap:6px;">
                                    <input type="number" step="any" class="form-control input-sm" name="length" id="dim_l" placeholder="L">
                                    <input type="number" step="any" class="form-control input-sm" name="width" id="dim_w" placeholder="W">
                                    <input type="number" step="any" class="form-control input-sm" name="height" id="dim_h" placeholder="H">
                                </div>
                            </div>
                        </div>

                        <!-- Calculated Weights Comparison Bar -->
                        <div style="background:#f1f5f9; border-radius: var(--radius-sm); padding: 14px; margin-top:8px;">
                            <div class="row text-center">
                                <div class="col-xs-4">
                                    <span style="font-size:11px; color:#64748b; font-weight:600;">ACTUAL</span><br>
                                    <strong id="display_actual_wt" style="font-size:15px; color:#0f172a;">0.000 kg</strong>
                                </div>
                                <div class="col-xs-4">
                                    <span style="font-size:11px; color:#64748b; font-weight:600;">VOLUMETRIC</span><br>
                                    <strong id="display_vol_wt" style="font-size:15px; color:#64748b;">0.000 kg</strong>
                                </div>
                                <div class="col-xs-4">
                                    <span style="font-size:11px; color:#2563eb; font-weight:700;">CHARGEABLE</span><br>
                                    <strong id="display_chargeable_wt" style="font-size:16px; color:#2563eb;">0.000 kg</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 6: Live Billing Charges Breakdown -->
                <div class="erp-card" style="border-top: 3px solid #2563eb;">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-calculator text-primary"></i> Commercial Rate & Charge Breakup</h3>
                    </div>
                    <div class="erp-card-body" style="padding:16px 20px;">
                        
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="display:flex; justify-content:space-between;">
                                <span>Base Freight / Slab Amount:</span>
                                <span id="lbl_base_freight" style="font-weight:700;">₹ 0.00</span>
                            </label>
                            <input type="hidden" name="subtotal" id="subtotal" value="0">
                        </div>

                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Pickup Charge (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="pickup_charge" id="pickup_charge" value="0.00">
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Door Delivery (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="door_charge" id="door_charge" value="0.00">
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Docket Charge (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="docket_charge" id="docket_charge" value="0.00">
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">ODA Surcharge (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="oda_charge" id="oda_charge" value="0.00">
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Insurance Charge (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="insurance_charge" id="insurance_charge" value="0.00" readonly>
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Other Charges (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="other_charges" id="other_charges" value="0.00">
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:14px;">
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Discount (%)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="discount_percent" id="discount_percent" value="0">
                            </div>
                            <div class="col-xs-6">
                                <label style="font-size:12px; color:#64748b;">Discount (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="discount_rs" id="discount_rs" value="0">
                            </div>
                        </div>

                        <!-- Grand Total Display Box -->
                        <div style="background:#eff6ff; border: 2px dashed #93c5fd; border-radius: var(--radius-md); padding:16px; text-align:center; margin-bottom:20px;">
                            <span style="font-size:12px; font-weight:700; color:#1e40af; text-transform:uppercase; letter-spacing:0.5px;">Total Consignment Amount</span>
                            <div id="display_grand_total" style="font-size:28px; font-weight:800; color:#1d4ed8; line-height:1.2; margin-top:4px;">₹ 0.00</div>
                            <input type="hidden" name="total_amt" id="total_amt" value="0">
                        </div>

                        <!-- Action Buttons -->
                        <button type="submit" id="btnSaveBooking" class="btn btn-primary btn-block btn-lg" style="border-radius: var(--radius-sm); font-weight:700; padding:12px;">
                            <i class="fa fa-check-circle"></i> Save & Book Consignment
                        </button>
                        <button type="reset" class="btn btn-default btn-block btn-sm" style="margin-top:8px;">
                            <i class="fa fa-refresh"></i> Reset Form
                        </button>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $("#booking_date").datepicker({ dateFormat: "dd-mm-yy" });

    // Client auto-fill handler
    $("#client_id").on("change", function() {
        var opt = $(this).find("option:selected");
        if (opt.val()) {
            $("#cp_company").text(opt.data("company") || "-");
            $("#cp_address").text(opt.data("address") || "-");
            $("#cp_contact").text(opt.data("contact") || "-");
            $("#cp_gstin").text(opt.data("gstin") || "-");
            $("#cp_pan").text(opt.data("pan") || "-");
            $("#cp_email").text(opt.data("email") || "-");
            $("#client_preview_card").slideDown(200);

            if (opt.data("pickup") !== undefined) $("#pickup_charge").val(opt.data("pickup"));
            if (opt.data("docket") !== undefined) $("#docket_charge").val(opt.data("docket"));
            recalculateBooking();
        } else {
            $("#client_preview_card").slideUp(200);
            recalculateBooking();
        }
    });

    // Destination & ODA auto-check
    $("#dest_id").on("change", function() {
        var opt = $(this).find("option:selected");
        if (opt.val()) {
            var isOda = opt.data("is-oda") == 1;
            var odaCharge = parseFloat(opt.data("oda-charge")) || 0;
            var doorCharge = parseFloat(opt.data("door-charge")) || 0;

            if (isOda) {
                $("#oda_badge_display").html('<span class="badge-pill-modern badge-oda-yes"><i class="fa fa-exclamation-triangle"></i> ODA AREA (+₹' + odaCharge.toFixed(2) + ')</span>');
                $("#oda_charge").val(odaCharge.toFixed(2));
            } else {
                $("#oda_badge_display").html('<span class="badge-pill-modern badge-oda-no"><i class="fa fa-check-circle"></i> STANDARD DELIVERY (NO ODA)</span>');
                $("#oda_charge").val("0.00");
            }

            if (doorCharge > 0) {
                $("#door_charge").val(doorCharge.toFixed(2));
            }
            recalculateBooking();
        } else {
            $("#oda_badge_display").html('<span class="badge-pill-modern badge-info">Select Destination</span>');
            $("#oda_charge").val("0.00");
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
        $("#insurance_premium_display").val("₹ " + prem.toFixed(2) + " (" + insRate + "%)");
        recalculateBooking();
    }

    // Weight & Dimension calculations
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

    // Charge inputs trigger re-computation
    $("#pickup_charge, #door_charge, #docket_charge, #oda_charge, #other_charges, #discount_percent, #discount_rs, #send_by").on("input change", function() {
        recalculateBooking();
    });

    // Central Ajax Re-calculation
    function recalculateBooking() {
        var clientId = $("#client_id").val();
        var destId = $("#dest_id").val();
        var actualWt = parseFloat($("#actual_weight").val()) || 0;

        if (!clientId || !destId || actualWt <= 0) {
            updateGrandTotalDisplay();
            return;
        }

        var formData = $("#consignmentForm").serialize() + "&type=computeCharges";
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

    function updateGrandTotalDisplay() {
        var freight = parseFloat($("#subtotal").val()) || 0;
        var pickup = parseFloat($("#pickup_charge").val()) || 0;
        var door = parseFloat($("#door_charge").val()) || 0;
        var docket = parseFloat($("#docket_charge").val()) || 0;
        var oda = parseFloat($("#oda_charge").val()) || 0;
        var ins = parseFloat($("#insurance_charge").val()) || 0;
        var other = parseFloat($("#other_charges").val()) || 0;
        var disPct = parseFloat($("#discount_percent").val()) || 0;
        var disRs = parseFloat($("#discount_rs").val()) || 0;

        var netFreight = freight;
        if (disPct > 0) netFreight = freight - ((freight * disPct) / 100);
        else if (disRs > 0) netFreight = freight - disRs;

        var total = Math.max(0, netFreight) + pickup + door + docket + oda + ins + other;
        $("#total_amt").val(total.toFixed(2));
        $("#display_grand_total").text("₹ " + total.toFixed(2));
    }

    // Form Submission & Validation
    $("#consignmentForm").submit(function(e) {
        e.preventDefault();

        if (!$("#client_id").val()) {
            alert("Please select a registered client.");
            return;
        }
        if (!$("#dest_id").val()) {
            alert("Please select a destination city.");
            return;
        }
        if (parseFloat($("#actual_weight").val()) <= 0) {
            alert("Please enter a valid actual weight.");
            return;
        }

        var btn = $("#btnSaveBooking");
        btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Saving Booking...');

        var formData = $(this).serialize() + "&type=addConsignment";
        $.ajax({
            url: "consignment_curd.php",
            type: "POST",
            data: formData,
            success: function(resp) {
                if (resp.trim() === "1") {
                    alert("Consignment booked successfully!");
                    window.location.href = "index.php";
                } else {
                    alert("Error saving consignment: " + resp);
                    btn.prop("disabled", false).html('<i class="fa fa-check-circle"></i> Save & Book Consignment');
                }
            },
            error: function() {
                alert("Network error while booking consignment.");
                btn.prop("disabled", false).html('<i class="fa fa-check-circle"></i> Save & Book Consignment');
            }
        });
    });
});
</script>