<?php
require('../config.php');
include_once(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
require_once(PATH_LIBRARIES.'/classes/CourierBillingEngine.php');

$db = new DBConn();
$branchId = intval($_SESSION['buser']);
$billing = new CourierBillingEngine($db);

// Branch-specific statistics
$totBookingsRes = $db->ExecuteQuery("SELECT COUNT(*) AS total_count, COALESCE(SUM(Total_Amount),0) AS total_val FROM tbl_consignments WHERE Branch_Id=$branchId");
$totBookings = !empty($totBookingsRes) ? intval($totBookingsRes[1]['total_count']) : 0;
$totBookingVal = !empty($totBookingsRes) ? floatval($totBookingsRes[1]['total_val']) : 0.0;

$todayBookingsRes = $db->ExecuteQuery("SELECT COUNT(*) AS today_count, COALESCE(SUM(Total_Amount),0) AS today_val FROM tbl_consignments WHERE Branch_Id=$branchId AND Date_Of_Submit = CURDATE()");
$todayBookings = !empty($todayBookingsRes) ? intval($todayBookingsRes[1]['today_count']) : 0;
$todayBookingVal = !empty($todayBookingsRes) ? floatval($todayBookingsRes[1]['today_val']) : 0.0;

$totRevenueRes = $db->ExecuteQuery("SELECT COALESCE(SUM(Final_Total_Amt),0) AS total_rev, COUNT(*) AS inv_count FROM tbl_invoices WHERE Branch_Id=$branchId");
$totRevenue = !empty($totRevenueRes) ? floatval($totRevenueRes[1]['total_rev']) : 0.0;
$totInvoices = !empty($totRevenueRes) ? intval($totRevenueRes[1]['inv_count']) : 0;

$totPaymentsRes = $db->ExecuteQuery("SELECT COALESCE(SUM(Payment_Amount),0) AS total_paid, COUNT(*) AS pay_count FROM tbl_payment_receipts WHERE Branch_Id=$branchId");
$totPaid = !empty($totPaymentsRes) ? floatval($totPaymentsRes[1]['total_paid']) : 0.0;
$totReceipts = !empty($totPaymentsRes) ? intval($totPaymentsRes[1]['pay_count']) : 0;

$outstanding = max(0, $totRevenue - $totPaid);

$odaCountRes = $db->ExecuteQuery("SELECT COUNT(*) AS oda_count FROM tbl_consignments WHERE Branch_Id=$branchId AND ODA_Charge > 0");
$odaCount = !empty($odaCountRes) ? intval($odaCountRes[1]['oda_count']) : 0;

$insCountRes = $db->ExecuteQuery("SELECT COUNT(*) AS ins_count FROM tbl_consignments WHERE Branch_Id=$branchId AND (Is_Insured = 1 OR Insured_Value > 0)");
$insCount = !empty($insCountRes) ? intval($insCountRes[1]['ins_count']) : 0;

$clientsCountRes = $db->ExecuteQuery("SELECT COUNT(*) AS client_count FROM tbl_clients WHERE Branch_Id=$branchId AND Is_Active=1");
$clientCount = !empty($clientsCountRes) ? intval($clientsCountRes[1]['client_count']) : 0;

// Recent 8 Bookings for this branch
$recentBookings = $db->ExecuteQuery("SELECT C.Consignment_Id, C.Consignment_No, DATE_FORMAT(C.Date_Of_Submit, '%d-%m-%Y') AS BookingDate, CL.Client_Name, D.Destination_Name, C.Total_Weight_In_KG, C.Total_Amount, C.ODA_Charge, C.Is_Insured
FROM tbl_consignments C
LEFT JOIN tbl_clients CL ON CL.Client_Id = C.Client_Id
LEFT JOIN tbl_destinations D ON D.Destination_Id = C.Destination_Id
WHERE C.Branch_Id=$branchId
ORDER BY C.Consignment_Id DESC LIMIT 8");

// Recent 5 Invoices for this branch
$recentInvoices = $db->ExecuteQuery("SELECT I.Invoice_Id, I.Invoice_No, DATE_FORMAT(I.Invoice_Date, '%d-%m-%Y') AS InvDate, CL.Client_Name, I.Final_Total_Amt, I.Payment_Status
FROM tbl_invoices I
LEFT JOIN tbl_clients CL ON CL.Client_Id = I.Client_Id
WHERE I.Branch_Id=$branchId
ORDER BY I.Invoice_Id DESC LIMIT 5");

// Recent 5 Payment Receipts
$recentReceipts = $db->ExecuteQuery("SELECT R.Receipt_Id, R.Receipt_No, DATE_FORMAT(R.Payment_Date, '%d-%m-%Y') AS PayDate, CL.Client_Name, R.Payment_Amount, R.Payment_Mode
FROM tbl_payment_receipts R
LEFT JOIN tbl_clients CL ON CL.Client_Id = R.Client_Id
WHERE R.Branch_Id=$branchId
ORDER BY R.Receipt_Id DESC LIMIT 5");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-dashboard text-primary"></i> Branch Dashboard</h1>
        <span style="color:#64748b; font-size:13px;">Branch Operations, Consignment Booking, Billing, and Collections Summary</span>
    </div>
    <div>
        <a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/consignments/add_consignment.php" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i> New Booking</a>
        <a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/invoice/" class="btn btn-success btn-sm"><i class="fa fa-file-text"></i> Generate Invoice</a>
        <a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/payment/" class="btn btn-info btn-sm"><i class="fa fa-credit-card"></i> New Payment Receipt</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 30px 24px;">

    <!-- Quick Actions Grid -->
    <div class="quick-actions-grid">
        <a href="<?php echo BRANCH_PATH_ADMIN_LINK?>/consignments/add_consignment.php" class="quick-action-btn">
            <i class="fa fa-plus-circle text-primary"></i>
            <span>New Consignment</span>
        </a>
        <a href="<?php echo BRANCH_PATH_ADMIN_LINK?>/consignments/index.php" class="quick-action-btn">
            <i class="fa fa-list text-primary"></i>
            <span>Consignment List</span>
        </a>
        <a href="<?php echo BRANCH_PATH_ADMIN_LINK?>/invoice/" class="quick-action-btn">
            <i class="fa fa-file-text-o text-success"></i>
            <span>Generate Invoice</span>
        </a>
        <a href="<?php echo BRANCH_PATH_ADMIN_LINK?>/invoice/report.php" class="quick-action-btn">
            <i class="fa fa-file-pdf-o text-danger"></i>
            <span>Invoice Report</span>
        </a>
        <a href="<?php echo BRANCH_PATH_ADMIN_LINK?>/payment/" class="quick-action-btn">
            <i class="fa fa-credit-card text-info"></i>
            <span>Payment Receipt</span>
        </a>
        <a href="<?php echo BRANCH_MASTERS_LINK_CONTROL?>/clients" class="quick-action-btn">
            <i class="fa fa-users text-warning"></i>
            <span>Clients (<?php echo $clientCount; ?>)</span>
        </a>
    </div>

    <!-- Main KPI Cards -->
    <div class="stat-card-grid">
        <div class="stat-card">
            <div class="stat-icon-box stat-icon-blue">
                <i class="fa fa-cubes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Branch Bookings</div>
                <div class="stat-value"><?php echo number_format($totBookings); ?></div>
                <div class="stat-sub">Total Value: ₹ <?php echo number_format($totBookingVal, 2); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-green">
                <i class="fa fa-calendar-check-o"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Today's Bookings</div>
                <div class="stat-value"><?php echo number_format($todayBookings); ?></div>
                <div class="stat-sub">Today's Total: ₹ <?php echo number_format($todayBookingVal, 2); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-purple">
                <i class="fa fa-inr"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Billed Revenue</div>
                <div class="stat-value">₹ <?php echo number_format($totRevenue, 2); ?></div>
                <div class="stat-sub"><?php echo $totInvoices; ?> Invoices Generated</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-amber">
                <i class="fa fa-hourglass-half"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Outstanding Balance</div>
                <div class="stat-value" style="color:#d97706;">₹ <?php echo number_format($outstanding, 2); ?></div>
                <div class="stat-sub">Collected: ₹ <?php echo number_format($totPaid, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="stat-card-grid">
        <div class="stat-card">
            <div class="stat-icon-box stat-icon-rose">
                <i class="fa fa-map-pin"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">ODA Shipments</div>
                <div class="stat-value"><?php echo number_format($odaCount); ?></div>
                <div class="stat-sub">Out of Delivery Area</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-cyan">
                <i class="fa fa-shield"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Insured Shipments</div>
                <div class="stat-value"><?php echo number_format($insCount); ?></div>
                <div class="stat-sub">Transit Protection</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-green">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Payment Receipts</div>
                <div class="stat-value"><?php echo number_format($totReceipts); ?></div>
                <div class="stat-sub">Total Paid: ₹ <?php echo number_format($totPaid, 2); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-blue">
                <i class="fa fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Branch Clients</div>
                <div class="stat-value"><?php echo number_format($clientCount); ?></div>
                <div class="stat-sub">Registered Customers</div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row">
        <!-- Recent Bookings Table -->
        <div class="col-md-7">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-truck text-primary"></i> Recent Branch Bookings</h3>
                    <a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/consignments/index.php" class="btn btn-xs btn-primary"><i class="fa fa-list"></i> View All</a>
                </div>
                <div class="erp-table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>AWB No.</th>
                                <th>Client</th>
                                <th>Destination</th>
                                <th>Weight</th>
                                <th>Flags</th>
                                <th style="text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentBookings) && count($recentBookings) > 0) {
                                foreach ($recentBookings as $b) { ?>
                                    <tr>
                                        <td><?php echo $b['BookingDate']; ?></td>
                                        <td><strong><a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/consignments/edit_consignment.php?id=<?php echo $b['Consignment_Id']; ?>"><?php echo htmlspecialchars($b['Consignment_No']); ?></a></strong></td>
                                        <td><?php echo htmlspecialchars($b['Client_Name']); ?></td>
                                        <td><?php echo htmlspecialchars($b['Destination_Name']); ?></td>
                                        <td><?php echo $b['Total_Weight_In_KG']; ?> kg</td>
                                        <td>
                                            <?php if ($b['ODA_Charge'] > 0) { ?>
                                                <span class="badge-pill-modern badge-oda-yes" title="ODA Charge: ₹<?php echo $b['ODA_Charge']; ?>">ODA</span>
                                            <?php } ?>
                                            <?php if ($b['Is_Insured'] == 1) { ?>
                                                <span class="badge-pill-modern badge-info" title="Insured">INS</span>
                                            <?php } ?>
                                        </td>
                                        <td style="text-align:right; font-weight:600;">₹ <?php echo number_format($b['Total_Amount'], 2); ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="7" align="center" style="padding: 24px; color: #94a3b8;">No consignments booked yet. <a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/consignments/add_consignment.php">Book First Consignment</a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Invoices & Receipts Tabbed Card -->
        <div class="col-md-5">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-file-text-o text-success"></i> Recent Invoices</h3>
                    <a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/invoice/report.php" class="btn btn-xs btn-default">View All</a>
                </div>
                <div class="erp-table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th style="text-align:right;">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentInvoices) && count($recentInvoices) > 0) {
                                foreach ($recentInvoices as $inv) { ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($inv['Invoice_No']); ?></strong></td>
                                        <td><?php echo $inv['InvDate']; ?></td>
                                        <td><?php echo htmlspecialchars($inv['Client_Name']); ?></td>
                                        <td style="text-align:right; font-weight:600;">₹ <?php echo number_format($inv['Final_Total_Amt'], 2); ?></td>
                                        <td>
                                            <?php if ($inv['Payment_Status'] == 'Paid') { ?>
                                                <span class="badge-pill-modern badge-success">Paid</span>
                                            <?php } else { ?>
                                                <span class="badge-pill-modern badge-warning">Unpaid</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="5" align="center" style="padding: 24px; color: #94a3b8;">No invoices generated yet.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Receipts Card -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-credit-card text-info"></i> Recent Payments</h3>
                    <a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/paymentreport/" class="btn btn-xs btn-default">View All</a>
                </div>
                <div class="erp-table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Receipt No</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Mode</th>
                                <th style="text-align:right;">Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentReceipts) && count($recentReceipts) > 0) {
                                foreach ($recentReceipts as $rec) { ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($rec['Receipt_No']); ?></strong></td>
                                        <td><?php echo $rec['PayDate']; ?></td>
                                        <td><?php echo htmlspecialchars($rec['Client_Name']); ?></td>
                                        <td><span class="badge-pill-modern badge-info"><?php echo htmlspecialchars($rec['Payment_Mode']); ?></span></td>
                                        <td style="text-align:right; font-weight:600; color:#059669;">₹ <?php echo number_format($rec['Payment_Amount'], 2); ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="5" align="center" style="padding: 20px; color: #94a3b8;">No payment receipts created yet.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
