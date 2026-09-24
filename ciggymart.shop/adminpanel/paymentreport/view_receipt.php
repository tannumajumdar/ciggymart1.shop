<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
require_once(PATH_LIBRARIES.'/classes/CourierBillingEngine.php');
require_once(PATH_LIBRARIES.'/classes/CourierEmailService.php');

$db = new DBConn();

// AJAX Resend Email Handler
if (isset($_POST['type']) && $_POST['type'] == "resendReceiptEmail") {
    $receiptId = intval($_POST['id']);
    $rRes = $db->ExecuteQuery("SELECT R.*, C.Client_Name, C.Email FROM tbl_payment_receipts R LEFT JOIN tbl_clients C ON C.Client_Id = R.Client_Id WHERE R.Receipt_Id=$receiptId");
    if (!empty($rRes)) {
        $r = $rRes[1];
        $pdfPath = ROOT . "/pdfmail/receipt/" . $r['Receipt_No'] . ".pdf";
        $emailService = new CourierEmailService($db);
        $ok = $emailService->sendPaymentReceiptEmail($r['Receipt_Id'], $r['Email'], $pdfPath, $r['Receipt_No'], $r['Payment_Amount'], $r['Client_Name'], $r['Remaining_Balance']);
        echo $ok ? "1" : "0";
    } else {
        echo "0";
    }
    exit();
}

$receiptId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT R.*, DATE_FORMAT(R.Payment_Date, '%d-%m-%Y') AS PayDate, 
        C.Client_Name, C.Company_Name, C.Address AS Client_Address, C.Contact_No AS Client_Contact, C.Email AS Client_Email, C.GSTIN_No AS Client_GSTIN,
        B.Branch_Name, B.Branch_Code, B.Address AS Branch_Address, B.Contact_No AS Branch_Contact, B.GSTIN AS Branch_GSTIN, B.PAN_No AS Branch_PAN,
        I.Invoice_No
        FROM tbl_payment_receipts R
        LEFT JOIN tbl_clients C ON C.Client_Id = R.Client_Id
        LEFT JOIN tbl_branchs B ON B.Branch_Id = R.Branch_Id
        LEFT JOIN tbl_invoices I ON I.Invoice_Id = R.Invoice_Id
        WHERE R.Receipt_Id=$receiptId";

$res = $db->ExecuteQuery($sql);

if (empty($res)) {
    echo "<div class='container'><div class='alert alert-danger'>Receipt record not found.</div></div>";
    exit();
}

$r = $res[1];
$amountWords = CourierBillingEngine::numberToWords($r['Payment_Amount']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt â€” <?php echo htmlspecialchars($r['Receipt_No']); ?></title>
    <link href="<?php echo PATH_CSS_LIBRARIES; ?>/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo PATH_CSS_LIBRARIES; ?>/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo PATH_CSS_LIBRARIES; ?>/modern_erp.css" rel="stylesheet" type="text/css" />
    <style>
        body { background: #f1f5f9; padding: 20px 0; }
        .receipt-card { max-width: 780px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); border: 1px solid #cbd5e1; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .receipt-card { box-shadow: none; border: 1px solid #000; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="container no-print" style="max-width: 780px; margin-bottom: 15px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <a href="index.php" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Receipts</a>
        <div>
            <button onclick="window.print();" class="btn btn-primary btn-sm"><i class="fa fa-print"></i> Print Receipt</button>
            <a href="<?php echo PATH_PDF_LINK; ?>/receipt/<?php echo $r['Receipt_No']; ?>.pdf" target="_blank" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf-o"></i> Download PDF</a>
        </div>
    </div>
</div>

<div class="receipt-card">
    <!-- Header -->
    <table width="100%" style="border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px;">
        <tr>
            <td width="60%">
                <h2 style="margin:0 0 5px 0; font-weight:800; color:#0f172a;"><?php echo htmlspecialchars($r['Branch_Name']); ?></h2>
                <div style="font-size:12.5px; color:#475569;">
                    <?php echo htmlspecialchars($r['Branch_Address']); ?><br>
                    <strong>GSTIN:</strong> <?php echo htmlspecialchars($r['Branch_GSTIN'] ? $r['Branch_GSTIN'] : '-'); ?> | <strong>PAN:</strong> <?php echo htmlspecialchars($r['Branch_PAN'] ? $r['Branch_PAN'] : '-'); ?><br>
                    <strong>Phone:</strong> <?php echo htmlspecialchars($r['Branch_Contact'] ? $r['Branch_Contact'] : '-'); ?>
                </div>
            </td>
            <td width="40%" align="right" style="vertical-align: top;">
                <span class="badge-pill-modern badge-success" style="font-size:14px; padding:6px 14px; margin-bottom:8px;">PAYMENT RECEIPT</span><br>
                <strong style="font-size:15px; color:#0f172a;"><?php echo htmlspecialchars($r['Receipt_No']); ?></strong><br>
                <span style="font-size:13px; color:#64748b;">Date: <strong><?php echo $r['PayDate']; ?></strong></span>
            </td>
        </tr>
    </table>

    <!-- Received From -->
    <table width="100%" style="margin-bottom: 25px; font-size: 13.5px;">
        <tr>
            <td width="55%" style="vertical-align: top;">
                <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700;">RECEIVED FROM</span><br>
                <strong style="font-size:16px; color:#0f172a;"><?php echo htmlspecialchars($r['Client_Name']); ?></strong><br>
                <?php if (!empty($r['Company_Name'])) { ?>
                    <div><?php echo htmlspecialchars($r['Company_Name']); ?></div>
                <?php } ?>
                <div><?php echo htmlspecialchars($r['Client_Address']); ?></div>
                <div style="margin-top:4px; font-size:12.5px; color:#64748b;">
                    <strong>GSTIN:</strong> <?php echo htmlspecialchars($r['Client_GSTIN'] ? $r['Client_GSTIN'] : '-'); ?> | <strong>Mobile:</strong> <?php echo htmlspecialchars($r['Client_Contact'] ? $r['Client_Contact'] : '-'); ?>
                </div>
            </td>
            <td width="45%" align="right" style="vertical-align: top; font-size:13px;">
                <strong>Payment Mode:</strong> <span class="badge-pill-modern badge-info"><?php echo htmlspecialchars($r['Payment_Mode']); ?></span><br>
                <strong>Ref / Transaction No:</strong> <?php echo htmlspecialchars($r['Reference_No'] ? $r['Reference_No'] : 'N/A'); ?><br>
                <?php if (!empty($r['Invoice_No'])) { ?>
                    <strong>Invoice Adjusted:</strong> Invoice #<?php echo htmlspecialchars($r['Invoice_No']); ?><br>
                <?php } else { ?>
                    <strong>Allocation:</strong> General On-Account<br>
                <?php } ?>
            </td>
        </tr>
    </table>

    <!-- Amount Banner -->
    <div style="background:#ecfdf5; border:1px solid #10b981; border-radius: var(--radius-md); padding:16px 20px; text-align:center; margin-bottom:25px;">
        <span style="font-size:12px; font-weight:700; color:#065f46; text-transform:uppercase; letter-spacing:0.5px;">Total Amount Received</span>
        <div style="font-size:32px; font-weight:800; color:#059669; margin:4px 0;">&#8377; <?php echo number_format($r['Payment_Amount'], 2); ?></div>
        <div style="font-size:13px; font-style:italic; color:#047857;">( <?php echo $amountWords; ?> )</div>
    </div>

    <!-- Ledger Details Table -->
    <table class="table table-bordered" style="margin-bottom: 25px; font-size:13.5px;">
        <tr>
            <td width="60%">Previous Total Outstanding Balance</td>
            <td width="40%" align="right">&#8377; <?php echo number_format($r['Previous_Balance'], 2); ?></td>
        </tr>
        <tr style="background:#f8fafc; font-weight:600;">
            <td>Amount Received in this Transaction</td>
            <td align="right" style="color:#059669;">- &#8377; <?php echo number_format($r['Payment_Amount'], 2); ?></td>
        </tr>
        <tr style="font-weight:700; font-size:15px; background:#fff;">
            <td>Remaining Balance Due</td>
            <td align="right" style="color:#dc2626;">&#8377; <?php echo number_format($r['Remaining_Balance'], 2); ?></td>
        </tr>
    </table>

    <?php if (!empty($r['Notes'])) { ?>
        <p style="font-size:13px; color:#475569;"><strong>Notes / Remarks:</strong> <?php echo htmlspecialchars($r['Notes']); ?></p>
    <?php } ?>

    <!-- Signatures -->
    <table width="100%" style="margin-top: 60px; font-size: 13px;">
        <tr>
            <td width="50%">
                <div style="border-top: 1px solid #94a3b8; width: 180px; text-align:center; padding-top:5px;">
                    Customer Signature
                </div>
            </td>
            <td width="50%" align="right">
                <div style="display:inline-block; border-top: 1px solid #94a3b8; width: 220px; text-align:center; padding-top:5px;">
                    For <strong><?php echo htmlspecialchars($r['Branch_Name']); ?></strong><br>
                    <span style="font-size:11px; color:#64748b;">(Authorized Signatory)</span>
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>


