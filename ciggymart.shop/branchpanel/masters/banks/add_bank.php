<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

?>
<script type="text/javascript" src="bank.js" ></script>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
       <a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-list"></i> <strong>Bank List</strong></a>
    </div>
	<h1 class="pull-left">Add New Bank</h1>
    <div class="clearfix"></div>
</div>
<div class="main">	
    <div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="insertBank" method="post">
        	<div>
            	<div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="bank_name">Bank Name <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="bank_name" name="bank_name" placeholder="Bank Name"  />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="account_name">Account Name <span>*</span>:</label>
                  <div class="col-sm-3">
                  	<input type="text" class="form-control input-sm" id="account_name" name="account_name" placeholder="Account Name" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="account_no">Account No. <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="account_no" name="account_no" placeholder="Account No."  />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="branch_address">Branch Address <span>*</span>:</label>
                  <div class="col-sm-3">
                    <textarea class="form-control input-sm" id="branch_address" name="branch_address" cols="" rows="" placeholder="Branch Address"></textarea>
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="IFSC_code">IFSC Code <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="IFSC_code" name="IFSC_code" placeholder="IFSC Code"  />
                  </div>
                </div>
                
                <hr />
                
                <div class="form-group">
                  <div class="col-sm-3"></div>
                  <div class="col-sm-3">
                  	<input type="button" class="btn btn-primary btn-xl" id="submit" value="Add">
                    <input type="reset" class="btn btn-default btn-xl" value="Reset" id="reset">
                  </div>
                </div>
            </div>
        </form>
    </div>
</div>