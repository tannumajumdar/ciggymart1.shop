<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

if(isset($_POST['addExpense'])) {
    $date = $_POST['expense_date'];
    $category = $_POST['category'];
    $amount = floatval($_POST['amount']);
    $mode = $_POST['payment_mode'];
    $ref = $_POST['reference_no'];
    $desc = $_POST['description'];
    
    $db->Execute("INSERT INTO tbl_expenses (Expense_Date, Category, Amount, Payment_Mode, Reference_No, Description) VALUES ('$date', '$category', $amount, '$mode', '$ref', '$desc')");
    echo "<script>window.location.href='expenses.php';</script>";
    exit;
}

if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->Execute("DELETE FROM tbl_expenses WHERE Expense_Id = $id");
    echo "<script>window.location.href='expenses.php';</script>";
    exit;
}

$expenses = $db->ExecuteQuery("SELECT * FROM tbl_expenses ORDER BY Expense_Date DESC, Expense_Id DESC LIMIT 100");
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-pie-chart text-warning"></i> Office & Operational Expenses</h1>
        <span style="color:#64748b; font-size:13px;">Track daily office expenses, rent, fuel, and salaries.</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addExpenseModal"><i class="fa fa-plus-circle"></i> Record Expense</button>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">
    <div class="erp-card">
        <div class="erp-card-header">
            <h3 class="erp-card-title"><i class="fa fa-list"></i> Recent Expenses</h3>
        </div>
        <div class="erp-table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th width="100">Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Mode / Ref</th>
                        <th style="text-align:right;">Amount (&#8377;)</th>
                        <th width="80" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    if(!empty($expenses)) {
                        foreach($expenses as $exp) {
                            $total += $exp['Amount'];
                    ?>
                    <tr>
                        <td><?php echo date('d-m-Y', strtotime($exp['Expense_Date'])); ?></td>
                        <td><span class="badge-pill-modern badge-info"><?php echo htmlspecialchars($exp['Category']); ?></span></td>
                        <td><?php echo htmlspecialchars($exp['Description']); ?></td>
                        <td><?php echo htmlspecialchars($exp['Payment_Mode']); ?><br><small style="color:#64748b;"><?php echo htmlspecialchars($exp['Reference_No']); ?></small></td>
                        <td style="text-align:right; font-weight:bold; color:#dc2626;">&#8377; <?php echo number_format($exp['Amount'], 2); ?></td>
                        <td align="center">
                            <a href="?delete=<?php echo $exp['Expense_Id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this expense?');"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php } } else { ?>
                        <tr><td colspan="6" align="center" style="padding:24px; color:#94a3b8;">No expenses recorded yet.</td></tr>
                    <?php } ?>
                </tbody>
                <?php if(!empty($expenses)) { ?>
                <tfoot style="background: #f1f5f9; font-weight:bold;">
                    <tr>
                        <td colspan="4" align="right">TOTAL OF DISPLAYED EXPENSES:</td>
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
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius: var(--radius-md); overflow:hidden;">
      <div class="modal-header" style="background:#0f172a; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title" style="margin:0; border:none; padding:0; color:#fff;"><i class="fa fa-pie-chart"></i> Record New Expense</h4>
      </div>
      <form method="post">
          <div class="modal-body" style="padding: 20px;">
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Expense Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Amount (&#8377;) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                </div>
                <div class="form-group custom-fg">
                    <label>Expense Category <span class="text-danger">*</span></label>
                    <select class="form-control" name="category" required>
                        <option value="Fuel / Travel">Fuel / Travel</option>
                        <option value="Office Rent">Office Rent</option>
                        <option value="Staff Salary">Staff Salary</option>
                        <option value="Stationery / Printing">Stationery / Printing</option>
                        <option value="Electricity / Internet">Electricity / Internet</option>
                        <option value="Misc / Other">Misc / Other</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Payment Mode</label>
                        <select class="form-control" name="payment_mode">
                            <option value="Cash">Cash</option>
                            <option value="UPI / Online">UPI / Online</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group custom-fg">
                        <label>Reference / UTR No.</label>
                        <input type="text" class="form-control" name="reference_no">
                    </div>
                </div>
                <div class="form-group custom-fg">
                    <label>Description / Notes</label>
                    <textarea class="form-control" name="description" rows="2"></textarea>
                </div>
          </div>
          <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" name="addExpense" class="btn btn-primary"><i class="fa fa-save"></i> Save Expense</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>
