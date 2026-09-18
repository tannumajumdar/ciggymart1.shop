<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$stateList = $db->ExecuteQuery("SELECT S.*, Z.Zone_Code, Z.Zone_Name FROM tbl_states S
LEFT JOIN tbl_zones Z ON Z.Zone_Id = S.Zone_Id ORDER BY S.State_Name ASC");
?>
<script type="text/javascript" src="state.js"></script>

<script>
$(document).ready(function(){ 
	var zonelist = [
		<?php 
			$menu = $db->ExecuteQuery("SELECT Zone_Code FROM tbl_zones WHERE Is_Active=1");
			if (!empty($menu)) {
				foreach($menu as $val) {
					echo '"'.$val['Zone_Code']. '",';
				}
			}
		?>
	];
	
	$("#zone_code").autocomplete({
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( zonelist, function(item,index){
				return matcher.test(item);
			});
			responseFn( a );
		}
	}).on('autocompleteresponse autocompleteselect', function(e, ui){
		var formdata = new FormData();
		formdata.append('type', "getZone");
		formdata.append('zone_code', $("#zone_code").val());

		$.ajax({
		   type: "POST",
		   url: "state_curd.php",
		   data: formdata,
		   success: function(data){
			   $('#zonename').html(data);
		   },
		   cache: false,
		   contentType: false,
		   processData: false
		});
	});
});
</script>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-map text-primary"></i> States Master</h1>
        <span style="color:#64748b; font-size:13px;">Manage state mappings, state codes, and zonal associations</span>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">
    <div class="row">
        <div class="col-md-4">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-plus-circle"></i> Add State</h3>
                </div>
                <div class="erp-card-body">
                    <form role="form" id="insertState" method="post">
                        <div class="form-group custom-fg">
                            <label style="font-weight:600; font-size:13px; color:#334155;">State Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control input-sm" id="state_code" name="state_code" placeholder="e.g. CG, MH, DL, KA" required>
                        </div>
                        <div class="form-group custom-fg">
                            <label style="font-weight:600; font-size:13px; color:#334155;">State Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control input-sm" id="state_name" name="state_name" placeholder="e.g. Chhattisgarh, Maharashtra" required>
                        </div>
                        <div class="form-group custom-fg">
                            <label style="font-weight:600; font-size:13px; color:#334155;">Zone Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control input-sm" id="zone_code" name="zone_code" placeholder="Type zone code...">
                        </div>
                        <div class="form-group custom-fg" id="zonename">
                            <label style="font-weight:600; font-size:13px; color:#334155;">Zone Name</label>
                            <input type="text" class="form-control input-sm" id="zone_name" name="zone_name" placeholder="Auto-populated zone name" readonly>
                        </div>
                        <div style="margin-top:20px; display:flex; gap:10px;">
                            <button type="button" class="btn btn-primary btn-sm btn-block" id="submit"><i class="fa fa-save"></i> Save State</button>
                            <button type="reset" class="btn btn-default btn-sm" id="reset">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-list"></i> State Directory (<?php echo count($stateList); ?>)</h3>
                </div>
                <div class="erp-table-responsive">
                    <table class="erp-table" id="addedProducts">
                        <thead>
                            <tr>
                                <th width="50">S.No</th>
                                <th>State Code</th>
                                <th>State Name</th>
                                <th>Zone Code</th>
                                <th>Zone Name</th>
                                <th width="140" style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $i = 1;
                        if (!empty($stateList) && count($stateList) > 0) {
                            foreach($stateList as $val) { ?>
                            <tr>
                                <td><?php echo $i;?></td>
                                <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($val['State_Code']);?></span></td>
                                <td><strong><?php echo htmlspecialchars($val['State_Name']);?></strong></td>
                                <td><span class="badge-pill-modern badge-info"><?php echo htmlspecialchars($val['Zone_Code']);?></span></td>
                                <td><?php echo htmlspecialchars($val['Zone_Name']);?></td>
                                <td align="center">
                                    <button type="button" id="editbtn" class="btn btn-success btn-xs" onClick="window.location.href='edit_state.php?id=<?php echo $val['State_Id'];?>'"><i class="fa fa-edit"></i> Edit</button>
                                    <button type="button" class="btn btn-danger btn-xs delete" id="<?php echo $val['State_Id']; ?>" name="delete"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php $i++; } 
                        } else { ?>
                            <tr>
                                <td colspan="6" align="center" style="padding:20px; color:#94a3b8;">No states configured yet.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
