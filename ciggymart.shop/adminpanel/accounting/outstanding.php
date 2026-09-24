<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

// We calculate outstanding by joining clients, their total invoices, and total payments.
$sql = "SELECT 
        C.Client_Id, C.Client_Code, C.Client_Name, C.Company_Name, C.Contact_No,
        COALESCE((SELECT SUM(Total_Amount) FROM tbl_invoices WHERE Client_Id = C.Client_Id), 0) AS Total_Billed,
        COALESCE((SELECT SUM(Amount_Received) FROM tbl_payment_receipts WHERE Customer_Id = C.Client_Id), 0) AS Total_Paid
        FROM tbl_clients C 
        WHERE C.Is_Active = 1
        HAVING (Total_Billed - Total_Paid) > 0
        ORDER BY (Total_Billed - Total_Paid) DESC";

$outstandingList = $db->ExecuteQuery($sql);

$totalOutstanding = 0;
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-clock-o text-danger"></i> Outstanding Balances Report</h1>
        <span style="color:#64748b; font-size:13px;">A master list of all clients who have pending balances across all generated invoices.</span>
    </div>
    <div>
        <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print Report</button>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Clients with Pending Balances</h3>
        </div>
        
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Client Code</th>
                        <th>Client / Company Name</th>
                        <th>Contact No</th>
                        <th style="text-align:right;">Total Billed (&#8377;)</th>
                        <th style="text-align:right;">Total Paid (&#8377;)</th>
                        <th style="text-align:right; font-weight:bold;">Pending Outstanding (&#8377;)</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($outstandingList)) {
                        foreach ($outstandingList as $row) { 
                            $balance = $row['Total_Billed'] - $row['Total_Paid'];
                            $totalOutstanding += $balance;
                    ?>
                        <tr>
                            <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($row['Client_Code']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($row['Client_Name']); ?></strong><br><small style="color:#64748b;"><?php echo htmlspecialchars($row['Company_Name']); ?></small></td>
                            <td><?php echo htmlspecialchars($row['Contact_No']); ?></td>
                            <td style="text-align:right;"><?php echo number_format($row['Total_Billed'], 2); ?></td>
                            <td style="text-align:right; color:#16a34a;"><?php echo number_format($row['Total_Paid'], 2); ?></td>
                            <td style="text-align:right; font-weight:bold; color:#dc2626;"><?php echo number_format($balance, 2); ?></td>
                            <td align="center">
                                <a href="ledger.php?client_id=<?php echo $row['Client_Id']; ?>" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> View Ledger</a>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else { ?>
                        <tr><td colspan="7" align="center" style="padding:24px; color:#16a34a; font-weight:bold;"><i class="fa fa-check-circle"></i> Excellent! No clients have any pending outstanding balances.</td></tr>
                    <?php } ?>
                </tbody>
                <?php if (!empty($outstandingList)) { ?>
                <tfoot style="background: #fef2f2; font-weight:bold;">
                    <tr>
                        <td colspan="5" align="right">TOTAL MARKET OUTSTANDING:</td>
                        <td style="text-align:right; font-size:16px; color:#dc2626;">&#8377; <?php echo number_format($totalOutstanding, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>
