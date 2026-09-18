<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$branchId = isset($_SESSION['buser']) ? intval($_SESSION['buser']) : 0;
$bankList = $db->ExecuteQuery("SELECT * FROM tbl_banks WHERE Branch_Id = $branchId ORDER BY Bank_Id DESC");
?>
<script type="text/javascript" src="bank.js"></script>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-university text-primary"></i> Branch Bank Accounts</h1>
        <span style="color:#64748b; font-size:13px;">Manage branch bank accounts printed on customer GST invoices and payment receipts</span>
    </div>
    <div>
        <a class="btn btn-primary btn-sm" href="add_bank.php"><i class="fa fa-plus-circle"></i> Add New Bank</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 40px 24px;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Configured Bank Accounts (<?php echo count($bankList); ?>)</h3>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table" id="addedProducts">
                <thead>
                    <tr>
                        <th width="50">S.No</th>
                        <th>Bank Name</th>
                        <th>Account Holder / Name</th>
                        <th>Account Number</th>
                        <th>Branch / Address</th>
                        <th>IFSC Code</th>
                        <th width="140" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if (!empty($bankList) && count($bankList) > 0) {
                    $i = 1;
                    foreach ($bankList as $b) { ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><strong style="color:#0f172a;"><?php echo htmlspecialchars($b['Bank_Name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($b['Account_Name']); ?></td>
                            <td><code style="font-size:13px; font-weight:700;"><?php echo htmlspecialchars($b['Account_No']); ?></code></td>
                            <td><?php echo htmlspecialchars($b['Branch_Address']); ?></td>
                            <td><span class="badge-pill-modern badge-primary"><?php echo htmlspecialchars($b['IFSC_Code']); ?></span></td>
                            <td align="center">
                                <a href="edit_bank.php?id=<?php echo $b['Bank_Id']; ?>" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Edit</a>
                                <button type="button" class="btn btn-danger btn-xs delete" id="<?php echo $b['Bank_Id']; ?>" name="delete"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php $i++; }
                } else { ?>
                    <tr>
                        <td colspan="7" align="center" style="padding:24px; color:#94a3b8;">No bank accounts configured for this branch yet. <a href="add_bank.php">Add Bank Account</a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>