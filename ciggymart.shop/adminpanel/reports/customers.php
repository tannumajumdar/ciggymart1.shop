<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// Fetch top customers by revenue
$customerStats = $db->ExecuteQuery("SELECT C.Client_Id, C.Client_Name, C.Contact_No, COUNT(CN.Consignment_Id) as Total_Shipments, SUM(CN.Total_Amount) as Total_Revenue 
    FROM tbl_clients C 
    LEFT JOIN tbl_consignments CN ON CN.Client_Id = C.Client_Id 
    GROUP BY C.Client_Id 
    ORDER BY Total_Revenue DESC");
?>

<div class="modern-dashboard">
    <div class="module-header">
        <div class="header-left">
            <h2>Customer Analytics</h2>
            <p>View your top clients by shipment volume and total revenue.</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Client Name</th>
                        <th>Contact</th>
                        <th>Total Shipments</th>
                        <th>Total Revenue Generated</th>
                        <th>Avg Value per Shipment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($customerStats)): 
                        $rank = 1;
                        foreach($customerStats as $c): 
                            $shipments = intval($c['Total_Shipments']);
                            $revenue = floatval($c['Total_Revenue']);
                            $avg = $shipments > 0 ? ($revenue / $shipments) : 0;
                    ?>
                    <tr>
                        <td>#<?php echo $rank++; ?></td>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($c['Client_Name']); ?></td>
                        <td><?php echo htmlspecialchars($c['Contact_No'] ? $c['Contact_No'] : '-'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo $shipments; ?></span></td>
                        <td class="fw-bold text-success">₹ <?php echo number_format($revenue, 2); ?></td>
                        <td>₹ <?php echo number_format($avg, 2); ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No customer data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>

