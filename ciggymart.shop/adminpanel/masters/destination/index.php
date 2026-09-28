<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// Search and paginate on the server: the master holds ~22k pincodes.
$perPage = 50;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$where = '';
if ($q !== '') {
    // Every word must match some column, so "New Delhi (Delhi) - 110017"
    // (the label shown in the consignment dropdown) finds that pincode.
    $conds = array();
    foreach (preg_split('/[^A-Za-z0-9]+/', $q, -1, PREG_SPLIT_NO_EMPTY) as $word) {
        $w = $db->escape($word);
        $conds[] = "(D.Destination_Name LIKE '%$w%' OR D.Destination_Code LIKE '$w%' OR D.Pincode LIKE '$w%' OR S.State_Name LIKE '%$w%')";
    }
    if (!empty($conds)) {
        $where = 'WHERE ' . implode(' AND ', $conds);
    }
}
$countRes = $db->ExecuteQuery("SELECT COUNT(*) AS Total FROM tbl_destinations D INNER JOIN tbl_states S ON S.State_Id = D.State_Id $where");
$totalRows = !empty($countRes) ? intval($countRes[1]['Total']) : 0;
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$destList = $db->ExecuteQuery("SELECT D.*, S.State_Name FROM tbl_destinations D INNER JOIN tbl_states S ON S.State_Id = D.State_Id $where ORDER BY D.Destination_Name ASC, D.Pincode ASC LIMIT $offset, $perPage");
function destPageUrl($p, $q) {
    return 'index.php?' . http_build_query(array_filter(array('q' => $q, 'page' => $p > 1 ? $p : null)));
}
$states = $db->ExecuteQuery("SELECT State_Id, State_Code, State_Name FROM tbl_states ORDER BY State_Name ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-map-marker text-primary"></i> City / Destination Master</h1>
        <span style="color:#64748b; font-size:13px;">Manage delivery cities, operational pincodes, ODA status, and door delivery charges</span>
    </div>
        <div>
        <a href="import.php" class="btn btn-success btn-sm" style="margin-right: 8px;"><i class="fa fa-upload"></i> Bulk Import Pincodes</a>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addDestModal">
            <i class="fa fa-plus-circle"></i> Add New City / Destination
        </button>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Registered Destinations (<?php echo number_format($totalRows); ?>)</h3>
            <form method="get" class="pull-right" style="margin:0;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control input-sm" placeholder="Search City, Code, State or Pincode..." style="width: 250px; display:inline-block;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i></button>
                <?php if ($q !== '') { ?><a href="index.php" class="btn btn-default btn-sm">Clear</a><?php } ?>
            </form>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table" id="destTable">
                <thead>
                    <tr>
                        <th width="60">S.No</th>
                        <th>Dest Code</th>
                        <th>City / Destination Name</th>
                        <th>State</th>
                        <th>Pincode</th>
                        <th>ODA Flag</th>
                        <th style="text-align:right;">ODA Charge (&#8377;)</th>
                        <th style="text-align:right;">Door Delivery (&#8377;)</th>
                        <th width="120" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($destList) && count($destList) > 0) {
                        $i = $offset + 1;
                        foreach ($destList as $d) { ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($d['Destination_Code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($d['Destination_Name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($d['State_Name']); ?></td>
                                <td><span class="badge-pill-modern badge-info"><?php echo htmlspecialchars(!empty($d['Pincode']) ? $d['Pincode'] : '-'); ?></span></td>
                                <td>
                                    <?php if (!empty($d['Is_ODA']) && $d['Is_ODA'] == 1) { ?>
                                        <span class="badge-pill-modern badge-oda-yes">YES</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-oda-no">NO</span>
                                    <?php } ?>
                                </td>
                                <td style="text-align:right; font-weight:600; color:#dc2626;">&#8377; <?php echo number_format($d['ODA_Charge'], 2); ?></td>
                                <td style="text-align:right; font-weight:600;">&#8377; <?php echo number_format($d['Door_Delivery_Charge'], 2); ?></td>
                                <td align="center">
                                    <a href="edit_destination.php?id=<?php echo $d['Destination_Id']; ?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i> Edit</a>
                                    <button type="button" class="btn btn-xs btn-danger delete-dest" data-id="<?php echo $d['Destination_Id']; ?>"><i class="fa fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php $i++; }
                    } else { ?>
                        <tr>
                            <td colspan="9" align="center" style="padding:24px; color:#94a3b8;">No destinations found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1) { ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; font-size:13px; color:#64748b;">
            <span>Showing <?php echo $offset + 1; ?>&ndash;<?php echo min($offset + $perPage, $totalRows); ?> of <?php echo number_format($totalRows); ?></span>
            <div>
                <?php if ($page > 1) { ?>
                    <a href="<?php echo destPageUrl(1, $q); ?>" class="btn btn-default btn-xs">&laquo; First</a>
                    <a href="<?php echo destPageUrl($page - 1, $q); ?>" class="btn btn-default btn-xs">&lsaquo; Prev</a>
                <?php } ?>
                <span style="margin:0 8px;">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                <?php if ($page < $totalPages) { ?>
                    <a href="<?php echo destPageUrl($page + 1, $q); ?>" class="btn btn-default btn-xs">Next &rsaquo;</a>
                    <a href="<?php echo destPageUrl($totalPages, $q); ?>" class="btn btn-default btn-xs">Last &raquo;</a>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!-- Modal to Add Destination -->
<div class="modal fade" id="addDestModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius: var(--radius-md); overflow:hidden;">
      <div class="modal-header" style="background:#0f172a; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title" style="margin:0; border:none; padding:0; color:#fff;"><i class="fa fa-map-marker"></i> Add New Destination</h4>
      </div>
      <form id="addDestForm">
          <div class="modal-body" style="padding: 20px;">
                <div class="row">
                    <div class="col-sm-4 form-group custom-fg">
                        <label>City Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dest_code" required placeholder="e.g. DEL, BOM, BLR">
                    </div>
                    <div class="col-sm-8 form-group custom-fg">
                        <label>City / Destination Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dest_name" required placeholder="e.g. Delhi, Mumbai, Bangalore">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>State <span class="text-danger">*</span></label>
                        <select class="form-control" name="state_id" required>
                            <option value="">Select State</option>
                            <?php foreach ($states as $st) { ?>
                                <option value="<?php echo $st['State_Id']; ?>"><?php echo htmlspecialchars($st['State_Name']); ?> (<?php echo htmlspecialchars($st['State_Code']); ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Pincode (Optional)</label>
                        <input type="text" class="form-control" name="pincode" placeholder="e.g. 110001">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4 form-group custom-fg">
                        <label>Is ODA Area?</label>
                        <select class="form-control" name="is_oda">
                            <option value="0">No (Standard)</option>
                            <option value="1">Yes (ODA Area)</option>
                        </select>
                    </div>
                    <div class="col-sm-4 form-group custom-fg">
                        <label>ODA Charge (&#8377;)</label>
                        <input type="number" step="0.01" class="form-control" name="oda_charge" value="0.00">
                    </div>
                    <div class="col-sm-4 form-group custom-fg">
                        <label>Door Delivery (&#8377;)</label>
                        <input type="number" step="0.01" class="form-control" name="door_charge" value="0.00">
                    </div>
                </div>
          </div>
          <div class="modal-footer" style="background:#f8fafc;">
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Destination</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $("#addDestForm").submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + "&type=addDestination";
        $.ajax({
            url: "destination_curd.php",
            type: "POST",
            data: formData,
            success: function(res) {
                if (res == "1") {
                    location.reload();
                } else {
                    alert("Failed to save destination. Code or Name might already exist.");
                }
            }
        });
    });

    $(".delete-dest").click(function() {
        if (confirm("Are you sure you want to delete this destination?")) {
            var id = $(this).data("id");
            $.ajax({
                url: "destination_curd.php",
                type: "POST",
                data: { type: "delete", Destination_Id: id },
                success: function(res) {
                    location.reload();
                }
            });
        }
    });
});
</script>

