<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

if(isset($_POST['addPayment'])) {
    $date = $_POST['payment_date'];
    $payee = $_POST['payee_name'];
    $category = $_POST['category'];
    $amount = floatval($_POST['amount']);
    $mode = $_POST['payment_mode'];
    $ref = $_POST['reference_no'];
    $desc = $_POST['notes'];
    
    $db->Execute("INSERT INTO tbl_payments_made (Payment_Date, Payee_Name, Category, Amount, Payment_Mode, Reference_No, Notes) VALUES ('$date', '$payee', '$category', $amount, '$mode', '$ref', '$desc')");
    echo "<script>window.location.href='payments_made.php';</script>";
    exit;
}

if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->Execute("DELETE FROM tbl_payments_made WHERE Payment_Id = $id");
    echo "<script>window.location.href='payments_made.php';</script>";
    exit;
}

$payments = $db->ExecuteQuery("SELECT * FROM tbl_payments_made ORDER BY Payment_Date DESC, Payment_Id DESC LIMIT 100");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-credit-card text-danger"></i> Payments Made (Outbound)</h1>
        <span style="color:#64748b; font-size:13px;">Track payments made to vendors, logistics partners, and utility companies.</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPaymentModal"><i class="fa fa-plus-circle"></i> Record Payment</button>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Recent Payments Dispatched</h3>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th width="100">Date</th>
                        <th>Payee / Vendor Name</th>
                        <th>Category</th>
                        <th>Mode / Ref</th>
                        <th style="text-align:right;">Amount (&#8377;)</th>
                        <th width="80" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    if(!empty($payments)) {
                        foreach($payments as $pay) {
                            $total += $pay['Amount'];
                    ?>
                    <tr>
                        <td><?php echo date('d-m-Y', strtotime($pay['Payment_Date'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($pay['Payee_Name']); ?></strong><br><small style="color:#64748b;"><?php echo htmlspecialchars($pay['Notes']); ?></small></td>
                        <td><span class="badge-pill-modern badge-info"><?php echo htmlspecialchars($pay['Category']); ?></span></td>
                        <td><?php echo htmlspecialchars($pay['Payment_Mode']); ?><br><small style="color:#64748b;"><?php echo htmlspecialchars($pay['Reference_No']); ?></small></td>
                        <td style="text-align:right; font-weight:bold; color:#dc2626;">&#8377; <?php echo number_format($pay['Amount'], 2); ?></td>
                        <td align="center">
                            <a href="?delete=<?php echo $pay['Payment_Id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this payment record?');"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php } } else { ?>
                        <tr><td colspan="6" align="center" style="padding:24px; color:#94a3b8;">No outbound payments recorded yet.</td></tr>
                    <?php } ?>
                </tbody>
                <?php if(!empty($payments)) { ?>
                <tfoot style="background: #f1f5f9; font-weight:bold;">
                    <tr>
                        <td colspan="4" align="right">TOTAL DISBURSED:</td>
                        <td style="text-align:right; color:#dc2626;">&#8377; <?php echo number_format($total, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius: var(--radius-md); overflow:hidden;">
      <div class="modal-header" style="background:#0f172a; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title" style="margin:0; border:none; padding:0; color:#fff;"><i class="fa fa-credit-card"></i> Record Outbound Payment</h4>
      </div>
      <form method="post">
          <div class="modal-body" style="padding: 20px;">
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Amount (&#8377;) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                </div>
                <div class="form-group custom-fg">
                    <label>Payee / Vendor Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="payee_name" required placeholder="e.g. DTDC Logistics, Landlord, Internet Provider">
                </div>
                <div class="form-group custom-fg">
                    <label>Payment Category <span class="text-danger">*</span></label>
                    <select class="form-control" name="category" required>
                        <option value="Vendor / Partner Settlement">Vendor / Partner Settlement</option>
                        <option value="Refund to Client">Refund to Client</option>
                        <option value="Asset Purchase">Asset Purchase</option>
                        <option value="Tax Payment">Tax Payment</option>
                        <option value="Other Disbursal">Other Disbursal</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Payment Mode</label>
                        <select class="form-control" name="payment_mode">
                            <option value="Bank Transfer (NEFT/RTGS)">Bank Transfer (NEFT/RTGS)</option>
                            <option value="Cheque">Cheque</option>
                            <option value="UPI / Online">UPI / Online</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Reference / UTR No.</label>
                        <input type="text" class="form-control" name="reference_no">
                    </div>
                </div>
                <div class="form-group custom-fg">
                    <label>Description / Notes</label>
                    <textarea class="form-control" name="notes" rows="2"></textarea>
                </div>
          </div>
          <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" name="addPayment" class="btn btn-primary"><i class="fa fa-save"></i> Save Payment Record</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>
