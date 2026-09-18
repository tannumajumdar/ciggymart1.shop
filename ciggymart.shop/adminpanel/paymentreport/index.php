<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();
$branchId = intval(0);

$where = "WHERE R.Branch_Id=$branchId";
if (!empty($_GET['client_id'])) {
    $where .= " AND R.Client_Id=" . intval($_GET['client_id']);
}
if (!empty($_GET['from_date'])) {
    $from = date('Y-m-d', strtotime($_GET['from_date']));
    $where .= " AND R.Payment_Date >= '$from'";
}
if (!empty($_GET['to_date'])) {
    $to = date('Y-m-d', strtotime($_GET['to_date']));
    $where .= " AND R.Payment_Date <= '$to'";
}
if (!empty($_GET['q'])) {
    $q = $db->escape(trim($_GET['q']));
    $where .= " AND (R.Receipt_No LIKE '%$q%' OR R.Reference_No LIKE '%$q%')";
}

$sql = "SELECT R.*, DATE_FORMAT(R.Payment_Date, '%d-%m-%Y') AS PayDate, C.Client_Name, C.Client_Code, C.Email 
        FROM tbl_payment_receipts R
        LEFT JOIN tbl_clients C ON C.Client_Id = R.Client_Id
        $where
        ORDER BY R.Receipt_Id DESC";

$receipts = $db->ExecuteQuery($sql);
$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name FROM tbl_clients WHERE Branch_Id=$branchId AND Is_Active=1 ORDER BY Client_Name ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-history text-primary"></i> Payment Receipts Report & History</h1>
        <span style="color:#64748b; font-size:13px;">View issued payment receipts, track email delivery status, download PDFs, print, and resend receipts</span>
    </div>
    <div>
        <a href="../payment/" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i> Create New Payment Receipt</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="" class="form-inline" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
            <div class="form-group custom-fg">
                <input type="text" class="form-control input-sm" name="q" placeholder="Search Receipt # or Ref..." value="<?php echo htmlspecialchars(isset($_GET['q']) ? $_GET['q'] : ''); ?>" style="width:200px;">
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
                <input type="text" class="form-control input-sm datepicker" name="from_date" placeholder="From Date" value="<?php echo htmlspecialchars(isset($_GET['from_date']) ? $_GET['from_date'] : ''); ?>" style="width:110px;">
            </div>

            <div class="form-group custom-fg">
                <input type="text" class="form-control input-sm datepicker" name="to_date" placeholder="To Date" value="<?php echo htmlspecialchars(isset($_GET['to_date']) ? $_GET['to_date'] : ''); ?>" style="width:110px;">
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply Filters</button>
            <a href="index.php" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
        </form>
    </div>

    <!-- Receipts Table -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Issued Receipts (<?php echo count($receipts); ?> Records)</h3>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th width="50">S.No</th>
                        <th>Receipt No</th>
                        <th>Date</th>
                        <th>Client Name</th>
                        <th>Payment Mode</th>
                        <th>Reference / Cheque</th>
                        <th style="text-align:right;">Paid Amount (&#8377;)</th>
                        <th style="text-align:right;">Balance Left (&#8377;)</th>
                        <th>Email Status</th>
                        <th width="180" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($receipts) && count($receipts) > 0) {
                        $i = 1;
                        foreach ($receipts as $r) { ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><strong><a href="view_receipt.php?id=<?php echo $r['Receipt_Id']; ?>"><?php echo htmlspecialchars($r['Receipt_No']); ?></a></strong></td>
                                <td><?php echo $r['PayDate']; ?></td>
                                <td><?php echo htmlspecialchars($r['Client_Name']); ?></td>
                                <td><span class="badge-pill-modern badge-info"><?php echo htmlspecialchars($r['Payment_Mode']); ?></span></td>
                                <td><?php echo htmlspecialchars(!empty($r['Reference_No']) ? $r['Reference_No'] : '-'); ?></td>
                                <td style="text-align:right; font-weight:700; color:#059669;">&#8377; <?php echo number_format($r['Payment_Amount'], 2); ?></td>
                                <td style="text-align:right; color:#64748b;">&#8377; <?php echo number_format($r['Remaining_Balance'], 2); ?></td>
                                <td>
                                    <?php if ($r['Email_Status'] == 'Sent') { ?>
                                        <span class="badge-pill-modern badge-success"><i class="fa fa-check"></i> Sent</span>
                                    <?php } else if ($r['Email_Status'] == 'Failed') { ?>
                                        <span class="badge-pill-modern badge-danger"><i class="fa fa-times"></i> Failed</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-warning">Pending</span>
                                    <?php } ?>
                                </td>
                                <td align="center">
                                    <a href="view_receipt.php?id=<?php echo $r['Receipt_Id']; ?>" class="btn btn-xs btn-default" title="View & Print Receipt"><i class="fa fa-eye"></i> View</a>
                                    <a href="<?php echo PATH_PDF_LINK; ?>/receipt/<?php echo $r['Receipt_No']; ?>.pdf" target="_blank" class="btn btn-xs btn-danger" title="Download PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>
                                    <button type="button" class="btn btn-xs btn-primary resend-email-btn" data-id="<?php echo $r['Receipt_Id']; ?>" title="Resend Email to Client"><i class="fa fa-envelope"></i></button>
                                </td>
                            </tr>
                        <?php $i++; }
                    } else { ?>
                        <tr>
                            <td colspan="10" align="center" style="padding:30px; color:#94a3b8;">No payment receipts found. <a href="../payment/">Create your first payment receipt</a></td>
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

    $(".resend-email-btn").click(function() {
        var btn = $(this);
        var id = btn.data("id");
        btn.html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: "view_receipt.php",
            type: "POST",
            data: { type: "resendReceiptEmail", id: id },
            success: function(resp) {
                alert("Receipt email sent successfully!");
                location.reload();
            },
            error: function() {
                alert("Failed to send email. Please check SMTP settings.");
                btn.html('<i class="fa fa-envelope"></i>');
            }
        });
    });
});
</script>



