<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
require_once(PATH_LIBRARIES.'/classes/CourierBillingEngine.php');
require_once(PATH_LIBRARIES.'/classes/CourierEmailService.php');

$db = new DBConn();
$branchId = isset($_SESSION['buser']) ? intval($_SESSION['buser']) : 1;
$billing = new CourierBillingEngine($db);
$emailService = new CourierEmailService($db);

// 1. Fetch Client Invoices & Balance
if (isset($_POST['type']) && $_POST['type'] == "getClientInvoicesAndBalance") {
    $clientId = intval($_POST['client_id']);
    $balance = $billing->getClientOutstanding($clientId);

    $invSql = "SELECT Invoice_Id, Invoice_No, Final_Total_Amt, Paid_Amount, Balance_Due, DATE_FORMAT(Invoice_Date,'%d-%m-%Y') AS InvDate 
               FROM tbl_invoices 
               WHERE Client_Id=$clientId AND (Payment_Status != 'Paid' OR Balance_Due > 0)
               ORDER BY Invoice_Id DESC";
    $invoices = $db->ExecuteQuery($invSql);

    $invList = [];
    if (!empty($invoices)) {
        foreach ($invoices as $row) {
            $invList[] = $row;
        }
    }

    echo json_encode([
        'status' => 'success',
        'balance' => $balance,
        'invoices' => $invList
    ]);
    exit();
}

// 2. Create Payment Receipt
if (isset($_POST['type']) && $_POST['type'] == "createPaymentReceipt") {
    $clientId = intval($_POST['client_id']);
    $paymentAmount = floatval($_POST['payment_amount']);
    $paymentMode = $db->escape(trim($_POST['payment_mode']));
    $referenceNo = $db->escape(trim($_POST['reference_no']));
    $invoiceId = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
    $rawDate = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('d-m-Y');
    $payDate = date('Y-m-d', strtotime($rawDate));
    $notes = $db->escape(trim($_POST['notes']));
    $previousBalance = floatval($_POST['previous_balance']);
    $remainingBalance = floatval($_POST['remaining_balance']);
    $sendEmail = !empty($_POST['send_email']) && $_POST['send_email'] == 1;

    // Fetch Branch Code
    $bRes = $db->ExecuteQuery("SELECT Branch_Code, Branch_Name, Address, Contact_No, GSTIN, PAN_No FROM tbl_branchs WHERE Branch_Id=$branchId");
    $branchCode = !empty($bRes) ? $bRes[1]['Branch_Code'] : 'BR';
    $branchInfo = !empty($bRes) ? $bRes[1] : [];

    // Generate Unique Receipt Number
    $datePart = date('Ymd');
    $seqRes = $db->ExecuteQuery("SELECT COUNT(*) AS today_count FROM tbl_payment_receipts WHERE Branch_Id=$branchId AND Payment_Date='$payDate'");
    $seq = (!empty($seqRes) ? intval($seqRes[1]['today_count']) : 0) + 1;
    $receiptNo = "REC-" . $branchCode . "-" . $datePart . "-" . sprintf('%04d', $seq);

    // Insert Receipt Record
    $fields = ['Receipt_No', 'Payment_Date', 'Client_Id', 'Branch_Id', 'Payment_Amount', 'Payment_Mode', 'Reference_No', 'Invoice_Id', 'Previous_Balance', 'Paid_Amount', 'Remaining_Balance', 'Notes', 'Email_Status'];
    $values = [$receiptNo, $payDate, $clientId, $branchId, $paymentAmount, $paymentMode, $referenceNo, $invoiceId, $previousBalance, $paymentAmount, $remainingBalance, $notes, ($sendEmail ? 'Pending' : 'Not Requested')];
    
    $inserted = $db->valInsert('tbl_payment_receipts', $fields, $values);
    if (!$inserted) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save payment receipt to database.']);
        exit();
    }

    $receiptIdRes = $db->ExecuteQuery("SELECT Receipt_Id FROM tbl_payment_receipts WHERE Receipt_No='$receiptNo' LIMIT 1");
    $receiptId = !empty($receiptIdRes) ? intval($receiptIdRes[1]['Receipt_Id']) : $db->getLastInsertId();

    // If allocated to invoice, update invoice
    if ($invoiceId > 0) {
        $invRes = $db->ExecuteQuery("SELECT Final_Total_Amt, Paid_Amount FROM tbl_invoices WHERE Invoice_Id=$invoiceId");
        if (!empty($invRes)) {
            $currentPaid = floatval($invRes[1]['Paid_Amount']) + $paymentAmount;
            $finalTotal = floatval($invRes[1]['Final_Total_Amt']);
            $newBal = max(0, $finalTotal - $currentPaid);
            $payStatus = ($newBal <= 0) ? 'Paid' : 'Partially Paid';
            $db->query("UPDATE tbl_invoices SET Paid_Amount=$currentPaid, Balance_Due=$newBal, Payment_Status='$payStatus' WHERE Invoice_Id=$invoiceId");
        }
    }

    // Client Info
    $cRes = $db->ExecuteQuery("SELECT Client_Name, Company_Name, Email, Address, Contact_No, GSTIN_No FROM tbl_clients WHERE Client_Id=$clientId");
    $client = !empty($cRes) ? $cRes[1] : [];
    $clientEmail = !empty($client['Email']) ? $client['Email'] : '';
    $clientName = !empty($client['Client_Name']) ? $client['Client_Name'] : 'Customer';

    // Generate PDF File
    $receiptDir = ROOT . '/pdfmail/receipt';
    if (!is_dir($receiptDir)) {
        @mkdir($receiptDir, 0777, true);
    }
    $pdfPath = $receiptDir . '/' . $receiptNo . '.pdf';

    // Build Receipt HTML
    $amountWords = CourierBillingEngine::numberToWords($paymentAmount);
    $htmlReceipt = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <title>Payment Receipt #$receiptNo</title>
        <style>
            body { font-family: 'Helvetica', Arial, sans-serif; font-size: 13px; color: #1e293b; padding: 20px; line-height: 1.5; }
            .receipt-box { border: 2px solid #0f172a; padding: 25px; border-radius: 8px; }
            .header-table { width: 100%; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 15px; }
            .title { font-size: 22px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
            .meta-table { width: 100%; margin-bottom: 20px; }
            .meta-table td { padding: 5px 0; vertical-align: top; }
            .amount-box { background: #ecfdf5; border: 1px solid #10b981; padding: 15px; border-radius: 6px; margin: 20px 0; text-align: center; }
            .amount-val { font-size: 26px; font-weight: bold; color: #059669; }
            .details-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
            .details-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
            .footer-sig { margin-top: 40px; }
        </style>
    </head>
    <body>
        <div class='receipt-box'>
            <table class='header-table'>
                <tr>
                    <td>
                        <div class='title'>" . (!empty($branchInfo['Branch_Name']) ? htmlspecialchars($branchInfo['Branch_Name']) : 'Keshri Express Logistics') . "</div>
                        <div>" . (!empty($branchInfo['Address']) ? htmlspecialchars($branchInfo['Address']) : '') . "</div>
                        <div>GSTIN: " . (!empty($branchInfo['GSTIN']) ? htmlspecialchars($branchInfo['GSTIN']) : '-') . " | PAN: " . (!empty($branchInfo['PAN_No']) ? htmlspecialchars($branchInfo['PAN_No']) : '-') . "</div>
                    </td>
                    <td align='right' style='vertical-align:top;'>
                        <h2 style='margin:0; color:#059669;'>PAYMENT RECEIPT</h2>
                        <strong>Receipt No:</strong> $receiptNo<br>
                        <strong>Date:</strong> " . date('d-m-Y', strtotime($payDate)) . "
                    </td>
                </tr>
            </table>

            <table class='meta-table'>
                <tr>
                    <td width='55%'>
                        <strong>Received From:</strong><br>
                        " . htmlspecialchars($clientName) . "<br>
                        " . (!empty($client['Company_Name']) ? htmlspecialchars($client['Company_Name']) . '<br>' : '') . "
                        " . (!empty($client['Address']) ? htmlspecialchars($client['Address']) . '<br>' : '') . "
                        Contact: " . (!empty($client['Contact_No']) ? htmlspecialchars($client['Contact_No']) : '-') . "
                    </td>
                    <td width='45%' align='right'>
                        <strong>Payment Mode:</strong> $paymentMode<br>
                        <strong>Ref / Cheque No:</strong> " . (!empty($referenceNo) ? $referenceNo : 'N/A') . "<br>
                        <strong>Allocated Invoice:</strong> " . ($invoiceId > 0 ? "Inv #$invoiceId" : "On Account") . "
                    </td>
                </tr>
            </table>

            <div class='amount-box'>
                <div style='font-size:12px; font-weight:bold; color:#065f46; text-transform:uppercase;'>Amount Received in Words</div>
                <div style='font-style:italic; margin-bottom:8px; font-size:14px;'>$amountWords</div>
                <div class='amount-val'>₹ " . number_format($paymentAmount, 2) . "</div>
            </div>

            <table class='details-table'>
                <tr>
                    <td>Previous Outstanding Balance:</td>
                    <td align='right'>₹ " . number_format($previousBalance, 2) . "</td>
                </tr>
                <tr>
                    <td>Amount Paid Now:</td>
                    <td align='right' style='font-weight:bold; color:#059669;'>₹ " . number_format($paymentAmount, 2) . "</td>
                </tr>
                <tr style='font-weight:bold; background:#f8fafc;'>
                    <td>Remaining Outstanding Balance:</td>
                    <td align='right' style='color:#dc2626;'>₹ " . number_format($remainingBalance, 2) . "</td>
                </tr>
            </table>

            " . (!empty($notes) ? "<p><strong>Notes:</strong> " . htmlspecialchars($notes) . "</p>" : "") . "

            <table class='footer-sig' width='100%'>
                <tr>
                    <td width='50%'>
                        Customer Signature
                    </td>
                    <td width='50%' align='right'>
                        For <strong>" . (!empty($branchInfo['Branch_Name']) ? htmlspecialchars($branchInfo['Branch_Name']) : 'Keshri Express') . "</strong><br><br><br>
                        Authorized Signatory
                    </td>
                </tr>
            </table>
        </div>
    </body>
    </html>";

    // Attempt DOMPDF generation if available
    if (file_exists(ROOT . "/dompdf/dompdf_config.inc.php")) {
        require_once(ROOT . "/dompdf/dompdf_config.inc.php");
        try {
            $dompdf = new DOMPDF();
            $dompdf->load_html($htmlReceipt);
            $dompdf->set_paper("A4", "portrait");
            $dompdf->render();
            file_put_contents($pdfPath, $dompdf->output());
        } catch (Exception $e) {
            // PDF fallback
        }
    }

    // Email Dispatch
    if ($sendEmail && !empty($clientEmail)) {
        $emailService->sendPaymentReceiptEmail($receiptId, $clientEmail, $pdfPath, $receiptNo, $paymentAmount, $clientName, $remainingBalance);
    }

    echo json_encode([
        'status' => 'success',
        'receipt_id' => $receiptId,
        'receipt_no' => $receiptNo,
        'message' => 'Payment receipt created successfully'
    ]);
    exit();
}
?>
