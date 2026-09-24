<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();
$odaList = $db->ExecuteQuery("SELECT O.*, Z.Zone_Code, Z.Zone_Name FROM tbl_oda_master O LEFT JOIN tbl_zones Z ON Z.Zone_Id = O.Zone_Id ORDER BY O.ODA_Id DESC");
$zones = $db->ExecuteQuery("SELECT Zone_Id, Zone_Code, Zone_Name FROM tbl_zones ORDER BY Zone_Name ASC");
$states = $db->ExecuteQuery("SELECT State_Id, State_Name FROM tbl_states ORDER BY State_Name ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-compass text-primary"></i> ODA (Out of Delivery Area) Master</h1>
        <span style="color:#64748b; font-size:13px;">Manage special delivery zones, remote pincodes, and automated ODA surcharge rates</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addOdaModal">
            <i class="fa fa-plus-circle"></i> Add New ODA Area
        </button>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Registered ODA Locations (<?php echo count($odaList); ?>)</h3>
            <div class="pull-right">
                <input type="text" id="odaSearchInput" class="form-control input-sm" placeholder="Search City or Pincode..." style="width: 240px; display:inline-block;">
            </div>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table" id="odaTable">
                <thead>
                    <tr>
                        <th width="60">S.No</th>
                        <th>City / Area</th>
                        <th>State</th>
                        <th>Pincode</th>
                        <th>Operational Zone</th>
                        <th style="text-align:right;">ODA Charge (&#8377;)</th>
                        <th>Status</th>
                        <th width="120" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($odaList) && count($odaList) > 0) {
                        $i = 1;
                        foreach ($odaList as $row) { ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['City']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['State']); ?></td>
                                <td><span class="badge-pill-modern badge-info"><?php echo htmlspecialchars($row['Pincode'] ? $row['Pincode'] : 'All Pincodes'); ?></span></td>
                                <td><?php echo htmlspecialchars($row['Zone_Name'] ? $row['Zone_Name'] : 'All Zones'); ?></td>
                                <td style="text-align:right; font-weight:700; color:#dc2626;">&#8377; <?php echo number_format($row['ODA_Charge'], 2); ?></td>
                                <td>
                                    <?php if ($row['Is_Active'] == 1) { ?>
                                        <span class="badge-pill-modern badge-success">Active</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-danger">Inactive</span>
                                    <?php } ?>
                                </td>
                                <td align="center">
                                    <button type="button" class="btn btn-xs btn-danger delete-oda" data-id="<?php echo $row['ODA_Id']; ?>"><i class="fa fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php $i++; }
                    } else { ?>
                        <tr>
                            <td colspan="8" align="center" style="padding:24px; color:#94a3b8;">No ODA records found. Click "Add New ODA Area" to create one.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal to Add ODA Area -->
<div class="modal fade" id="addOdaModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius: var(--radius-md); overflow:hidden;">
      <div class="modal-header" style="background:#0f172a; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title" style="margin:0; border:none; padding:0; color:#fff;"><i class="fa fa-compass"></i> Add ODA Area Master</h4>
      </div>
      <form id="addOdaForm">
          <div class="modal-body" style="padding: 20px;">
                <div class="form-group custom-fg">
                    <label>City / Location Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="city" id="oda_city" required placeholder="e.g. Leh, Siliguri Outskirts, Andaman">
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>State</label>
                        <select class="form-control" name="state" id="oda_state">
                            <option value="">Select State</option>
                            <?php foreach ($states as $st) { ?>
                                <option value="<?php echo htmlspecialchars($st['State_Name']); ?>"><?php echo htmlspecialchars($st['State_Name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Pincode (Optional / Specific)</label>
                        <input type="text" class="form-control" name="pincode" id="oda_pincode" placeholder="e.g. 194101">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Operational Zone</label>
                        <select class="form-control" name="zone_id" id="oda_zone_id">
                            <option value="0">All Zones</option>
                            <?php foreach ($zones as $z) { ?>
                                <option value="<?php echo $z['Zone_Id']; ?>"><?php echo htmlspecialchars($z['Zone_Name']); ?> (<?php echo htmlspecialchars($z['Zone_Code']); ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>ODA Surcharge Rate (&#8377;) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="oda_charge" id="oda_charge" value="150.00" required>
                    </div>
                </div>
          </div>
          <div class="modal-footer" style="background:#f8fafc;">
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save ODA Location</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Quick search filter
    $("#odaSearchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#odaTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Add ODA Ajax
    $("#addOdaForm").submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + "&type=addODA";
        $.ajax({
            url: "oda_curd.php",
            type: "POST",
            data: formData,
            success: function(response) {
                if (response == "1") {
                    location.reload();
                } else {
                    alert("Error saving ODA location: " + response);
                }
            }
        });
    });

    // Delete ODA
    $(".delete-oda").click(function() {
        if (confirm("Are you sure you want to delete this ODA location?")) {
            var id = $(this).data("id");
            $.ajax({
                url: "oda_curd.php",
                type: "POST",
                data: { type: "deleteODA", id: id },
                success: function(response) {
                    location.reload();
                }
            });
        }
    });
});
</script>

