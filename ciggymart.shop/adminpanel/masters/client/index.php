<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$clientList = $db->ExecuteQuery("SELECT C.*, B.Branch_Name, D.Destination_Name FROM tbl_clients C LEFT JOIN tbl_branchs B ON C.Branch_Id = B.Branch_Id LEFT JOIN tbl_destinations D ON C.Destination_Id = D.Destination_Id ORDER BY C.Client_Name ASC");
$branches = $db->ExecuteQuery("SELECT Branch_Id, Branch_Code, Branch_Name FROM tbl_branchs ORDER BY Branch_Name ASC");
$destinations = $db->ExecuteQuery("SELECT Destination_Id, Destination_Name FROM tbl_destinations ORDER BY Destination_Name ASC");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-users text-primary"></i> Client / Customer Master</h1>
        <span style="color:#64748b; font-size:13px;">Manage your registered clients, their default branches, billing details, and surcharges.</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addClientModal">
            <i class="fa fa-plus-circle"></i> Add New Customer
        </button>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">

    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Registered Customers (<?php echo count($clientList); ?>)</h3>
            <div class="pull-right">
                <input type="text" id="clientSearch" class="form-control input-sm" placeholder="Search Customer..." style="width: 250px; display:inline-block;">
            </div>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table" id="clientTable">
                <thead>
                    <tr>
                        <th width="60">S.No</th>
                        <th>Code</th>
                        <th>Client / Company Name</th>
                        <th>Contact No</th>
                        <th>Email</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th width="120" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientList) && count($clientList) > 0) {
                        $i = 1;
                        foreach ($clientList as $c) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($c['Client_Code']); ?></span></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($c['Client_Name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($c['Company_Name']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($c['Contact_No']); ?></td>
                                <td><?php echo htmlspecialchars($c['Email']); ?></td>
                                <td><?php echo htmlspecialchars($c['Branch_Name']); ?></td>
                                <td>
                                    <?php if ($c['Is_Active'] == 1) { ?>
                                        <span class="badge-pill-modern badge-success">Active</span>
                                    <?php } else { ?>
                                        <span class="badge-pill-modern badge-danger">Inactive</span>
                                    <?php } ?>
                                </td>
                                <td style="text-align:center;">
                                    <button type="button" class="btn btn-xs btn-default edit-client-btn" data-id="<?php echo $c['Client_Id']; ?>"><i class="fa fa-pencil text-primary"></i> Edit</button>
                                    <button type="button" class="btn btn-xs btn-default toggle-status-btn" data-id="<?php echo $c['Client_Id']; ?>" data-status="<?php echo $c['Is_Active']; ?>"><i class="fa fa-power-off <?php echo ($c['Is_Active']==1)?'text-danger':'text-success'; ?>"></i></button>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr><td colspan="8" align="center" style="padding: 24px; color: #94a3b8;">No clients found. Add a new customer to get started.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 900px; max-width: 95%;">
        <div class="modal-content" style="border-radius: 16px; border:none; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-bottom: none; padding: 12px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; text-shadow: none; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addClientLabel" style="font-weight: 700; font-size: 18px; margin: 0;"><i class="fa fa-user-plus" style="color: #60a5fa; margin-right: 8px;"></i> Add New Customer</h4>
            </div>
            <div class="modal-body" style="padding: 16px 20px; background: #f8fafc; max-height: calc(100vh - 180px); overflow-y: auto;">
                <form id="addClientForm">
    <input type="hidden" name="type" value="addClient">
    
    <div style="background: #ffffff; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h5 style="font-weight: 700; color: #1e293b; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;"><i class="fa fa-id-card-o text-primary"></i> Primary Information</h5>
        <div class="row">
            <div class="col-sm-3 custom-fg">
                <label>Client Code <span class="text-danger">*</span></label>
                <input type="text" name="Client_Code" class="form-control" required placeholder="CUST001">
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Client Name <span class="text-danger">*</span></label>
                <input type="text" name="Client_Name" class="form-control" required placeholder="Full Name">
            </div>
            <div class="col-sm-5 custom-fg">
                <label>Company / Business Name</label>
                <input type="text" name="Company_Name" class="form-control" placeholder="Company Ltd.">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 custom-fg">
                <label>Contact Person</label>
                <input type="text" name="Contact_Person" class="form-control" placeholder="Person Name">
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Contact Number</label>
                <input type="text" name="Contact_No" class="form-control" placeholder="Phone/Mobile">
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Email Address <span class="text-danger">*</span></label>
                <input type="email" name="Email" class="form-control" required placeholder="email@example.com">
            </div>
        </div>
    </div>

    <div style="background: #ffffff; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h5 style="font-weight: 700; color: #1e293b; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;"><i class="fa fa-briefcase text-primary"></i> Billing & Configuration</h5>
        <div class="row">
            <div class="col-sm-4 custom-fg">
                <label>GSTIN No <span class="text-danger">*</span></label>
                <input type="text" name="GSTIN_No" class="form-control" required placeholder="15 Digit GSTIN">
            </div>
            <div class="col-sm-4 custom-fg">
                <label>PAN No <span class="text-danger">*</span></label>
                <input type="text" name="PAN_No" class="form-control" required placeholder="10 Digit PAN">
            </div>
            <div class="col-sm-4 custom-fg" style="display: flex; align-items: flex-end;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; width:100%; background: #f8fafc; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; height: 33px; margin:0;">
                    <input type="checkbox" name="GST_Within_State" style="margin:0;"> 
                    <span style="font-weight: 600; color: #334155; font-size: 12px; margin-top:2px;">GST Within State (CGST/SGST)</span>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 custom-fg">
                <label>Default Branch <span class="text-danger">*</span></label>
                <select name="Branch_Id" class="form-control" required>
                    <option value="">-- Select Branch --</option>
                    <?php foreach ($branches as $b) { ?>
                        <option value="<?php echo $b['Branch_Id']; ?>"><?php echo htmlspecialchars($b['Branch_Name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Default Destination</label>
                <select name="Destination_Id" class="form-control">
                    <option value="0">-- Select City --</option>
                    <?php foreach ($destinations as $d) { ?>
                        <option value="<?php echo $d['Destination_Id']; ?>"><?php echo htmlspecialchars($d['Destination_Name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Portal Login Password <span class="text-danger">*</span></label>
                <input type="password" name="Password" class="form-control" required placeholder="Min 6 characters">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 custom-fg">
                <label>Pickup / Operating Address</label>
                <textarea name="Address" class="form-control" rows="2" placeholder="Full address..."></textarea>
            </div>
            <div class="col-sm-6 custom-fg">
                <label>Registered Billing Address <span class="text-danger">*</span></label>
                <textarea name="Billing_Address" class="form-control" rows="2" required placeholder="Billing address..."></textarea>
            </div>
        </div>
    </div>

    <div style="background: #ffffff; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h5 style="font-weight: 700; color: #1e293b; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;"><i class="fa fa-percent text-primary"></i> Contract Surcharges</h5>
        <div class="row">
            <div class="col-sm-4 custom-fg">
                <label>Insurance (%)</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="Insurance_Percent" class="form-control" value="2.00">
                    <span class="input-group-addon" style="background: #f1f5f9;">%</span>
                </div>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Fuel Surcharge (%)</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="Fuel_Surcharge" class="form-control" value="0.00">
                    <span class="input-group-addon" style="background: #f1f5f9;">%</span>
                </div>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Docket Charge (,1)</label>
                <div class="input-group">
                    <span class="input-group-addon" style="background: #f1f5f9;">&#8377;</span>
                    <input type="number" step="0.01" name="Docket_Charge" class="form-control" value="0.00">
                </div>
            </div>
        </div>
    </div>
</form>
            </div>
            <div class="modal-footer" style="background:#ffffff; border-top:1px solid #e2e8f0; border-radius: 0 0 16px 16px; padding: 12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; padding: 8px 16px; font-weight: 600; color: #475569; background: #f1f5f9; border: 1px solid #e2e8f0;">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveClientBtn" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; background: #2563eb; border: none; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.3);"><i class="fa fa-save" style="margin-right: 6px;"></i> Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1" role="dialog" aria-labelledby="editClientLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 900px; max-width: 95%;">
        <div class="modal-content" style="border-radius: 16px; border:none; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-bottom: none; padding: 12px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; text-shadow: none; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editClientLabel" style="font-weight: 700; font-size: 18px; margin: 0;"><i class="fa fa-pencil" style="color: #60a5fa; margin-right: 8px;"></i> Edit Customer</h4>
            </div>
            <div class="modal-body" style="padding: 16px 20px; background: #f8fafc; max-height: calc(100vh - 180px); overflow-y: auto;">
                <form id="editClientForm">
    <input type="hidden" name="type" value="editClient">`n    <input type="hidden" name="Client_Id" id="edit_Client_Id">
    
    <div style="background: #ffffff; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h5 style="font-weight: 700; color: #1e293b; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;"><i class="fa fa-id-card-o text-primary"></i> Primary Information</h5>
        <div class="row">
            <div class="col-sm-3 custom-fg">
                <label>Client Code <span class="text-danger">*</span></label>
                <input type="text" name="Client_Code" id="edit_Client_Code" class="form-control" required>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Client Name <span class="text-danger">*</span></label>
                <input type="text" name="Client_Name" id="edit_Client_Name" class="form-control" required>
            </div>
            <div class="col-sm-5 custom-fg">
                <label>Company / Business Name</label>
                <input type="text" name="Company_Name" id="edit_Company_Name" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 custom-fg">
                <label>Contact Person</label>
                <input type="text" name="Contact_Person" id="edit_Contact_Person" class="form-control">
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Contact Number</label>
                <input type="text" name="Contact_No" id="edit_Contact_No" class="form-control">
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Email Address <span class="text-danger">*</span></label>
                <input type="email" name="Email" id="edit_Email" class="form-control" required>
            </div>
        </div>
    </div>

    <div style="background: #ffffff; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h5 style="font-weight: 700; color: #1e293b; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;"><i class="fa fa-briefcase text-primary"></i> Billing & Configuration</h5>
        <div class="row">
            <div class="col-sm-4 custom-fg">
                <label>GSTIN No <span class="text-danger">*</span></label>
                <input type="text" name="GSTIN_No" id="edit_GSTIN_No" class="form-control" required>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>PAN No <span class="text-danger">*</span></label>
                <input type="text" name="PAN_No" id="edit_PAN_No" class="form-control" required>
            </div>
            <div class="col-sm-4 custom-fg" style="display: flex; align-items: flex-end;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; width:100%; background: #f8fafc; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; height: 33px; margin:0;">
                    <input type="checkbox" name="GST_Within_State" id="edit_GST_Within_State" style="margin:0;"> 
                    <span style="font-weight: 600; color: #334155; font-size: 12px; margin-top:2px;">GST Within State (CGST/SGST)</span>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 custom-fg">
                <label>Default Branch <span class="text-danger">*</span></label>
                <select name="Branch_Id" id="edit_Branch_Id" class="form-control" required>
                    <option value="">-- Select Branch --</option>
                    <?php foreach ($branches as $b) { ?>
                        <option value="<?php echo $b['Branch_Id']; ?>"><?php echo htmlspecialchars($b['Branch_Name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Default Destination</label>
                <select name="Destination_Id" id="edit_Destination_Id" class="form-control">
                    <option value="0">-- Select City --</option>
                    <?php foreach ($destinations as $d) { ?>
                        <option value="<?php echo $d['Destination_Id']; ?>"><?php echo htmlspecialchars($d['Destination_Name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Portal Login Password <span class="text-danger">*</span></label>
                <input type="text" name="Password" id="edit_Password" class="form-control" required>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 custom-fg">
                <label>Pickup / Operating Address</label>
                <textarea name="Address" id="edit_Address" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-sm-6 custom-fg">
                <label>Registered Billing Address <span class="text-danger">*</span></label>
                <textarea name="Billing_Address" id="edit_Billing_Address" class="form-control" rows="2" required></textarea>
            </div>
        </div>
    </div>

    <div style="background: #ffffff; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h5 style="font-weight: 700; color: #1e293b; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;"><i class="fa fa-percent text-primary"></i> Contract Surcharges</h5>
        <div class="row">
            <div class="col-sm-4 custom-fg">
                <label>Insurance (%)</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="Insurance_Percent" id="edit_Insurance_Percent" class="form-control">
                    <span class="input-group-addon" style="background: #f1f5f9;">%</span>
                </div>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Fuel Surcharge (%)</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="Fuel_Surcharge" id="edit_Fuel_Surcharge" class="form-control">
                    <span class="input-group-addon" style="background: #f1f5f9;">%</span>
                </div>
            </div>
            <div class="col-sm-4 custom-fg">
                <label>Docket Charge (,1)</label>
                <div class="input-group">
                    <span class="input-group-addon" style="background: #f1f5f9;">&#8377;</span>
                    <input type="number" step="0.01" name="Docket_Charge" id="edit_Docket_Charge" class="form-control">
                </div>
            </div>
        </div>
    </div>
</form>
            </div>
            <div class="modal-footer" style="background:#ffffff; border-top:1px solid #e2e8f0; border-radius: 0 0 16px 16px; padding: 12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; padding: 8px 16px; font-weight: 600; color: #475569; background: #f1f5f9; border: 1px solid #e2e8f0;">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateClientBtn" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; background: #2563eb; border: none; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.3);"><i class="fa fa-save" style="margin-right: 6px;"></i> Update Customer</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for the beautified form */
.custom-fg {
    margin-bottom: 16px;
}
.custom-fg label {
    font-weight: 600;
    color: #475569;
    font-size: 12px;
    margin-bottom: 6px;
    display: block;
}
.custom-fg .form-control {
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    padding: 8px 12px;
    font-size: 13px;
    height: auto;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.2s;
}
.custom-fg .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    outline: none;
}
.custom-fg .input-group-addon {
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}
.custom-fg .input-group .form-control:first-child {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
.custom-fg .input-group .form-control:last-child {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}
.modal-backdrop.in {
    opacity: 0.5;
    background-color: #0f172a;
}
</style>
<script>
$(document).ready(function() {
    
    // Search functionality
    $("#clientSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#clientTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Save Client
    $("#saveClientBtn").click(function() {
        if (!$("#addClientForm")[0].checkValidity()) {
            $("#addClientForm")[0].reportValidity();
            return;
        }
        $.ajax({
            url: 'client_curd.php',
            type: 'POST',
            data: $("#addClientForm").serialize(),
            success: function(response) {
                if (response.trim() === "true") {
                    location.reload();
                } else {
                    alert("Error: " + response);
                }
            }
        });
    });

    // Edit Client (Populate Modal)
    $(".edit-client-btn").click(function() {
        var id = $(this).data("id");
        $.ajax({
            url: 'client_curd.php',
            type: 'POST',
            data: { type: 'getClient', client_id: id },
            dataType: 'json',
            success: function(data) {
                if(data.error) {
                    alert(data.error);
                    return;
                }
                $("#edit_Client_Id").val(data.Client_Id);
                $("#edit_Client_Code").val(data.Client_Code);
                $("#edit_Client_Name").val(data.Client_Name);
                $("#edit_Company_Name").val(data.Company_Name);
                $("#edit_Contact_Person").val(data.Contact_Person);
                $("#edit_Contact_No").val(data.Contact_No);
                $("#edit_Email").val(data.Email);
                $("#edit_GSTIN_No").val(data.GSTIN_No);
                $("#edit_PAN_No").val(data.PAN_No);
                $("#edit_Password").val(data.Password);
                $("#edit_Branch_Id").val(data.Branch_Id);
                $("#edit_Destination_Id").val(data.Destination_Id);
                
                if (data.GST_Within_State == 1) {
                    $("#edit_GST_Within_State").prop("checked", true);
                } else {
                    $("#edit_GST_Within_State").prop("checked", false);
                }
                
                $("#edit_Address").val(data.Address);
                $("#edit_Billing_Address").val(data.Billing_Address);
                
                $("#edit_Insurance_Percent").val(data.Insurance_Percent);
                $("#edit_Fuel_Surcharge").val(data.Fuel_Surcharge);
                $("#edit_Pickup_Charge").val(data.Pickup_Charge);
                $("#edit_Docket_Charge").val(data.Docket_Charge);
                $("#edit_Door_Delivery_Charge").val(data.Door_Delivery_Charge);
                
                $("#editClientModal").modal("show");
            }
        });
    });

    // Update Client
    $("#updateClientBtn").click(function() {
        if (!$("#editClientForm")[0].checkValidity()) {
            $("#editClientForm")[0].reportValidity();
            return;
        }
        $.ajax({
            url: 'client_curd.php',
            type: 'POST',
            data: $("#editClientForm").serialize(),
            success: function(response) {
                if (response.trim() === "true") {
                    location.reload();
                } else {
                    alert("Error: " + response);
                }
            }
        });
    });

    // Toggle Status
    $(".toggle-status-btn").click(function() {
        var id = $(this).data("id");
        var status = $(this).data("status");
        if(confirm("Are you sure you want to change this customer's status?")) {
            $.ajax({
                url: 'client_curd.php',
                type: 'POST',
                data: { type: 'toggleStatus', client_id: id, status: status },
                success: function(response) {
                    if (response.trim() === "true") {
                        location.reload();
                    } else {
                        alert("Error: " + response);
                    }
                }
            });
        }
    });

});
</script>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>




