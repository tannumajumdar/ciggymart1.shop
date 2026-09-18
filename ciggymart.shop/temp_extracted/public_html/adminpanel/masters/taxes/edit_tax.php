<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get tax
$res=$db->ExecuteQuery("SELECT * FROM tbl_applicable_taxes WHERE Tax_id='".$_GET['id']."'");
?>
<script type="text/javascript"  src="tax.js" ></script>

<div class="pageTitle">
	<h1>Edit Tax Type</h1>
</div>
<div class="main">	
    <div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="editTax" method="post">
        	<div>
            	<div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="tax_type">Tax Type <span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="tax_type" name="tax_type" placeholder="Tax Type" value="<?php echo $res[1]['tax_type'] ?>"  />
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="tax_percent">Tax Percent(%) <span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="tax_percent" name="tax_percent" placeholder="Tax Percent(%)" value="<?php echo $res[1]['tax_percent'] ?>" />
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-3"></div>
                  <div class="col-sm-3">
                  	<input type="hidden" id="tax_id" name="tax_id" value="<?php echo $res[1]['Tax_id'] ?>" />
                  	<input type="button" class="btn btn-primary btn-sm" id="edit" value="Update">
                    <input type="reset" class="btn btn-default btn-sm" id="reset" value="Reset">
                  </div>
                </div>
            </div>
        </form>
    </div>
</div>