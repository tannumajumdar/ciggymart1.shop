<?php
require('../../config.php');
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

// --- Filters ---
$where = "WHERE 1=1";
if (!empty($_GET['q'])) {
    $q = $db->escape(trim($_GET['q']));
    $where .= " AND (C.Consignment_No LIKE '%$q%' OR C.Consignee_Name LIKE '%$q%' OR C.Consignee_Mobile LIKE '%$q%' OR CL.Client_Name LIKE '%$q%')";
}
if (!empty($_GET['from_date'])) {
    $from = date('Y-m-d', strtotime($_GET['from_date']));
    $where .= " AND C.Date_Of_Submit >= '$from'";
}
if (!empty($_GET['to_date'])) {
    $to = date('Y-m-d', strtotime($_GET['to_date']));
    $where .= " AND C.Date_Of_Submit <= '$to'";
}
if (!empty($_GET['client_id'])) {
    $where .= " AND C.Client_id=" . intval($_GET['client_id']);
}
if (!empty($_GET['destination_id'])) {
    $where .= " AND C.Destination_Id=" . intval($_GET['destination_id']);
}

$sql = "SELECT C.*,
    DATE_FORMAT(C.Date_Of_Submit,'%d %b %Y') AS BookingDate,
    COALESCE(CL.Client_Name, 'Walk-in') AS Client_Name,
    COALESCE(D.Destination_Name, '-') AS Destination_Name
    FROM tbl_consignments C
    LEFT JOIN tbl_clients CL ON CL.Client_Id = C.Client_id
    LEFT JOIN tbl_destinations D ON D.Destination_Id = C.Destination_Id
    $where
    ORDER BY C.Consignment_Id DESC";

$consignments = $db->ExecuteQuery($sql);
$clients      = $db->ExecuteQuery("SELECT Client_Id, Client_Name FROM tbl_clients WHERE Is_Active=1 ORDER BY Client_Name ASC");
$destinations = $db->ExecuteQuery("SELECT Destination_Id, Destination_Name FROM tbl_destinations WHERE Is_Active=1 ORDER BY Destination_Name ASC");

$total    = is_array($consignments) ? count($consignments) : 0;
$totalAmt = 0;
$todayCount = 0;
if (!empty($consignments)) {
    foreach ($consignments as $r) {
        $totalAmt += floatval($r['Total_Amount']);
        if ($r['Date_Of_Submit'] == date('Y-m-d')) $todayCount++;
    }
}
?>
<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-cube" style="color:#2563eb;"></i> Consignments</h1>
        <span style="color:#64748b;font-size:13px;">Manage all bookings, AWB numbers, and shipment details</span>
    </div>
    <div>
        <a href="<?php echo PATH_ADMIN_LINK; ?>/consignments/add.php" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> New Consignment
        </a>
    </div>
</div>

<div style="padding:0 24px 40px 24px;">

    <!-- Summary Cards -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <div class="erp-card" style="padding:20px;display:flex;align-items:center;gap:16px;">
            <div class="stat-icon-box stat-icon-blue"><i class="fa fa-cube"></i></div>
            <div>
                <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Total Consignments</div>
                <div style="font-size:28px;font-weight:800;color:#0f172a;"><?php echo $total; ?></div>
            </div>
        </div>
        <div class="erp-card" style="padding:20px;display:flex;align-items:center;gap:16px;">
            <div class="stat-icon-box stat-icon-green"><i class="fa fa-money"></i></div>
            <div>
                <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Total Value</div>
                <div style="font-size:28px;font-weight:800;color:#0f172a;">&#8377;<?php echo number_format($totalAmt, 0); ?></div>
            </div>
        </div>
        <div class="erp-card" style="padding:20px;display:flex;align-items:center;gap:16px;">
            <div class="stat-icon-box stat-icon-amber"><i class="fa fa-calendar"></i></div>
            <div>
                <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Today's Bookings</div>
                <div style="font-size:28px;font-weight:800;color:#0f172a;"><?php echo $todayCount; ?></div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar" style="margin-bottom:20px;">
        <form method="GET" action="" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div class="form-group custom-fg" style="margin:0;">
                <label style="display:block;margin-bottom:4px;font-size:12px;font-weight:600;">Search</label>
                <input type="text" class="form-control input-sm" name="q"
                    placeholder="AWB No / Consignee / Mobile..."
                    value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
                    style="width:220px;">
            </div>
            <div class="form-group custom-fg" style="margin:0;">
                <label style="display:block;margin-bottom:4px;font-size:12px;font-weight:600;">Client</label>
                <select class="form-control input-sm" name="client_id" style="width:160px;">
                    <option value="">-- All Clients --</option>
                    <?php foreach ((array)$clients as $cl): ?>
                        <option value="<?php echo $cl['Client_Id']; ?>" <?php echo (isset($_GET['client_id']) && $_GET['client_id'] == $cl['Client_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cl['Client_Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group custom-fg" style="margin:0;">
                <label style="display:block;margin-bottom:4px;font-size:12px;font-weight:600;">Destination</label>
                <select class="form-control input-sm" name="destination_id" style="width:150px;">
                    <option value="">-- All Destinations --</option>
                    <?php foreach ((array)$destinations as $d): ?>
                        <option value="<?php echo $d['Destination_Id']; ?>" <?php echo (isset($_GET['destination_id']) && $_GET['destination_id'] == $d['Destination_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d['Destination_Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group custom-fg" style="margin:0;">
                <label style="display:block;margin-bottom:4px;font-size:12px;font-weight:600;">From Date</label>
                <input type="text" class="form-control input-sm datepicker" name="from_date"
                    placeholder="dd-mm-yyyy" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>" style="width:110px;">
            </div>
            <div class="form-group custom-fg" style="margin:0;">
                <label style="display:block;margin-bottom:4px;font-size:12px;font-weight:600;">To Date</label>
                <input type="text" class="form-control input-sm datepicker" name="to_date"
                    placeholder="dd-mm-yyyy" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>" style="width:110px;">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                <a href="<?php echo PATH_ADMIN_LINK; ?>/consignments/" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> All Consignments
                <span style="font-size:12px;font-weight:500;color:#64748b;margin-left:8px;">(<?php echo $total; ?> records)</span>
            </h3>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>AWB No.</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Consignee</th>
                        <th>Destination</th>
                        <th>Pcs</th>
                        <th>Wt (KG)</th>
                        <th style="text-align:right;">Amount (&#8377;)</th>
                        <th>Mode</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($consignments) && count($consignments) > 0):
                        $i = 1;
                        foreach ($consignments as $row):
                            $mode = ($row['Mode'] == 1) ? 'Surface' : 'Air';
                            $modeClass = ($row['Mode'] == 1) ? 'badge-info' : 'badge-primary';
                    ?>
                    <tr>
                        <td style="color:#94a3b8;font-size:12px;"><?php echo $i++; ?></td>
                        <td><strong style="color:#2563eb;font-family:monospace;"><?php echo htmlspecialchars($row['Consignment_No']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['BookingDate']); ?></td>
                        <td><?php echo htmlspecialchars($row['Client_Name']); ?></td>
                        <td>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($row['Consignee_Name'] ?? '-'); ?></div>
                            <?php if (!empty($row['Consignee_Mobile'])): ?>
                                <div style="font-size:11px;color:#64748b;"><?php echo htmlspecialchars($row['Consignee_Mobile']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['Destination_Name']); ?></td>
                        <td style="text-align:center;"><?php echo intval($row['No_Of_Pieces']); ?></td>
                        <td style="text-align:center;"><?php echo floatval($row['Chargeable_Weight']); ?></td>
                        <td style="text-align:right;font-weight:700;">&#8377;<?php echo number_format(floatval($row['Total_Amount']), 2); ?></td>
                        <td><span class="badge-pill-modern <?php echo $modeClass; ?>" style="font-size:10px;"><?php echo $mode; ?></span></td>
                        <td style="text-align:center;white-space:nowrap;">
                            <a href="view.php?id=<?php echo $row['Consignment_Id']; ?>" class="btn btn-xs btn-primary" title="View"><i class="fa fa-eye"></i> View</a>
                        </td>
                    </tr>
                    <?php endforeach;
                    else: ?>
                    <tr>
                        <td colspan="11" style="text-align:center;padding:48px;color:#94a3b8;">
                            <i class="fa fa-cube" style="font-size:32px;display:block;margin-bottom:12px;opacity:.4;"></i>
                            No consignments found.
                            <?php if (!empty(array_filter($_GET ?? []))): ?>
                                <br><a href="<?php echo PATH_ADMIN_LINK; ?>/consignments/" style="color:#2563eb;">Clear filters</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
});
</script>
<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>

