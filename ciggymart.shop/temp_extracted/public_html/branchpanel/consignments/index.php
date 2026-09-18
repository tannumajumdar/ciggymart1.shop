<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();
$branchId = intval($_SESSION['buser']);

// Build Filter Queries
$where = "WHERE CO.Branch_Id=$branchId";

if (!empty($_GET['client_id'])) {
    $where .= " AND CO.Client_Id=" . intval($_GET['client_id']);
}
if (!empty($_GET['dest_id'])) {
    $where .= " AND CO.Destination_Id=" . intval($_GET['dest_id']);
}
if (!empty($_GET['from_date'])) {
    $from = date('Y-m-d', strtotime($_GET['from_date']));
    $where .= " AND CO.Date_Of_Submit >= '$from'";
}
if (!empty($_GET['to_date'])) {
    $to = date('Y-m-d', strtotime($_GET['to_date']));
    $where .= " AND CO.Date_Of_Submit <= '$to'";
}
if (!empty($_GET['q'])) {
    $q = $db->escape(trim($_GET['q']));
    $where .= " AND (CO.Consignment_No LIKE '%$q%' OR CO.Consignee_Name LIKE '%$q%')";
}

$sql = "SELECT DATE_FORMAT(CO.Date_Of_Submit,'%d-%m-%Y') AS Date, CO.Consignment_Id, CO.Consignment_No, D.Destination_Name, 
CASE WHEN CO.Mode=1 THEN 'Dox' ELSE 'Non-Dox' END AS Mode, 
CASE WHEN CO.Send_By=1 THEN 'Surface' WHEN CO.Send_By=2 THEN 'Air' ELSE 'Urgent' END Send_By, 
CO.Total_Weight_In_KG, CO.Chargeable_Weight, CO.Total_Amount, CO.ODA_Charge, CO.Is_Insured,
C.Client_Name, C.Client_Code, CO.Consignee_Name 
FROM tbl_consignments CO
LEFT JOIN tbl_destinations D ON D.Destination_Id = CO.Destination_Id
LEFT JOIN tbl_clients C ON C.Client_id = CO.Client_id
$where
ORDER BY CO.Consignment_Id DESC";

$consignments = $db->ExecuteQuery($sql);

$clients = $db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name FROM tbl_clients WHERE Branch_Id=$branchId AND Is_Active=1 ORDER BY Client_Name ASC");
$destinations = $db->ExecuteQuery("SELECT Destination_Id, Destination_Name FROM tbl_destinations ORDER BY Destination_Name ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-barcode text-primary"></i> Consignment / AWB Bookings</h1>
        <span style="color:#64748b; font-size:13px;">Manage bookings, view dimensional weights, ODA surcharges, and edit shipments</span>
    </div>
    <div>
        <a href="add_consignment.php" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i> New Consignment Booking</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="" class="form-inline" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
            <div class="form-group">
                <input type="text" class="form-control input-sm" name="q" placeholder="Search AWB or Consignee..." value="<?php echo htmlspecialchars(isset($_GET['q']) ? $_GET['q'] : ''); ?>" style="width:200px;">
            </div>

            <div class="form-group">
                <select class="form-control input-sm" name="client_id">
                    <option value="">-- All Clients --</option>
                    <?php foreach ($clients as $cl) { ?>
                        <option value="<?php echo $cl['Client_Id']; ?>" <?php echo (isset($_GET['client_id']) && $_GET['client_id'] == $cl['Client_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cl['Client_Name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <select class="form-control input-sm" name="dest_id">
                    <option value="">-- All Destinations --</option>
                    <?php foreach ($destinations as $dst) { ?>
                        <option value="<?php echo $dst['Destination_Id']; ?>" <?php echo (isset($_GET['dest_id']) && $_GET['dest_id'] == $dst['Destination_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dst['Destination_Name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <input type="text" class="form-control input-sm datepicker" name="from_date" placeholder="From Date" value="<?php echo htmlspecialchars(isset($_GET['from_date']) ? $_GET['from_date'] : ''); ?>" style="width:110px;">
            </div>

            <div class="form-group">
                <input type="text" class="form-control input-sm datepicker" name="to_date" placeholder="To Date" value="<?php echo htmlspecialchars(isset($_GET['to_date']) ? $_GET['to_date'] : ''); ?>" style="width:110px;">
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply Filters</button>
            <a href="index.php" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
        </form>
    </div>

    <!-- Consignments Table Card -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Booked Consignments (<?php echo count($consignments); ?> Records)</h3>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th width="50">S.No</th>
                        <th>Booking Date</th>
                        <th>AWB Number</th>
                        <th>Client (Consignor)</th>
                        <th>Destination</th>
                        <th>Consignee</th>
                        <th>Mode / Send By</th>
                        <th>Actual Wt</th>
                        <th>Chargeable Wt</th>
                        <th>Special Surcharges</th>
                        <th style="text-align:right;">Amount (₹)</th>
                        <th width="120" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($consignments) && count($consignments) > 0) {
                        $i = 1;
                        foreach ($consignments as $row) { ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $row['Date']; ?></td>
                                <td><strong><a href="edit_consignment.php?id=<?php echo $row['Consignment_Id']; ?>"><?php echo htmlspecialchars($row['Consignment_No']); ?></a></strong></td>
                                <td><?php echo htmlspecialchars($row['Client_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Destination_Name']); ?></td>
                                <td><?php echo htmlspecialchars(!empty($row['Consignee_Name']) ? $row['Consignee_Name'] : '-'); ?></td>
                                <td>
                                    <span class="badge-pill-modern badge-primary"><?php echo $row['Mode']; ?></span>
                                    <span class="badge-pill-modern badge-info"><?php echo $row['Send_By']; ?></span>
                                </td>
                                <td><?php echo floatval($row['Total_Weight_In_KG']); ?> kg</td>
                                <td><strong><?php echo floatval($row['Chargeable_Weight'] > 0 ? $row['Chargeable_Weight'] : $row['Total_Weight_In_KG']); ?> kg</strong></td>
                                <td>
                                    <?php if ($row['ODA_Charge'] > 0) { ?>
                                        <span class="badge-pill-modern badge-oda-yes" title="ODA Charge: ₹<?php echo $row['ODA_Charge']; ?>">ODA (+₹<?php echo floatval($row['ODA_Charge']); ?>)</span>
                                    <?php } ?>
                                    <?php if ($row['Is_Insured'] == 1) { ?>
                                        <span class="badge-pill-modern badge-success" title="Transit Insured">INSURED</span>
                                    <?php } ?>
                                </td>
                                <td style="text-align:right; font-weight:700; color:#0f172a;">₹ <?php echo number_format($row['Total_Amount'], 2); ?></td>
                                <td align="center">
                                    <a href="edit_consignment.php?id=<?php echo $row['Consignment_Id']; ?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i> Edit</a>
                                    <button type="button" class="btn btn-xs btn-danger delete-con" data-id="<?php echo $row['Consignment_Id']; ?>"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php $i++; }
                    } else { ?>
                        <tr>
                            <td colspan="12" align="center" style="padding:30px; color:#94a3b8;">No consignments found for the selected criteria. <a href="add_consignment.php">Book a new consignment</a></td>
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

    $(".delete-con").click(function() {
        if (confirm("Are you sure you want to delete this consignment?")) {
            var id = $(this).data("id");
            $.ajax({
                url: "consignment_curd.php",
                type: "POST",
                data: { type: "delete", id: id },
                success: function(res) {
                    location.reload();
                }
            });
        }
    });
});
</script>
