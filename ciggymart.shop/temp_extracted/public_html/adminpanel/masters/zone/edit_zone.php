<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get zone
$res=$db->ExecuteQuery("SELECT * FROM tbl_zones WHERE Zone_Id='".$_GET['id']."'");

?>
<script type="text/javascript"  src="zone.js" ></script>
<div class="pageTitle">
	<h1>Edit Operational Zone</h1>
</div>

<div class="main">
	<div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="editZone" method="post">
        	<div>
            	<div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="zone_code">Zone Code <span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="zone_code" name="zone_code" placeholder="Zone Code" value="<?php echo $res[1]['Zone_Code'] ?>"  />
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="zone_name">Zone Name <span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="zone_name" name="zone_name" placeholder="Zone Name" value="<?php echo $res[1]['Zone_Name'] ?>" />
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-3"></div>
                  <div class="col-sm-3">
                  	<input type="hidden" id="zone_id" name="zone_id" value="<?php echo $res[1]['Zone_Id'] ?>" />
                  	<input type="button" class="btn btn-primary btn-sm" id="edit" value="Update">
                    <input type="reset" class="btn btn-default btn-sm" value="Reset" id="reset">
                  </div>
                </div>
            </div>
        </form>
    </div>
</div>