<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$destList = $db->ExecuteQuery("SELECT D.*, S.State_Name FROM tbl_destinations D INNER JOIN tbl_states S ON S.State_Id = D.State_Id ORDER BY D.Destination_Name ASC");
$states = $db->ExecuteQuery("SELECT State_Id, State_Code, State_Name FROM tbl_states ORDER BY State_Name ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-map-marker text-primary"></i> City / Destination Master</h1>
        <span style="color:#64748b; font-size:13px;">Manage delivery cities, operational pincodes, ODA status, and door delivery charges</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addDestModal">
            <i class="fa fa-plus-circle"></i> Add New City / Destination
        </button>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Registered Destinations (<?php echo count($destList); ?>)</h3>
            <div class="pull-right">
                <input type="text" id="destSearch" class="form-control input-sm" placeholder="Search City, Code or Pincode..." style="width: 250px; display:inline-block;">
            </div>
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
                        <th style="text-align:right;">ODA Charge (₹)</th>
                        <th style="text-align:right;">Door Delivery (₹)</th>
                        <th width="120" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($destList) && count($destList) > 0) {
                        $i = 1;
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
                                <td style="text-align:right; font-weight:600; color:#dc2626;">₹ <?php echo number_format($d['ODA_Charge'], 2); ?></td>
                                <td style="text-align:right; font-weight:600;">₹ <?php echo number_format($d['Door_Delivery_Charge'], 2); ?></td>
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
                    <div class="col-sm-4 form-group">
                        <label>City Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dest_code" required placeholder="e.g. DEL, BOM, BLR">
                    </div>
                    <div class="col-sm-8 form-group">
                        <label>City / Destination Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dest_name" required placeholder="e.g. Delhi, Mumbai, Bangalore">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>State <span class="text-danger">*</span></label>
                        <select class="form-control" name="state_id" required>
                            <option value="">Select State</option>
                            <?php foreach ($states as $st) { ?>
                                <option value="<?php echo $st['State_Id']; ?>"><?php echo htmlspecialchars($st['State_Name']); ?> (<?php echo htmlspecialchars($st['State_Code']); ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Pincode (Optional)</label>
                        <input type="text" class="form-control" name="pincode" placeholder="e.g. 110001">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4 form-group">
                        <label>Is ODA Area?</label>
                        <select class="form-control" name="is_oda">
                            <option value="0">No (Standard)</option>
                            <option value="1">Yes (ODA Area)</option>
                        </select>
                    </div>
                    <div class="col-sm-4 form-group">
                        <label>ODA Charge (₹)</label>
                        <input type="number" step="0.01" class="form-control" name="oda_charge" value="0.00">
                    </div>
                    <div class="col-sm-4 form-group">
                        <label>Door Delivery (₹)</label>
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
    $("#destSearch").on("keyup", function() {
        var val = $(this).val().toLowerCase();
        $("#destTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

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