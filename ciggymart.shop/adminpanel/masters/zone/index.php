<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$res = $db->ExecuteQuery("SELECT * FROM tbl_zones ORDER BY Zone_Id ASC");
?>
<script type="text/javascript" src="zone.js"></script>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-globe text-primary"></i> Operational Zones Master</h1>
        <span style="color:#64748b; font-size:13px;">Define regional courier shipping zones (North, South, East, West, Central, Special Zones)</span>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">
    <div class="row">
        <div class="col-md-4">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-plus-circle"></i> Add Operational Zone</h3>
                </div>
                <div class="erp-card-body">
                    <form role="form" id="insertZone" method="post">
                        <div class="form-group custom-fg">
                            <label style="font-weight:600; font-size:13px; color:#334155;">Zone Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control input-sm" id="zone_code" name="zone_code" placeholder="e.g. CZ, NZ, SZ, EZ" required>
                        </div>
                        <div class="form-group custom-fg">
                            <label style="font-weight:600; font-size:13px; color:#334155;">Zone Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control input-sm" id="zone_name" name="zone_name" placeholder="e.g. Central Zone, North Zone" required>
                        </div>
                        <div style="margin-top:20px; display:flex; gap:10px;">
                            <button type="button" class="btn btn-primary btn-sm btn-block" id="submit"><i class="fa fa-save"></i> Save Zone</button>
                            <button type="reset" class="btn btn-default btn-sm" id="reset">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-list"></i> Existing Zones (<?php echo count($res); ?>)</h3>
                </div>
                <div class="erp-table-responsive">
                    <table class="erp-table" id="addedProducts">
                        <thead>
                            <tr>
                                <th width="60">S.No</th>
                                <th>Zone Code</th>
                                <th>Zone Name</th>
                                <th width="140" style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $i = 1;
                        if (!empty($res) && count($res) > 0) {
                            foreach($res as $val) { ?>
                            <tr>
                                <td><?php echo $i;?></td>
                                <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($val['Zone_Code']);?></span></td>
                                <td><strong><?php echo htmlspecialchars($val['Zone_Name']);?></strong></td>
                                <td align="center">
                                    <button type="button" id="editbtn" class="btn btn-success btn-xs" onClick="window.location.href='edit_zone.php?id=<?php echo $val['Zone_Id'];?>'"><i class="fa fa-edit"></i> Edit</button>
                                    <button type="button" class="btn btn-danger btn-xs delete" id="<?php echo $val['Zone_Id']; ?>" name="delete"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php $i++; } 
                        } else { ?>
                            <tr>
                                <td colspan="4" align="center" style="padding:20px; color:#94a3b8;">No zones created yet.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
