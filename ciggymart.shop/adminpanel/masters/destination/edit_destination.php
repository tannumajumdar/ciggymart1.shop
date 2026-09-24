<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$destId = intval($_GET['id']);
$res = $db->ExecuteQuery("SELECT D.*, S.State_Code, S.State_Name FROM tbl_destinations D
INNER JOIN tbl_states S ON S.State_Id = D.State_Id
WHERE D.Destination_Id=$destId");

if (empty($res)) {
    echo "<div class='container'><div class='alert alert-danger'>Destination not found.</div></div>";
    exit();
}

$states = $db->ExecuteQuery("SELECT State_Id, State_Code, State_Name FROM tbl_states ORDER BY State_Name ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-edit text-primary"></i> Edit City / Destination</h1>
        <span style="color:#64748b; font-size:13px;">Update destination details, pincode, ODA surcharge, and door delivery charge</span>
    </div>
    <div>
        <a href="index.php" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Destinations</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">
    <div class="erp-card" style="max-width: 800px; margin: 0 auto;">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-map-marker"></i> Destination Details</h3>
        </div>
        <div class="erp-card-body">
            <form class="form-horizontal" id="editDestForm">
                <input type="hidden" name="id" value="<?php echo $res[1]['Destination_Id']; ?>">
                
                <div class="row form-group">
                    <label class="col-sm-3 control-label">Destination Code <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="dest_code" value="<?php echo htmlspecialchars($res[1]['Destination_Code']); ?>" required>
                    </div>
                </div>

                <div class="row form-group">
                    <label class="col-sm-3 control-label">City / Destination Name <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="dest_name" value="<?php echo htmlspecialchars($res[1]['Destination_Name']); ?>" required>
                    </div>
                </div>

                <div class="row form-group">
                    <label class="col-sm-3 control-label">State <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control" name="state_id" required>
                            <?php foreach ($states as $st) { ?>
                                <option value="<?php echo $st['State_Id']; ?>" <?php echo ($st['State_Id'] == $res[1]['State_Id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($st['State_Name']); ?> (<?php echo htmlspecialchars($st['State_Code']); ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row form-group">
                    <label class="col-sm-3 control-label">Pincode</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="pincode" value="<?php echo htmlspecialchars(isset($res[1]['Pincode']) ? $res[1]['Pincode'] : ''); ?>" placeholder="e.g. 110001">
                    </div>
                </div>

                <div class="row form-group">
                    <label class="col-sm-3 control-label">Is ODA Area?</label>
                    <div class="col-sm-4">
                        <select class="form-control" name="is_oda">
                            <option value="0" <?php echo (!isset($res[1]['Is_ODA']) || $res[1]['Is_ODA'] == 0) ? 'selected' : ''; ?>>No (Standard)</option>
                            <option value="1" <?php echo (isset($res[1]['Is_ODA']) && $res[1]['Is_ODA'] == 1) ? 'selected' : ''; ?>>Yes (ODA Area)</option>
                        </select>
                    </div>
                </div>

                <div class="row form-group">
                    <label class="col-sm-3 control-label">ODA Charge (&#8377;)</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" class="form-control" name="oda_charge" value="<?php echo isset($res[1]['ODA_Charge']) ? floatval($res[1]['ODA_Charge']) : '0.00'; ?>">
                    </div>
                </div>

                <div class="row form-group">
                    <label class="col-sm-3 control-label">Door Delivery Charge (&#8377;)</label>
                    <div class="col-sm-4">
                        <input type="number" step="0.01" class="form-control" name="door_charge" value="<?php echo isset($res[1]['Door_Delivery_Charge']) ? floatval($res[1]['Door_Delivery_Charge']) : '0.00'; ?>">
                    </div>
                </div>

                <hr>

                <div class="row form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Destination</button>
                        <a href="index.php" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#editDestForm").submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + "&type=editdestination";
        $.ajax({
            url: "destination_curd.php",
            type: "POST",
            data: formData,
            success: function(res) {
                if (res == "1") {
                    window.location.href = "index.php";
                } else {
                    alert("Failed to update destination.");
                }
            }
        });
    });
});
</script>
