<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$consignments = $db->ExecuteQuery("SELECT C.Consignment_Id, C.Consignment_No, C.Date_Of_Submit, C.Total_Amount, CL.Client_Name 
    FROM tbl_consignments C 
    LEFT JOIN tbl_clients CL ON CL.Client_Id = C.Client_Id 
    ORDER BY C.Consignment_Id DESC LIMIT 100");
?>

<div class="modern-dashboard">
    <div class="module-header">
        <div class="header-left">
            <h2>Consignment Reports</h2>
            <p>View latest dispatch records, statuses, and generated revenue.</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>AWB / Tracking No</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($consignments)): foreach($consignments as $c): ?>
                    <tr>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($c['Consignment_No']); ?></td>
                        <td><?php echo date('d M Y', strtotime($c['Date_Of_Submit'])); ?></td>
                        <td><?php echo htmlspecialchars($c['Client_Name'] ?? 'Walk-in'); ?></td>
                        <td class="fw-bold text-success">₹ <?php echo number_format($c['Total_Amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-delivered">
                                Dispatched
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center text-muted">No consignments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>

