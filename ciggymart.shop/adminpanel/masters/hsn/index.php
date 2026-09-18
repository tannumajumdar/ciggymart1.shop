<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();
$hsnList = $db->ExecuteQuery("SELECT * FROM tbl_hsn_master ORDER BY HSN_Id ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-barcode text-primary"></i> HSN / SAC & GST Master</h1>
        <span style="color:#64748b; font-size:13px;">Manage Harmonized System of Nomenclature (HSN) and Service Accounting Codes (SAC) with GST tax rates</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addHsnModal">
            <i class="fa fa-plus-circle"></i> Add New HSN Code
        </button>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Configured HSN & GST Codes (<?php echo count($hsnList); ?>)</h3>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th width="60">S.No</th>
                        <th>HSN / SAC Code</th>
                        <th>Service / Commodity Description</th>
                        <th style="text-align:right;">Total GST (%)</th>
                        <th style="text-align:right;">CGST (%)</th>
                        <th style="text-align:right;">SGST (%)</th>
                        <th style="text-align:right;">IGST (%)</th>
                        <th>Status</th>
                        <th width="100" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($hsnList) && count($hsnList) > 0) {
                        $i = 1;
                        foreach ($hsnList as $row) { ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><strong><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($row['HSN_Code']); ?></span></strong></td>
                                <td><?php echo htmlspecialchars($row['Description']); ?></td>
                                <td style="text-align:right; font-weight:700; color:#2563eb;"><?php echo $row['GST_Percent']; ?>%</td>
                                <td style="text-align:right;"><?php echo $row['CGST_Percent']; ?>%</td>
                                <td style="text-align:right;"><?php echo $row['SGST_Percent']; ?>%</td>
                                <td style="text-align:right;"><?php echo $row['IGST_Percent']; ?>%</td>
                                <td>
                                    <?php if ($row['Is_Active'] == 1) { ?>
                                        <span class="badge-pill-modern badge-success">Active</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-danger">Inactive</span>
                                    <?php } ?>
                                </td>
                                <td align="center">
                                    <button type="button" class="btn btn-xs btn-danger delete-hsn" data-id="<?php echo $row['HSN_Id']; ?>"><i class="fa fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php $i++; }
                    } else { ?>
                        <tr>
                            <td colspan="9" align="center" style="padding:24px; color:#94a3b8;">No HSN codes found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal to Add HSN Code -->
<div class="modal fade" id="addHsnModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius: var(--radius-md); overflow:hidden;">
      <div class="modal-header" style="background:#0f172a; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title" style="margin:0; border:none; padding:0; color:#fff;"><i class="fa fa-barcode"></i> Add HSN / SAC Code</h4>
      </div>
      <form id="addHsnForm">
          <div class="modal-body" style="padding: 20px;">
                <div class="form-group custom-fg">
                    <label>HSN / SAC Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="hsn_code" id="hsn_code" required placeholder="e.g. 996812">
                </div>
                <div class="form-group custom-fg">
                    <label>Description of Goods / Services <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="description" id="hsn_desc" required placeholder="e.g. Courier & Express Services">
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Total GST Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="gst_percent" id="gst_percent" value="18.00" required>
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>IGST Rate (%) (Inter-State)</label>
                        <input type="number" step="0.01" class="form-control" name="igst_percent" id="igst_percent" value="18.00">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>CGST Rate (%) (Central)</label>
                        <input type="number" step="0.01" class="form-control" name="cgst_percent" id="cgst_percent" value="9.00">
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>SGST Rate (%) (State)</label>
                        <input type="number" step="0.01" class="form-control" name="sgst_percent" id="sgst_percent" value="9.00">
                    </div>
                </div>
          </div>
          <div class="modal-footer" style="background:#f8fafc;">
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save HSN Code</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Auto-calculate CGST & SGST on GST change
    $("#gst_percent").on("input", function() {
        var total = parseFloat($(this).val()) || 0;
        $("#igst_percent").val(total.toFixed(2));
        var half = (total / 2).toFixed(2);
        $("#cgst_percent").val(half);
        $("#sgst_percent").val(half);
    });

    // Add HSN Ajax
    $("#addHsnForm").submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + "&type=addHSN";
        $.ajax({
            url: "hsn_curd.php",
            type: "POST",
            data: formData,
            success: function(response) {
                if (response == "1") {
                    location.reload();
                } else {
                    alert("Error saving HSN Code: " + response);
                }
            }
        });
    });

    // Delete HSN
    $(".delete-hsn").click(function() {
        if (confirm("Are you sure you want to delete this HSN Code?")) {
            var id = $(this).data("id");
            $.ajax({
                url: "hsn_curd.php",
                type: "POST",
                data: { type: "deleteHSN", id: id },
                success: function(response) {
                    location.reload();
                }
            });
        }
    });
});
</script>

