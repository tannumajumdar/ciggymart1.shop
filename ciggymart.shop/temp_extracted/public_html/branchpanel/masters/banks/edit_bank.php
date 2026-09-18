<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// Get Operator details
$res=$db->ExecuteQuery("SELECT * FROM tbl_banks WHERE Bank_Id='".$_GET['id']."'");
?>

<script type="text/javascript"  src="bank.js" ></script>

<div class="pageTitle">
	<div class="ef_header_tools pull-right BtnLeftPadd">
        <a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-list"></i> <strong>Bank List</strong></a>
    </div>
    <div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="add_bank.php"><i class="glyphicon glyphicon-plus"></i> <strong>Add New Bank</strong></a>
    </div>
	<h1 class="pull-left">Edit Bank</h1>
    <div class="clearfix"></div>
</div>
<div class="main">
	<div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="editBank" method="post">
        	<div>
            	<div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="bank_name">Bank Name <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="bank_name" name="bank_name" placeholder="Bank Name" value="<?php echo $res[1]['Bank_Name']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="account_name">Account Name :</label>
                  <div class="col-sm-3">
                  	<input type="text" class="form-control input-sm" id="account_name" name="account_name" placeholder="Account Name" value="<?php echo $res[1]['Account_Name']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="account_no">Account No. <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="account_no" name="account_no" placeholder="Account No." value="<?php echo $res[1]['Account_No']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="branch_address">Branch Address <span>*</span>:</label>
                  <div class="col-sm-3">
                    <textarea class="form-control input-sm" id="branch_address" name="branch_address" cols="" rows="" placeholder="Branch Address"><?php echo $res[1]['Branch_Address']; ?></textarea>
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="IFSC_code">IFSC Code <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="IFSC_code" name="IFSC_code" placeholder="IFSC Code" value="<?php echo $res[1]['IFSC_Code']; ?>" />
                  </div>
                </div>
                
                <hr />
                
                <div class="form-group">
                  <div class="col-sm-3"></div>
                  <div class="col-sm-3">
                  	<input type="hidden" id="bank_id" name="bank_id" value="<?php echo $res[1]['Bank_Id']; ?>">
                  	<input type="button" class="btn btn-primary btn-xl" id="edit" value="Update">
                    <input type="reset" class="btn btn-default btn-xl" value="Reset" id="reset">
                  </div>
                </div>
            </div>
        </form>
    </div>
</div>