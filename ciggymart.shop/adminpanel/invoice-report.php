<?php
include('../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

$where = "WHERE 1=1";
if (!empty($_GET['branch_id'])) {
    $where .= " AND I.Branch_Id=" . intval($_GET['branch_id']);
}
if (!empty($_GET['client_id'])) {
    $where .= " AND I.Client_Id=" . intval($_GET['client_id']);
}
if (!empty($_GET['from_date'])) {
    $from = date('Y-m-d', strtotime($_GET['from_date']));
    $where .= " AND I.Bill_Date >= '$from'";
}
if (!empty($_GET['to_date'])) {
    $to = date('Y-m-d', strtotime($_GET['to_date']));
    $where .= " AND I.Bill_Date <= '$to'";
}
if (!empty($_GET['q'])) {
    $q = $db->escape(trim($_GET['q']));
    $where .= " AND (I.Invoice_No LIKE '%$q%' OR C.Client_Name LIKE '%$q%')";
}
if (!empty($_GET['status'])) {
    $status = $db->escape(trim($_GET['status']));
    $where .= " AND I.Payment_Status='$status'";
}

$sql = "SELECT I.*, DATE_FORMAT(I.Date_From,'%d-%m-%Y') AS 'Date_From_Fmt', DATE_FORMAT(I.Date_To,'%d-%m-%Y') AS 'Date_To_Fmt', DATE_FORMAT(I.Bill_Date,'%d-%m-%Y') AS 'Bill_Date_Fmt',
C.Client_Name, C.Client_Code, C.GSTIN_No, C.Email, B.Branch_Name
FROM tbl_invoices I
LEFT JOIN tbl_clients C ON I.Client_Id = C.Client_Id
LEFT JOIN tbl_branchs B ON B.Branch_Id = I.Branch_Id
$where
ORDER BY I.Invoice_Id DESC";

$invoices = $db->ExecuteQuery($sql);
$branches = $db->ExecuteQuery("SELECT Branch_Id, Branch_Code, Branch_Name FROM tbl_branchs WHERE Is_Active=1 ORDER BY Branch_Name ASC");
$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name FROM tbl_clients WHERE Is_Active=1 ORDER BY Client_Name ASC");
?>

<div class="modern-dashboard"><div class="module-header">
    <div>
        <h1><i class="fa fa-file-text-o text-primary"></i> Master Network Invoice & GST Billing Reports</h1>
        <span style="color:#64748b; font-size:13px;">Enterprise-wide billing report, tax analysis (CGST/SGST/IGST), outstanding balances, and client email dispatch</span>
    </div>
</div>

<!-- Filter Bar -->
    <div class="stat-card" style="display: block; padding: 24px; margin-bottom: 24px;">
        <form method="GET" action="" class="form-inline" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
            <div class="form-group custom-fg">
                <input type="text" class="form-control input-sm" name="q" placeholder="Search Invoice No / Client..." value="<?php echo htmlspecialchars(isset($_GET['q']) ? $_GET['q'] : ''); ?>" style="width:200px;">
            </div>

            <div class="form-group custom-fg">
                <select class="form-control input-sm" name="branch_id">
                    <option value="">-- All Branches --</option>
                    <?php foreach ($branches as $br) { ?>
                        <option value="<?php echo $br['Branch_Id']; ?>" <?php echo (isset($_GET['branch_id']) && $_GET['branch_id'] == $br['Branch_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($br['Branch_Name']); ?> (<?php echo htmlspecialchars($br['Branch_Code']); ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group custom-fg">
                <select class="form-control input-sm" name="client_id">
                    <option value="">-- All Clients --</option>
                    <?php foreach ($clients as $cl) { ?>
                        <option value="<?php echo $cl['Client_Id']; ?>" <?php echo (isset($_GET['client_id']) && $_GET['client_id'] == $cl['Client_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cl['Client_Name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group custom-fg">
                <select class="form-control input-sm" name="status">
                    <option value="">-- All Payment Status --</option>
                    <option value="Paid" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="Unpaid" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                    <option value="Partially Paid" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Partially Paid') ? 'selected' : ''; ?>>Partially Paid</option>
                </select>
            </div>

            <div class="form-group custom-fg">
                <input type="text" class="form-control input-sm datepicker" name="from_date" placeholder="From Bill Date" value="<?php echo htmlspecialchars(isset($_GET['from_date']) ? $_GET['from_date'] : ''); ?>" style="width:110px;">
            </div>

            <div class="form-group custom-fg">
                <input type="text" class="form-control input-sm datepicker" name="to_date" placeholder="To Bill Date" value="<?php echo htmlspecialchars(isset($_GET['to_date']) ? $_GET['to_date'] : ''); ?>" style="width:110px;">
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply Filters</button>
            <a href="invoice-report.php" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
        </form>
    </div>

    <!-- Invoices Table -->
<div class="stat-card" style="display: block;">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Network Invoices (<?php echo count($invoices); ?> Records)</h3>
        </div>
        <div class="table-responsive">
            <table class="modern-table table-compact">
                <thead>
                    <tr>
                        <th width="40">S.No</th>
                        <th>Invoice No</th>
                        <th>Branch</th>
                        <th>Client Name</th>
                        <th>Bill Date</th>
                        <th>Billing Period</th>
                        <th style="text-align:right;">Subtotal (&#8377;)</th>
                        <th style="text-align:right;">GST Tax (&#8377;)</th>
                        <th style="text-align:right;">Final Total (&#8377;)</th>
                        <th>Payment</th>
                        <th>Email Status</th>
                        <th width="140" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoices) && count($invoices) > 0) {
                        $i = 1;
                        foreach ($invoices as $inv) { 
                            $taxTotal = floatval($inv['IGST_Tax']) + floatval($inv['SGST_Tax']) + floatval($inv['CGST_Tax']);
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><strong><?php echo htmlspecialchars($inv['Invoice_No']); ?></strong></td>
                                <td><?php echo htmlspecialchars($inv['Branch_Name']); ?></td>
                                <td><?php echo htmlspecialchars($inv['Client_Name']); ?></td>
                                <td><?php echo $inv['Bill_Date_Fmt']; ?></td>
                                <td><span style="font-size:12px; color:#64748b;"><?php echo $inv['Date_From_Fmt']; ?> to <?php echo $inv['Date_To_Fmt']; ?></span></td>
                                <td style="text-align:right;">&#8377; <?php echo number_format($inv['Subtotal'], 2); ?></td>
                                <td style="text-align:right; color:#2563eb;">&#8377; <?php echo number_format($taxTotal, 2); ?></td>
                                <td style="text-align:right; font-weight:700; color:#0f172a;">&#8377; <?php echo number_format($inv['Final_Total_Amt'], 2); ?></td>
                                <td>
                                    <?php if ($inv['Payment_Status'] == 'Paid') { ?>
                                        <span class="badge-pill-modern badge-success">PAID</span>
                                    <?php } else if ($inv['Payment_Status'] == 'Partially Paid') { ?>
                                        <span class="badge-pill-modern badge-warning">PARTIAL</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-danger">UNPAID</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($inv['Email_Status'] == 'Sent') { ?>
                                        <span class="badge-pill-modern badge-success"><i class="fa fa-check"></i> Sent</span>
                                    <?php } else if ($inv['Email_Status'] == 'Failed') { ?>
                                        <span class="badge-pill-modern badge-danger"><i class="fa fa-times"></i> Failed</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-warning">Pending</span>
                                    <?php } ?>
                                </td>
                                <td align="center">
                                    <a href="<?php echo PATH_PDF_LINK; ?>/invoice/<?php echo $inv['Invoice_No']; ?>.pdf" target="_blank" class="btn btn-xs btn-danger" title="Download PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>
                                    <button type="button" class="btn btn-xs btn-primary resend-admin-inv-email" data-id="<?php echo $inv['Invoice_Id']; ?>" title="Resend Invoice Email to Client"><i class="fa fa-envelope"></i> Resend</button>
                                </td>
                            </tr>
                        <?php $i++; }
                    } else { ?>
                        <tr>
                            <td colspan="12" align="center" style="padding:30px; color:#94a3b8;">No invoices found matching the selected filters.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });

    $(".resend-admin-inv-email").click(function() {
        var btn = $(this);
        var id = btn.data("id");
        btn.html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: "invoice-report-curd.php",
            type: "POST",
            data: { type: "resend", id: id },
            success: function(resp) {
                alert("Invoice email dispatched successfully!");
                location.reload();
            },
            error: function() {
                alert("Error sending invoice email.");
                btn.html('<i class="fa fa-envelope"></i> Resend');
            }
        });
    });
});
</script>





