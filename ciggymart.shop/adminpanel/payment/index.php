<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
require_once(PATH_LIBRARIES.'/classes/CourierBillingEngine.php');

$db = new DBConn();
$branchId = intval(0);
$billing = new CourierBillingEngine($db);

$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name, Company_Name, Email, Contact_No FROM tbl_clients WHERE Branch_Id=$branchId AND Is_Active=1 ORDER BY Client_Name ASC");
$banks = $db->ExecuteQuery("SELECT Bank_Id, Bank_Name FROM tbl_banks WHERE Branch_Id=$branchId AND Is_Active=1");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-credit-card text-primary"></i> Create Payment Receipt</h1>
        <span style="color:#64748b; font-size:13px;">Record client payment, auto-generate sequential receipt number, update ledger, generate PDF & email client</span>
    </div>
    <div>
        <a href="../paymentreport/" class="btn btn-default btn-sm"><i class="fa fa-history"></i> Payment Receipts Report</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">

    <form id="paymentReceiptForm" autocomplete="off">
        <div class="row">
            
            <!-- Left Column: Payment Details -->
            <div class="col-md-7">
                <div class="erp-card">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-user text-primary"></i> Client & Transaction Details</h3>
                    </div>
                    <div class="erp-card-body">
                        
                        <div class="form-group custom-fg">
                            <label>Select Client <span class="text-danger">*</span></label>
                            <select class="form-control" name="client_id" id="client_id" required>
                                <option value="">-- Select Client --</option>
                                <?php foreach ($clients as $cl) { ?>
                                    <option value="<?php echo $cl['Client_Id']; ?>" 
                                            data-email="<?php echo htmlspecialchars($cl['Email']); ?>"
                                            data-name="<?php echo htmlspecialchars($cl['Client_Name']); ?>"
                                            data-company="<?php echo htmlspecialchars($cl['Company_Name']); ?>">
                                        <?php echo htmlspecialchars($cl['Client_Name']); ?> (Code: <?php echo htmlspecialchars($cl['Client_Code']); ?><?php echo !empty($cl['Company_Name']) ? ' - ' . htmlspecialchars($cl['Company_Name']) : ''; ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Client Invoices Selector -->
                        <div class="form-group custom-fg" id="invoice_selection_area" style="display:none;">
                            <label>Allocate to Invoice (Optional)</label>
                            <select class="form-control" name="invoice_id" id="invoice_id">
                                <option value="0">General On-Account Payment</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Payment Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="payment_date" id="payment_date" value="<?php echo date('d-m-Y'); ?>" required>
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Payment Amount Received (&#8377;) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="1" class="form-control" name="payment_amount" id="payment_amount" placeholder="0.00" required style="font-size:18px; font-weight:700; color:#059669;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Payment Mode <span class="text-danger">*</span></label>
                                <select class="form-control" name="payment_mode" id="payment_mode" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="NEFT/RTGS">NEFT / RTGS / IMPS</option>
                                    <option value="UPI">UPI / QR Code</option>
                                    <option value="Demand Draft">Demand Draft (DD)</option>
                                </select>
                            </div>
                            <div class="col-sm-6 form-group custom-fg">
                                <label>Reference / Cheque / UTR No.</label>
                                <input type="text" class="form-control" name="reference_no" id="reference_no" placeholder="Transaction Ref / Cheque No">
                            </div>
                        </div>

                        <div class="form-group custom-fg">
                            <label>Deposited In Bank (Optional)</label>
                            <select class="form-control" name="bank_id">
                                <option value="0">-- Direct Cash / Primary Bank --</option>
                                <?php foreach ($banks as $bk) { ?>
                                    <option value="<?php echo $bk['Bank_Id']; ?>"><?php echo htmlspecialchars($bk['Bank_Name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group custom-fg">
                            <label>Remarks / Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="e.g. Received via GPay / Cheque clearance remarks"></textarea>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="send_email" value="1" checked>
                                <strong>Automatically email PDF receipt</strong> to client (<span id="client_email_display">no client selected</span>)
                            </label>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right Column: Live Balance Ledger & Actions -->
            <div class="col-md-5">
                <div class="erp-card" style="border-top: 3px solid #10b981;">
                    <div class="erp-card-header">
                        <h3 class="erp-card-title"><i class="fa fa-calculator text-success"></i> Outstanding & Balance Summary</h3>
                    </div>
                    <div class="erp-card-body">
                        
                        <div style="background:#f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding:16px; margin-bottom:16px;">
                            <table style="width:100%; font-size:13.5px;">
                                <tr>
                                    <td style="padding:6px 0; color:#64748b;">Total Billed Invoices:</td>
                                    <td align="right" style="font-weight:600;"><span id="disp_total_billed">&#8377; 0.00</span></td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#64748b;">Total Prior Payments:</td>
                                    <td align="right" style="font-weight:600; color:#059669;"><span id="disp_total_paid">&#8377; 0.00</span></td>
                                </tr>
                                <tr style="border-top:1px solid #e2e8f0;">
                                    <td style="padding:10px 0; font-weight:700; color:#0f172a;">Current Outstanding:</td>
                                    <td align="right" style="padding:10px 0; font-weight:700; font-size:16px; color:#dc2626;"><span id="disp_prev_balance">&#8377; 0.00</span></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Live Calculation Preview -->
                        <div style="background:#ecfdf5; border: 2px dashed #6ee7b7; border-radius: var(--radius-md); padding:16px; margin-bottom:20px; text-align:center;">
                            <span style="font-size:12px; font-weight:600; color:#065f46; text-transform:uppercase;">Estimated Remaining Balance</span>
                            <div id="disp_remaining_balance" style="font-size:26px; font-weight:800; color:#047857; margin-top:4px;">&#8377; 0.00</div>
                            <input type="hidden" name="previous_balance" id="previous_balance" value="0">
                            <input type="hidden" name="remaining_balance" id="remaining_balance" value="0">
                        </div>

                        <button type="submit" id="btnSubmitReceipt" class="btn btn-success btn-block btn-lg" style="border-radius: var(--radius-sm); font-weight:700; padding:12px;">
                            <i class="fa fa-check-circle"></i> Generate & Issue Receipt
                        </button>
                        <a href="../paymentreport/" class="btn btn-default btn-block btn-sm" style="margin-top:8px;">View Receipt History</a>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $("#payment_date").datepicker({ dateFormat: "dd-mm-yy" });

    // Client change: fetch outstanding and invoices
    $("#client_id").on("change", function() {
        var clientId = $(this).val();
        var opt = $(this).find("option:selected");
        
        if (clientId) {
            $("#client_email_display").text(opt.data("email") || "No email on record");
            
            $.ajax({
                url: "payment_curd.php",
                type: "POST",
                data: { type: "getClientInvoicesAndBalance", client_id: clientId },
                dataType: "json",
                success: function(resp) {
                    if (resp && resp.status === "success") {
                        var b = resp.balance;
                        $("#disp_total_billed").text("&#8377; " + parseFloat(b.total_billed).toFixed(2));
                        $("#disp_total_paid").text("&#8377; " + parseFloat(b.total_paid).toFixed(2));
                        $("#disp_prev_balance").text("&#8377; " + parseFloat(b.outstanding).toFixed(2));
                        $("#previous_balance").val(b.outstanding);

                        // Populate Invoices
                        var invSelect = $("#invoice_id");
                        invSelect.empty();
                        invSelect.append('<option value="0">General On-Account Payment</option>');
                        if (resp.invoices && resp.invoices.length > 0) {
                            $.each(resp.invoices, function(i, inv) {
                                invSelect.append('<option value="' + inv.Invoice_Id + '" data-amt="' + inv.Final_Total_Amt + '">Invoice #' + inv.Invoice_No + ' (&#8377; ' + inv.Final_Total_Amt + ')</option>');
                            });
                            $("#invoice_selection_area").show();
                        } else {
                            $("#invoice_selection_area").hide();
                        }

                        recalculateRemaining();
                    }
                }
            });
        } else {
            $("#client_email_display").text("no client selected");
            $("#disp_total_billed").text("&#8377; 0.00");
            $("#disp_total_paid").text("&#8377; 0.00");
            $("#disp_prev_balance").text("&#8377; 0.00");
            $("#previous_balance").val(0);
            $("#disp_remaining_balance").text("&#8377; 0.00");
            $("#remaining_balance").val(0);
            $("#invoice_selection_area").hide();
        }
    });

    // Invoice selection autofill amount
    $("#invoice_id").on("change", function() {
        var opt = $(this).find("option:selected");
        if (opt.val() > 0 && opt.data("amt")) {
            $("#payment_amount").val(opt.data("amt"));
            recalculateRemaining();
        }
    });

    $("#payment_amount").on("input", function() {
        recalculateRemaining();
    });

    function recalculateRemaining() {
        var prev = parseFloat($("#previous_balance").val()) || 0;
        var paid = parseFloat($("#payment_amount").val()) || 0;
        var remaining = Math.max(0, prev - paid);
        $("#remaining_balance").val(remaining.toFixed(2));
        $("#disp_remaining_balance").text("&#8377; " + remaining.toFixed(2));
    }

    $("#paymentReceiptForm").submit(function(e) {
        e.preventDefault();

        if (!$("#client_id").val()) {
            alert("Please select a client.");
            return;
        }
        if (parseFloat($("#payment_amount").val()) <= 0) {
            alert("Please enter a valid payment amount.");
            return;
        }

        var btn = $("#btnSubmitReceipt");
        btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Generating Receipt...');

        var formData = $(this).serialize() + "&type=createPaymentReceipt";
        $.ajax({
            url: "payment_curd.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(resp) {
                if (resp && resp.status === "success") {
                    alert("Payment Receipt #" + resp.receipt_no + " generated successfully!");
                    window.location.href = "../paymentreport/view_receipt.php?id=" + resp.receipt_id;
                } else {
                    alert("Error creating receipt: " + (resp.message || "Unknown error"));
                    btn.prop("disabled", false).html('<i class="fa fa-check-circle"></i> Generate & Issue Receipt');
                }
            },
            error: function() {
                alert("Network error occurred.");
                btn.prop("disabled", false).html('<i class="fa fa-check-circle"></i> Generate & Issue Receipt');
            }
        });
    });
});
</script>



