<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$branchList = $db->ExecuteQuery("SELECT B.*, D.Destination_Name FROM tbl_branchs B
LEFT JOIN tbl_destinations D ON D.Destination_Id = B.Destination_Id ORDER BY B.Branch_Id ASC");
?>
<script type="text/javascript" src="branch.js"></script>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-building text-primary"></i> Franchise & Hub Branch Network</h1>
        <span style="color:#64748b; font-size:13px;">Manage company-owned branches, franchise partners, and portal credentials</span>
    </div>
    <div>
        <a class="btn btn-primary btn-sm" href="add_branch.php"><i class="fa fa-plus-circle"></i> Add New Branch</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Registered Branches (<?php echo count($branchList); ?>)</h3>
            <div class="pull-right">
                <input type="text" id="branchSearch" class="form-control input-sm" placeholder="Search Branch..." style="width:240px; display:inline-block;">
            </div>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table" id="branchTable">
                <thead>
                    <tr>
                        <th width="50">S.No</th>
                        <th>Code</th>
                        <th>Branch Name</th>
                        <th>Franchise Entity</th>
                        <th>City</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Login Email</th>
                        <th>Status</th>
                        <th width="140" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($branchList) && count($branchList) > 0) {
                        $i = 1;
                        foreach ($branchList as $b) { ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($b['Branch_Code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($b['Branch_Name']); ?></strong></td>
                                <td><?php echo htmlspecialchars(!empty($b['Franchise_Name']) ? $b['Franchise_Name'] : 'Primary Hub'); ?></td>
                                <td><?php echo htmlspecialchars(!empty($b['Destination_Name']) ? $b['Destination_Name'] : '-'); ?></td>
                                <td><?php echo htmlspecialchars($b['Contact_Person']); ?></td>
                                <td><?php echo htmlspecialchars($b['Contact_No']); ?></td>
                                <td><?php echo htmlspecialchars($b['Email']); ?></td>
                                <td>
                                    <?php if ($b['Is_Active'] == 1) { ?>
                                        <span class="badge-pill-modern badge-success">Active</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-danger">Blocked</span>
                                    <?php } ?>
                                </td>
                                <td align="center">
                                    <a href="edit_branch.php?id=<?php echo $b['Branch_Id']; ?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i> Edit</a>
                                    <button type="button" class="btn btn-xs btn-danger delete" id="<?php echo $b['Branch_Id']; ?>" name="delete"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php $i++; }
                    } else { ?>
                        <tr>
                            <td colspan="10" align="center" style="padding:24px; color:#94a3b8;">No branches registered yet. <a href="add_branch.php">Create Branch</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#branchSearch").on("keyup", function() {
        var val = $(this).val().toLowerCase();
        $("#branchTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });
});
</script>