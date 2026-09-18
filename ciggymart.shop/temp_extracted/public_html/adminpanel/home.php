<?php
require('../config.php');
include_once(PATH_ADMIN_INCLUDE.'/header.php');
require_once(PATH_LIBRARIES.'/classes/CourierBillingEngine.php');

$db = new DBConn();
$billing = new CourierBillingEngine($db);

// Fetch Admin Dashboard KPI statistics
$totBookingsRes = $db->ExecuteQuery("SELECT COUNT(*) AS total_count, COALESCE(SUM(Total_Amount),0) AS total_val FROM tbl_consignments");
$totBookings = !empty($totBookingsRes) ? intval($totBookingsRes[1]['total_count']) : 0;
$totBookingVal = !empty($totBookingsRes) ? floatval($totBookingsRes[1]['total_val']) : 0.0;

$todayBookingsRes = $db->ExecuteQuery("SELECT COUNT(*) AS today_count, COALESCE(SUM(Total_Amount),0) AS today_val FROM tbl_consignments WHERE Date_Of_Submit = CURDATE()");
$todayBookings = !empty($todayBookingsRes) ? intval($todayBookingsRes[1]['today_count']) : 0;
$todayBookingVal = !empty($todayBookingsRes) ? floatval($todayBookingsRes[1]['today_val']) : 0.0;

$totRevenueRes = $db->ExecuteQuery("SELECT COALESCE(SUM(Final_Total_Amt),0) AS total_rev, COUNT(*) AS inv_count FROM tbl_invoices");
$totRevenue = !empty($totRevenueRes) ? floatval($totRevenueRes[1]['total_rev']) : 0.0;
$totInvoices = !empty($totRevenueRes) ? intval($totRevenueRes[1]['inv_count']) : 0;

$totPaymentsRes = $db->ExecuteQuery("SELECT COALESCE(SUM(Payment_Amount),0) AS total_paid, COUNT(*) AS pay_count FROM tbl_payment_receipts");
$totPaid = !empty($totPaymentsRes) ? floatval($totPaymentsRes[1]['total_paid']) : 0.0;
$totReceipts = !empty($totPaymentsRes) ? intval($totPaymentsRes[1]['pay_count']) : 0;

$outstanding = max(0, $totRevenue - $totPaid);

$odaCountRes = $db->ExecuteQuery("SELECT COUNT(*) AS oda_count FROM tbl_consignments WHERE ODA_Charge > 0");
$odaCount = !empty($odaCountRes) ? intval($odaCountRes[1]['oda_count']) : 0;

$insCountRes = $db->ExecuteQuery("SELECT COUNT(*) AS ins_count FROM tbl_consignments WHERE Is_Insured = 1 OR Insured_Value > 0");
$insCount = !empty($insCountRes) ? intval($insCountRes[1]['ins_count']) : 0;

$branchesCountRes = $db->ExecuteQuery("SELECT COUNT(*) AS branch_count FROM tbl_branchs WHERE Is_Active=1");
$branchCount = !empty($branchesCountRes) ? intval($branchesCountRes[1]['branch_count']) : 0;

$clientsCountRes = $db->ExecuteQuery("SELECT COUNT(*) AS client_count FROM tbl_clients WHERE Is_Active=1");
$clientCount = !empty($clientsCountRes) ? intval($clientsCountRes[1]['client_count']) : 0;

// Recent 8 Bookings
$recentBookings = $db->ExecuteQuery("SELECT C.Consignment_Id, C.Consignment_No, DATE_FORMAT(C.Date_Of_Submit, '%d-%m-%Y') AS BookingDate, CL.Client_Name, D.Destination_Name, C.Total_Weight_In_KG, C.Total_Amount, C.ODA_Charge, C.Is_Insured, B.Branch_Name
FROM tbl_consignments C
LEFT JOIN tbl_clients CL ON CL.Client_Id = C.Client_Id
LEFT JOIN tbl_destinations D ON D.Destination_Id = C.Destination_Id
LEFT JOIN tbl_branchs B ON B.Branch_Id = C.Branch_Id
ORDER BY C.Consignment_Id DESC LIMIT 8");

// Recent 6 Invoices
$recentInvoices = $db->ExecuteQuery("SELECT I.Invoice_Id, I.Invoice_No, DATE_FORMAT(I.Invoice_Date, '%d-%m-%Y') AS InvDate, CL.Client_Name, I.Final_Total_Amt, I.Payment_Status, I.Email_Status, B.Branch_Name
FROM tbl_invoices I
LEFT JOIN tbl_clients CL ON CL.Client_Id = I.Client_Id
LEFT JOIN tbl_branchs B ON B.Branch_Id = I.Branch_Id
ORDER BY I.Invoice_Id DESC LIMIT 6");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-dashboard text-primary"></i> Admin ERP Dashboard</h1>
        <span style="color:#64748b; font-size:13px;">Overview of network shipments, billing performance, and operational activity</span>
    </div>
    <div>
        <a href="<?php echo PATH_ADMIN_LINK; ?>/settings.php" class="btn btn-default btn-sm"><i class="fa fa-cogs"></i> System Settings</a>
        <a href="<?php echo PATH_ADMIN_LINK; ?>/invoice-report.php" class="btn btn-primary btn-sm"><i class="fa fa-file-text"></i> Invoice Reports</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 30px 24px;">

    <!-- Quick Action Bar -->
    <div class="quick-actions-grid">
        <a href="<?php echo MASTERS_LINK_CONTROL?>/destination" class="quick-action-btn">
            <i class="fa fa-map-marker"></i>
            <span>City Master</span>
        </a>
        <a href="<?php echo MASTERS_LINK_CONTROL?>/oda" class="quick-action-btn">
            <i class="fa fa-compass"></i>
            <span>ODA Master</span>
        </a>
        <a href="<?php echo MASTERS_LINK_CONTROL?>/hsn" class="quick-action-btn">
            <i class="fa fa-barcode"></i>
            <span>HSN & GST</span>
        </a>
        <a href="<?php echo MASTERS_LINK_CONTROL?>/charges" class="quick-action-btn">
            <i class="fa fa-calculator"></i>
            <span>Charges Config</span>
        </a>
        <a href="<?php echo MASTERS_LINK_CONTROL?>/branch" class="quick-action-btn">
            <i class="fa fa-building"></i>
            <span>Branches (<?php echo $branchCount; ?>)</span>
        </a>
        <a href="<?php echo PATH_ADMIN_LINK?>/invoice-report.php" class="quick-action-btn">
            <i class="fa fa-file-pdf-o"></i>
            <span>Invoice Reports</span>
        </a>
    </div>

    <!-- Main KPI Cards -->
    <div class="stat-card-grid">
        <div class="stat-card">
            <div class="stat-icon-box stat-icon-blue">
                <i class="fa fa-cubes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Bookings</div>
                <div class="stat-value"><?php echo number_format($totBookings); ?></div>
                <div class="stat-sub">Value: ₹ <?php echo number_format($totBookingVal, 2); ?></div>
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
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">₹ <?php echo number_format($totRevenue, 2); ?></div>
                <div class="stat-sub"><?php echo $totInvoices; ?> Invoices Generated</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-amber">
                <i class="fa fa-hourglass-half"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Outstanding Amount</div>
                <div class="stat-value" style="color: #d97706;">₹ <?php echo number_format($outstanding, 2); ?></div>
                <div class="stat-sub">Collected: ₹ <?php echo number_format($totPaid, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- Secondary KPI Row -->
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
                <div class="stat-sub">With Transit Cover</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-blue">
                <i class="fa fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Clients</div>
                <div class="stat-value"><?php echo number_format($clientCount); ?></div>
                <div class="stat-sub">Corporate & Retail</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-green">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Receipts</div>
                <div class="stat-value"><?php echo number_format($totReceipts); ?></div>
                <div class="stat-sub">Payments Recorded</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Bookings Table -->
        <div class="col-md-7">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-truck text-primary"></i> Recent Network Bookings</h3>
                    <span class="badge badge-primary">Latest 8</span>
                </div>
                <div class="erp-table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>AWB No.</th>
                                <th>Branch</th>
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
                                        <td><strong><?php echo htmlspecialchars($b['Consignment_No']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($b['Branch_Name']); ?></td>
                                        <td><?php echo htmlspecialchars($b['Client_Name']); ?></td>
                                        <td><?php echo htmlspecialchars($b['Destination_Name']); ?></td>
                                        <td><?php echo $b['Total_Weight_In_KG']; ?> kg</td>
                                        <td>
                                            <?php if ($b['ODA_Charge'] > 0) { ?>
                                                <span class="badge-pill-modern badge-oda-yes" title="ODA Charge Applied">ODA</span>
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
                                    <td colspan="8" align="center" style="padding: 24px; color: #94a3b8;">No bookings recorded yet.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Invoices Table -->
        <div class="col-md-5">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-file-text-o text-success"></i> Recent Invoices</h3>
                    <a href="<?php echo PATH_ADMIN_LINK; ?>/invoice-report.php" class="btn btn-xs btn-default">View All</a>
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
        </div>
    </div>
</div>
