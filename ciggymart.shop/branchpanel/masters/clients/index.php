<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$branchId = isset($_SESSION['buser']) ? intval($_SESSION['buser']) : 0;
$clients = $db->ExecuteQuery("SELECT C.*, DATE_FORMAT(C.Joining_Date, '%d-%m-%Y') AS DateFormatted, D.Destination_Name 
FROM tbl_clients C
LEFT JOIN tbl_destinations D ON D.Destination_Id = C.Destination_Id
WHERE C.Branch_Id = $branchId 
ORDER BY C.Client_Id DESC");
?>
<script type="text/javascript" src="client.js"></script>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-users text-primary"></i> Client Master Directory</h1>
        <span style="color:#64748b; font-size:13px;">Manage branch corporate & retail client accounts, GSTIN, default charges, and rate tariffs</span>
    </div>
    <div>
        <a class="btn btn-primary btn-sm" href="add_client.php"><i class="fa fa-user-plus"></i> Add New Client</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Registered Clients (<?php echo count($clients); ?>)</h3>
            <div class="pull-right">
                <input type="text" id="clientSearchInput" class="form-control input-sm" placeholder="Search by name, code, GSTIN..." style="width:260px; display:inline-block;">
            </div>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table" id="clientDataTable">
                <thead>
                    <tr>
                        <th width="45">S.No</th>
                        <th>Code</th>
                        <th>Client / Company Name</th>
                        <th>City</th>
                        <th>Contact No</th>
                        <th>GSTIN No</th>
                        <th>Fuel %</th>
                        <th>Docket (₹)</th>
                        <th>Pickup (₹)</th>
                        <th>Status</th>
                        <th width="140" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if (!empty($clients) && count($clients) > 0) {
                    $i = 1;
                    foreach ($clients as $val) { ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($val['Client_Code']); ?></span></td>
                            <td>
                                <strong><?php echo htmlspecialchars($val['Client_Name']); ?></strong>
                                <?php if (!empty($val['Company_Name'])) { ?>
                                    <div style="font-size:11.5px; color:#64748b;"><?php echo htmlspecialchars($val['Company_Name']); ?></div>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars(!empty($val['Destination_Name']) ? $val['Destination_Name'] : '-'); ?></td>
                            <td><?php echo htmlspecialchars($val['Contact_No']); ?></td>
                            <td><code style="font-size:11.5px;"><?php echo !empty($val['GSTIN_No']) ? htmlspecialchars($val['GSTIN_No']) : 'N/A'; ?></code></td>
                            <td><?php echo floatval($val['Fuel_Surcharge']); ?>%</td>
                            <td>₹<?php echo number_format(floatval(isset($val['Docket_Charge']) ? $val['Docket_Charge'] : 0), 2); ?></td>
                            <td>₹<?php echo number_format(floatval(isset($val['Pickup_Charge']) ? $val['Pickup_Charge'] : 0), 2); ?></td>
                            <td>
                                <?php if ($val['Is_Active'] == 1) { ?>
                                    <button type="button" id="Block-<?php echo $val['Client_Id']; ?>" class="status btn btn-xs btn-success"><i class="fa fa-unlock"></i> Active</button>
                                <?php } else { ?>
                                    <button type="button" id="Unblock-<?php echo $val['Client_Id']; ?>" class="status btn btn-xs btn-danger"><i class="fa fa-lock"></i> Blocked</button>
                                <?php } ?>
                            </td>
                            <td align="center">
                                <a href="edit_client.php?id=<?php echo $val['Client_Id']; ?>" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Edit</a>
                                <button type="button" class="btn btn-danger btn-xs delete" id="<?php echo $val['Client_Id']; ?>" name="delete"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php $i++; }
                } else { ?>
                    <tr>
                        <td colspan="11" align="center" style="padding:25px; color:#94a3b8;">No clients registered for this branch. <a href="add_client.php">Add New Client</a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#clientSearchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#clientDataTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>
