<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name, Company_Name FROM tbl_clients WHERE Is_Active=1 ORDER BY Client_Name ASC");

$selectedClientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$ledgerEntries = [];
$clientInfo = null;

if ($selectedClientId > 0) {
    $clientInfo = $db->ExecuteQuery("SELECT * FROM tbl_clients WHERE Client_Id=$selectedClientId");
    if(!empty($clientInfo)) {
        $clientInfo = $clientInfo[1];
    }
    // For ledger, we'll dynamically build the running balance based on actual invoices and payments,
    // since the original ledger entries weren't being created historically.
    // Fetch all Invoices for this client
    $invoices = $db->ExecuteQuery("SELECT Invoice_Id AS Ref_Id, Invoice_Date AS Date, 'Invoice' AS Type, Invoice_No AS Ref_No, Total_Amount AS Debit, 0 AS Credit FROM tbl_invoices WHERE Client_Id=$selectedClientId");
    
    // Fetch all Payments for this client
    $payments = $db->ExecuteQuery("SELECT Receipt_Id AS Ref_Id, Receipt_Date AS Date, 'Payment' AS Type, Reference_No AS Ref_No, 0 AS Debit, Amount_Received AS Credit FROM tbl_payment_receipts WHERE Customer_Id=$selectedClientId");
    
    // Merge and sort
    if(!empty($invoices)) {
        foreach($invoices as $inv) $ledgerEntries[] = $inv;
    }
    if(!empty($payments)) {
        foreach($payments as $pay) $ledgerEntries[] = $pay;
    }
    
    // Sort by Date ascending
    usort($ledgerEntries, function($a, $b) {
        return strtotime($a['Date']) - strtotime($b['Date']);
    });
}
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-address-book text-primary"></i> Customer Ledger</h1>
        <span style="color:#64748b; font-size:13px;">View complete statement of account, invoices billed, and payments received</span>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">
    
    <div class="erp-card" style="margin-bottom: 24px;">
        <div class="erp-card-body">
            <form method="GET" class="form-inline" style="display:flex; align-items:center; gap: 15px;">
                <label>Select Customer:</label>
                <select name="client_id" class="form-control" style="width: 300px;" required>
                    <option value="">-- Choose --</option>
                    <?php foreach ($clients as $cl) { ?>
                        <option value="<?php echo $cl['Client_Id']; ?>" <?php if($selectedClientId == $cl['Client_Id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cl['Client_Name'].' - '.$cl['Company_Name']); ?>
                        </option>
                    <?php } ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> View Ledger</button>
            </form>
        </div>
    </div>

    <?php if ($selectedClientId > 0) { ?>
        <div class="erp-card">
            <div class="erp-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3 class="erp-card-title"><i class="fa fa-list"></i> Statement of Account: <?php echo htmlspecialchars($clientInfo['Company_Name']); ?></h3>
                <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
            </div>
            
            <div class="erp-table-responsive">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction Type</th>
                            <th>Reference / No.</th>
                            <th style="text-align:right;">Debit (&#8377;) [Billed]</th>
                            <th style="text-align:right;">Credit (&#8377;) [Received]</th>
                            <th style="text-align:right; font-weight:bold;">Running Balance (&#8377;)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $runningBal = 0;
                        $totDebit = 0;
                        $totCredit = 0;
                        if (!empty($ledgerEntries)) {
                            foreach ($ledgerEntries as $entry) { 
                                $runningBal += $entry['Debit'] - $entry['Credit'];
                                $totDebit += $entry['Debit'];
                                $totCredit += $entry['Credit'];
                        ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($entry['Date'])); ?></td>
                                <td><span class="badge-pill-modern <?php echo ($entry['Type'] == 'Invoice') ? 'badge-primary' : 'badge-success'; ?>"><?php echo $entry['Type']; ?></span></td>
                                <td><?php echo htmlspecialchars($entry['Ref_No']); ?></td>
                                <td style="text-align:right; color:#dc2626;"><?php echo $entry['Debit'] > 0 ? number_format($entry['Debit'], 2) : '-'; ?></td>
                                <td style="text-align:right; color:#16a34a;"><?php echo $entry['Credit'] > 0 ? number_format($entry['Credit'], 2) : '-'; ?></td>
                                <td style="text-align:right; font-weight:bold;"><?php echo number_format($runningBal, 2); ?></td>
                            </tr>
                        <?php 
                            }
                        } else { ?>
                            <tr><td colspan="6" align="center" style="padding:24px; color:#94a3b8;">No transactions found for this customer.</td></tr>
                        <?php } ?>
                    </tbody>
                    <?php if (!empty($ledgerEntries)) { ?>
                    <tfoot style="background: #f1f5f9; font-weight:bold;">
                        <tr>
                            <td colspan="3" align="right">TOTALS:</td>
                            <td style="text-align:right; color:#dc2626;">&#8377; <?php echo number_format($totDebit, 2); ?></td>
                            <td style="text-align:right; color:#16a34a;">&#8377; <?php echo number_format($totCredit, 2); ?></td>
                            <td style="text-align:right; font-size:15px;">&#8377; <?php echo number_format($runningBal, 2); ?></td>
                        </tr>
                    </tfoot>
                    <?php } ?>
                </table>
            </div>
        </div>
    <?php } ?>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>
