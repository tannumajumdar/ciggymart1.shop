<?php
include('../../config.php');
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();
$branchId = 0;

// Origin defaults to the city/pincode of the client's own branch.
$clients = $db->ExecuteQuery("SELECT C.Client_Id, C.Client_Code, C.Client_Name, C.Company_Name, C.Address, C.Billing_Address, C.Contact_No, C.Email, C.GSTIN_No, C.PAN_No, C.Insurance_Percent,
    BD.Destination_Name AS Origin_City, IFNULL(NULLIF(B.Pincode,''), BD.Pincode) AS Origin_Pincode
    FROM tbl_clients C
    LEFT JOIN tbl_branchs B ON B.Branch_Id = C.Branch_Id
    LEFT JOIN tbl_destinations BD ON BD.Destination_Id = B.Destination_Id
    WHERE C.Is_Active=1 ORDER BY C.Client_Name ASC");
$destinations = $db->ExecuteQuery("SELECT D.Destination_Id, D.Destination_Name, D.Destination_Code, D.Pincode, S.State_Name FROM tbl_destinations D INNER JOIN tbl_states S ON S.State_Id = D.State_Id ORDER BY D.Destination_Name ASC");
$hsnList = $db->ExecuteQuery("SELECT HSN_Code, Description, GST_Percent FROM tbl_hsn_master WHERE Is_Active=1");
$partners = $db->ExecuteQuery("SELECT Partner_Id, Partner_Name FROM tbl_courier_partners WHERE Is_Active=1 ORDER BY Partner_Name ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-barcode text-primary"></i> New Consignment / AWB Booking</h1>
        <span style="color:#64748b; font-size:13px;">Click <i class="fa fa-unlock-alt"></i> next to a field to lock its value for the next booking. Pickup, ODA, FOV, fuel and GST are filled from the rate master.</span>
    </div>
    <div>
        <a href="index.php" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Consignment List</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">

    <div id="booking_alert" class="alert alert-success" style="display:none;"></div>

    <form id="consignmentForm" autocomplete="off">
        <div class="row">
            <!-- Left Column: Booking, Customer, Route, Shipment -->
            <div class="col-md-7">

                <!-- Card 1: AWB & Service -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-file-text-o text-primary"></i> AWB & Service</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>AWB Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="consignment_no" id="consignment_no" placeholder="e.g. KE1002345" required data-lockable>
                                <small id="awb_status_msg" style="display:none;"></small>
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Booking Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="date" id="booking_date" value="<?php echo date('d-m-Y'); ?>" required data-lockable>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Courier Partner</label>
                                <select class="form-control" name="courier_partner_id" id="courier_partner_id" data-lockable>
                                    <option value="0">-- Own Network --</option>
                                    <?php if (!empty($partners)) { foreach ($partners as $p) { ?>
                                        <option value="<?php echo $p['Partner_Id']; ?>"><?php echo htmlspecialchars($p['Partner_Name']); ?></option>
                                    <?php } } ?>
                                </select>
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Service Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="send_by" id="send_by" required data-lockable>
                                    <option value="1">Surface</option>
                                    <option value="2">Air</option>
                                    <option value="3">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Customer -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-user text-primary"></i> Customer</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="form-group custom-fg">
                            <label>Customer <span class="text-danger">*</span></label>
                            <select class="form-control" name="client_id" id="client_id" required data-lockable>
                                <option value="">-- Choose Customer --</option>
                                <?php foreach ($clients as $cl) { ?>
                                    <option value="<?php echo $cl['Client_Id']; ?>"
                                            data-company="<?php echo htmlspecialchars($cl['Company_Name']); ?>"
                                            data-address="<?php echo htmlspecialchars($cl['Address']); ?>"
                                            data-contact="<?php echo htmlspecialchars($cl['Contact_No']); ?>"
                                            data-email="<?php echo htmlspecialchars($cl['Email']); ?>"
                                            data-gstin="<?php echo htmlspecialchars($cl['GSTIN_No']); ?>"
                                            data-pan="<?php echo htmlspecialchars($cl['PAN_No']); ?>"
                                            data-ins="<?php echo floatval($cl['Insurance_Percent']); ?>"
                                            data-origin-city="<?php echo htmlspecialchars($cl['Origin_City']); ?>"
                                            data-origin-pin="<?php echo htmlspecialchars($cl['Origin_Pincode']); ?>">
                                        <?php echo htmlspecialchars($cl['Client_Name']); ?> (Code: <?php echo htmlspecialchars($cl['Client_Code']); ?><?php echo !empty($cl['Company_Name']) ? ' - ' . htmlspecialchars($cl['Company_Name']) : ''; ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

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

                <!-- Card 3: Consignee & Route -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-map-marker text-primary"></i> Consignee & Route</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Consignee</label>
                                <input type="text" class="form-control" name="consignee_name" id="consignee_name" placeholder="Receiver full name or firm" data-lockable>
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Mobile</label>
                                <input type="text" class="form-control" name="consignee_mobile" id="consignee_mobile" placeholder="10-digit mobile number" data-lockable>
                            </div>
                        </div>

                        <div class="form-group custom-fg">
                            <label>Delivery Address</label>
                            <input type="text" class="form-control" name="consignee_address" id="consignee_address" placeholder="Complete street address" data-lockable>
                        </div>

                        <div class="row">
                            <div class="col-sm-8 form-group custom-fg">
                                <label>Origin</label>
                                <input type="text" class="form-control" name="origin_city" id="origin_city" placeholder="Pickup city" data-lockable>
                            </div>
                            <div class="col-sm-4 form-group custom-fg">
                                <label>Origin Pincode</label>
                                <input type="text" class="form-control" name="origin_pincode" id="origin_pincode" placeholder="6-digit pincode" data-lockable>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-8 form-group custom-fg">
                                <label>Destination <span class="text-danger">*</span></label>
                                <select class="form-control" name="dest_id" id="dest_id" required data-lockable>
                                    <option value="">-- Select Destination City --</option>
                                    <?php foreach ($destinations as $dst) { ?>
                                        <option value="<?php echo $dst['Destination_Id']; ?>"
                                                data-name="<?php echo htmlspecialchars($dst['Destination_Name']); ?>"
                                                data-pincode="<?php echo htmlspecialchars($dst['Pincode']); ?>">
                                            <?php echo htmlspecialchars($dst['Destination_Name']); ?> (<?php echo htmlspecialchars($dst['State_Name']); ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-4 form-group custom-fg">
                                <label>Destination Pincode</label>
                                <input type="text" class="form-control" name="consignee_pincode" id="consignee_pincode" placeholder="6-digit pincode" data-lockable>
                            </div>
                        </div>
                        <div id="oda_badge_display">
                            <span class="badge-pill-modern badge-info">Select destination to check ODA</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Shipment -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-cubes text-primary"></i> Shipment</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-sm-4 form-group custom-fg">
                                <label>Document / Parcel</label>
                                <select class="form-control" name="mode" id="mode" data-lockable>
                                    <option value="1">Document</option>
                                    <option value="2">Parcel</option>
                                </select>
                            </div>
                            <div class="col-sm-8 form-group custom-fg">
                                <label>Products</label>
                                <input type="text" class="form-control" name="commodity_type" id="commodity_type" placeholder="e.g. Documents, Electronic Spares, Garments" data-lockable>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4 form-group custom-fg">
                                <label>HSN Code</label>
                                <select class="form-control" name="hsn_code" id="hsn_code" data-lockable>
                                    <?php foreach ($hsnList as $h) { ?>
                                        <option value="<?php echo htmlspecialchars($h['HSN_Code']); ?>">
                                            <?php echo htmlspecialchars($h['HSN_Code']); ?> (<?php echo $h['GST_Percent']; ?>%)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-4 form-group custom-fg">
                                <label>No. of Pieces</label>
                                <input type="number" min="1" class="form-control" name="pieces" id="pieces" value="1" required data-lockable>
                            </div>
                            <div class="col-sm-4 form-group custom-fg">
                                <label>Package Type</label>
                                <select class="form-control" name="package_type" id="package_type" data-lockable>
                                    <option value="Box">Box / Carton</option>
                                    <option value="Envelope">Envelope / Flyer</option>
                                    <option value="Packet">Packet</option>
                                    <option value="Wooden Crate">Wooden Crate</option>
                                    <option value="Bag">Bag / Gunny</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4 form-group custom-fg">
                                <label>Value (₹)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="insured_value" id="insured_value" value="0" data-lockable>
                            </div>
                            <div class="col-sm-4 form-group custom-fg">
                                <label>Insurance</label>
                                <select class="form-control" name="is_insured" id="is_insured" data-lockable>
                                    <option value="0">Not Insured</option>
                                    <option value="1">Insured</option>
                                </select>
                            </div>
                            <div class="col-sm-4 form-group custom-fg">
                                <label>Insurance Premium</label>
                                <input type="text" class="form-control calc-readout" id="insurance_premium_display" readonly value="₹ 0.00">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Weight & Charges -->
            <div class="col-md-5">

                <!-- Card 5: Weight & Dimensions -->
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-balance-scale text-primary"></i> Weight & Dimensions</h3>
                    </div>
                    <div class="erp-card-body">
                        <div class="row">
                            <div class="col-xs-6 form-group custom-fg">
                                <label>Actual Weight (KG) <span class="text-danger">*</span></label>
                                <input type="number" step="any" min="0.01" class="form-control" name="actual_weight" id="actual_weight" placeholder="0.500" required style="font-size:16px; font-weight:700;" data-lockable>
                            </div>
                            <div class="col-xs-6 form-group custom-fg">
                                <label>Volumetric Weight (KG)</label>
                                <input type="text" class="form-control calc-readout" id="display_vol_wt" readonly value="0.000">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 form-group custom-fg">
                                <label>Length (cm)</label>
                                <input type="number" step="any" min="0" class="form-control" name="length" id="dim_l" data-lockable>
                            </div>
                            <div class="col-xs-4 form-group custom-fg">
                                <label>Width (cm)</label>
                                <input type="number" step="any" min="0" class="form-control" name="width" id="dim_w" data-lockable>
                            </div>
                            <div class="col-xs-4 form-group custom-fg">
                                <label>Height (cm)</label>
                                <input type="number" step="any" min="0" class="form-control" name="height" id="dim_h" data-lockable>
                            </div>
                        </div>
                        <div class="form-group custom-fg" style="margin-bottom:0;">
                            <label style="color:#2563eb;">Chargeable Weight (KG)</label>
                            <input type="text" class="form-control calc-readout" id="display_chargeable_wt" readonly value="0.000" style="color:#2563eb; font-size:16px;">
                        </div>
                    </div>
                </div>

                <!-- Card 6: Charges & Tax -->
                <div class="erp-card" style="border-top: 3px solid #2563eb;">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-calculator text-primary"></i> Charges & Tax</h3>
                    </div>
                    <div class="erp-card-body" style="padding:16px 20px;">

                        <div class="form-group custom-fg" style="margin-bottom:12px;">
                            <label style="display:flex; justify-content:space-between;">
                                <span>Base Freight (rate master):</span>
                                <span id="lbl_base_freight" style="font-weight:700;">₹ 0.00</span>
                            </label>
                            <input type="hidden" name="subtotal" id="subtotal" value="0">
                        </div>

                        <!-- Blank = automatic from the masters; typing a value overrides it. -->
                        <div class="row">
                            <div class="col-xs-6 form-group lock-group">
                                <label style="font-size:12px; color:#64748b;">Pickup (zone-wise)</label>
                                <input type="number" step="0.01" class="form-control input-sm auto-charge" name="pickup_charge" id="pickup_charge" data-calc="pickup_charge" placeholder="auto" data-lockable>
                            </div>
                            <div class="col-xs-6 form-group lock-group">
                                <label style="font-size:12px; color:#64748b;">ODA</label>
                                <input type="number" step="0.01" class="form-control input-sm auto-charge" name="oda_charge" id="oda_charge" data-calc="oda_charge" placeholder="auto" data-lockable>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6 form-group lock-group">
                                <label style="font-size:12px; color:#64748b;">Door Delivery</label>
                                <input type="number" step="0.01" class="form-control input-sm auto-charge" name="door_charge" id="door_charge" data-calc="door_delivery_charge" placeholder="auto" data-lockable>
                            </div>
                            <div class="col-xs-6 form-group lock-group">
                                <label style="font-size:12px; color:#64748b;">Docket</label>
                                <input type="number" step="0.01" class="form-control input-sm auto-charge" name="docket_charge" id="docket_charge" data-calc="docket_charge" placeholder="auto" data-lockable>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6 form-group lock-group">
                                <label style="font-size:12px; color:#64748b;">Other Charges</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="other_charges" id="other_charges" value="0.00" data-lockable>
                            </div>
                            <div class="col-xs-6 form-group">
                                <label style="font-size:12px; color:#64748b;">Insurance</label>
                                <input type="text" class="form-control input-sm calc-readout" id="insurance_charge_display" readonly value="0.00">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6 form-group lock-group">
                                <label style="font-size:12px; color:#64748b;">Discount (%)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="discount_percent" id="discount_percent" value="0" data-lockable>
                            </div>
                            <div class="col-xs-6 form-group lock-group">
                                <label style="font-size:12px; color:#64748b;">Discount (₹)</label>
                                <input type="number" step="0.01" class="form-control input-sm" name="discount_rs" id="discount_rs" value="0" data-lockable>
                            </div>
                        </div>

                        <table class="table table-condensed" style="margin:6px 0 14px 0; font-size:13px;">
                            <tr><td>FOV</td><td class="text-right" id="out_fov">0.00</td></tr>
                            <tr><td>Fuel <span id="out_fuel_pct"></span></td><td class="text-right" id="out_fuel">0.00</td></tr>
                            <tr id="row_urgent" style="display:none;"><td>Urgent Surcharge</td><td class="text-right" id="out_urgent">0.00</td></tr>
                            <tr style="font-weight:700;"><td>Taxable Value</td><td class="text-right" id="out_taxable">0.00</td></tr>
                            <tr><td>CGST <span id="out_cgst_pct"></span></td><td class="text-right" id="out_cgst">0.00</td></tr>
                            <tr><td>SGST <span id="out_sgst_pct"></span></td><td class="text-right" id="out_sgst">0.00</td></tr>
                            <tr><td>IGST <span id="out_igst_pct"></span></td><td class="text-right" id="out_igst">0.00</td></tr>
                        </table>

                        <div style="background:#eff6ff; border: 2px dashed #93c5fd; border-radius: var(--radius-md); padding:16px; text-align:center; margin-bottom:20px;">
                            <span style="font-size:12px; font-weight:700; color:#1e40af; text-transform:uppercase; letter-spacing:0.5px;">Grand Total (incl. GST)</span>
                            <div id="display_grand_total" style="font-size:28px; font-weight:800; color:#1d4ed8; line-height:1.2; margin-top:4px;">₹ 0.00</div>
                        </div>

                        <button type="submit" id="btnSaveBooking" class="btn btn-primary btn-block btn-lg" style="border-radius: var(--radius-sm); font-weight:700; padding:12px;">
                            <i class="fa fa-check-circle"></i> Save & Book Consignment
                        </button>
                        <button type="button" id="btnResetBooking" class="btn btn-default btn-block btn-sm" style="margin-top:8px;">
                            <i class="fa fa-refresh"></i> Clear Unlocked Fields
                        </button>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script type="text/javascript" src="<?php echo PATH_JS_LIBRARIES; ?>/consignment_lock.js"></script>
<script>
$(document).ready(function() {
    $("#booking_date").datepicker({ dateFormat: "dd-mm-yy" });

    function money(v) {
        return (parseFloat(v) || 0).toFixed(2);
    }

    // Customer: preview card + origin from the customer's branch
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
            if (!FieldLock.isLocked("origin_city")) $("#origin_city").val(opt.data("origin-city") || "");
            if (!FieldLock.isLocked("origin_pincode")) $("#origin_pincode").val(opt.data("origin-pin") || "");
        } else {
            $("#client_preview_card").slideUp(200);
        }
        scheduleRecalc();
    });

    // Destination: default the delivery pincode (used for the ODA lookup)
    $("#dest_id").on("change", function() {
        var opt = $(this).find("option:selected");
        if (opt.val() && !FieldLock.isLocked("consignee_pincode")) {
            $("#consignee_pincode").val(opt.data("pincode") || "");
        }
        scheduleRecalc();
    });

    // Volumetric / chargeable weight, shown instantly; the server recomputes on save.
    $("#actual_weight, #dim_l, #dim_w, #dim_h").on("input", function() {
        var actual = parseFloat($("#actual_weight").val()) || 0;
        var l = parseFloat($("#dim_l").val()) || 0;
        var w = parseFloat($("#dim_w").val()) || 0;
        var h = parseFloat($("#dim_h").val()) || 0;
        var vol = (l > 0 && w > 0 && h > 0) ? (l * w * h) / 5000 : 0;
        $("#display_vol_wt").val(vol.toFixed(3));
        $("#display_chargeable_wt").val(Math.max(actual, vol).toFixed(3));
        scheduleRecalc();
    });

    $("#consignee_pincode, #insured_value, #is_insured, #send_by, #hsn_code, #other_charges, #discount_percent, #discount_rs")
        .on("input change", scheduleRecalc);
    $(".auto-charge").on("input", scheduleRecalc);

    // Charge boxes left blank are sent without a value so the server fills
    // them from the masters (zone-wise pickup, ODA by pincode, etc).
    function bookingData(type) {
        var data = $.grep($("#consignmentForm").serializeArray(), function(f) {
            var $el = $("#consignmentForm [name='" + f.name + "']");
            return !($el.hasClass("auto-charge") && $.trim(f.value) === "");
        });
        data.push({ name: "type", value: type });
        return $.param(data);
    }

    var recalcTimer = null, recalcSeq = 0;
    function scheduleRecalc() {
        clearTimeout(recalcTimer);
        recalcTimer = setTimeout(recalculateBooking, 250);
    }

    function recalculateBooking() {
        if (!$("#client_id").val() || !$("#dest_id").val() || !(parseFloat($("#actual_weight").val()) > 0)) {
            return;
        }
        var seq = ++recalcSeq;
        $.ajax({
            url: "consignment_curd.php",
            type: "POST",
            data: bookingData("computeCharges"),
            dataType: "json",
            success: function(resp) {
                if (seq !== recalcSeq || !resp || resp.status !== "success") return;
                var c = resp.calc, g = c.gst_details;

                $("#subtotal").val(c.base_freight);
                $("#lbl_base_freight").text("₹ " + money(c.base_freight));

                $(".auto-charge").each(function() {
                    if ($.trim($(this).val()) === "") {
                        $(this).attr("placeholder", "auto " + money(c[$(this).data("calc")]));
                    }
                });

                if (c.oda_details && c.oda_details.is_oda) {
                    $("#oda_badge_display").html('<span class="badge-pill-modern badge-oda-yes"><i class="fa fa-exclamation-triangle"></i> ODA AREA (+₹' + money(c.oda_details.oda_charge) + ')</span>');
                } else {
                    $("#oda_badge_display").html('<span class="badge-pill-modern badge-oda-no"><i class="fa fa-check-circle"></i> STANDARD DELIVERY (NO ODA)</span>');
                }

                var ins = c.insurance_details || {};
                $("#insurance_charge_display").val(money(c.insurance_charge));
                $("#insurance_premium_display").val("₹ " + money(c.insurance_charge) + (c.insurance_charge > 0 ? " (" + ins.insurance_rate + "%)" : ""));

                $("#out_fov").text(money(c.fov_charge));
                $("#out_fuel_pct").text("(" + c.fuel_percent + "%)");
                $("#out_fuel").text(money(c.fuel_charge));
                $("#row_urgent").toggle(c.urgent_charge > 0);
                $("#out_urgent").text(money(c.urgent_charge));
                $("#out_taxable").text(money(c.taxable_amount));
                $("#out_cgst_pct").text(g.cgst_amount > 0 ? "(" + g.cgst_rate + "%)" : "");
                $("#out_sgst_pct").text(g.sgst_amount > 0 ? "(" + g.sgst_rate + "%)" : "");
                $("#out_igst_pct").text(g.igst_amount > 0 ? "(" + g.igst_rate + "%)" : "");
                $("#out_cgst").text(money(c.cgst_amount));
                $("#out_sgst").text(money(c.sgst_amount));
                $("#out_igst").text(money(c.igst_amount));
                $("#display_grand_total").text("₹ " + money(c.grand_total));
            }
        });
    }

    // Clear everything that isn't locked, then put the locked values back.
    function resetForNextBooking(advanceAwb) {
        recalcSeq++;
        $("#consignmentForm")[0].reset();
        $("#booking_date").val("<?php echo date('d-m-Y'); ?>");
        $("#client_preview_card").hide();
        $("#oda_badge_display").html('<span class="badge-pill-modern badge-info">Select destination to check ODA</span>');
        $(".auto-charge").attr("placeholder", "auto");
        $("#subtotal").val(0);
        $("#lbl_base_freight, #display_grand_total").text("₹ 0.00");
        $("#insurance_charge_display").val("0.00");
        $("#insurance_premium_display").val("₹ 0.00");
        $("#display_vol_wt, #display_chargeable_wt").val("0.000");
        $("#out_fov, #out_fuel, #out_urgent, #out_taxable, #out_cgst, #out_sgst, #out_igst").text("0.00");
        $("#out_fuel_pct, #out_cgst_pct, #out_sgst_pct, #out_igst_pct").text("");
        $("#row_urgent").hide();

        FieldLock.restore(advanceAwb);
        $("#consignmentForm").find("input, select").filter(":visible:not([readonly]):not(.is-locked)").first().focus();
    }

    $("#btnResetBooking").on("click", function() {
        resetForNextBooking(false);
    });

    FieldLock.init("consignment_locks_admin");

    $("#consignmentForm").submit(function(e) {
        e.preventDefault();

        if (!$("#client_id").val()) {
            alert("Please select a customer.");
            return;
        }
        if (!$("#dest_id").val()) {
            alert("Please select a destination.");
            return;
        }
        if (!(parseFloat($("#actual_weight").val()) > 0)) {
            alert("Please enter a valid actual weight.");
            return;
        }

        var btn = $("#btnSaveBooking");
        var awb = $("#consignment_no").val();
        btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Saving Booking...');

        $.ajax({
            url: "consignment_curd.php",
            type: "POST",
            data: bookingData("addConsignment"),
            success: function(resp) {
                if ($.trim(resp) === "1") {
                    $("#booking_alert").html('<i class="fa fa-check-circle"></i> AWB <strong></strong> booked. <a href="index.php">View consignment list</a>')
                        .find("strong").text(awb).end().show();
                    resetForNextBooking(true);
                } else {
                    alert("Error saving consignment: " + resp);
                }
            },
            error: function() {
                alert("Network error while booking consignment.");
            },
            complete: function() {
                btn.prop("disabled", false).html('<i class="fa fa-check-circle"></i> Save & Book Consignment');
            }
        });
    });
});
</script>
