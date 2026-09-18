<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get state info
$res=$db->ExecuteQuery("SELECT S.*, Z.Zone_Code, Z.Zone_Name FROM tbl_states S
INNER JOIN tbl_zones Z ON Z.Zone_Id = S.Zone_Id
WHERE State_Id='".$_GET['id']."'");

?>
<script type="text/javascript"  src="state.js" ></script>
<script>
$(document).ready(function(){ 
	///////////// Get Zone List(autocomplete)///////////////////////  
	var zonelist  = [
		<?php // PHP begins here
		  
			$menu=$db->ExecuteQuery("SELECT Zone_Code FROM tbl_zones ");
			foreach($menu as $val) {
				echo '"'.$val['Zone_Code']. '",';
			} // PHP ends here*/
		?>
	];// eof json format
	
	$("#zone_code").autocomplete({
   
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( zonelist, function(item,index){
				return matcher.test(item);
			});
			responseFn( a );
		}
	}).on( 'autocompleteresponse autocompleteselect', function( e, ui ){
					
					//alert("hello");
					
			var formdata = new FormData();
			formdata.append('type', "getZone");
			formdata.append('zone_code', $("#zone_code").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "state_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $('#zonename').html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
	});
	
});// eof ready function
</script>
<div class="pageTitle">
	<h1>Edit State</h1>
</div>

<div class="main">	
    <div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="editState" method="post">
        	<div>
            	<div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="state_code">State Code <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="state_code" name="state_code" placeholder="State Code" value="<?php echo $res[1]['State_Code']; ?>" />
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="state_name">State Name <span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="state_name" name="state_name" placeholder="State Name" value="<?php echo $res[1]['State_Name']; ?>" />
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="zone_code">Zone Code <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="zone_code" name="zone_code" placeholder="Zone Code" value="<?php echo $res[1]['Zone_Code']; ?>" />
                    
                  </div>
                  
                  <div class="col-sm-2" id="zonename">
                  	 <input type="hidden" class="form-control input-sm" id="state_id" name="state_id" value="<?php echo $res[1]['State_Id']; ?>" />
                  	 <input type="text" class="form-control input-sm" id="zone_name" name="zone_name" placeholder="Zone Name" readonly="readonly" value="<?php echo $res[1]['Zone_Name']; ?>" />
                  </div>
                </div>
                
                
                <div class="form-group">
                  <div class="col-sm-3"></div>
                  <div class="col-sm-3">
                  	<input type="hidden" class="form-control input-sm" id="state_id" name="state_id" value="<?php echo $res[1]['State_Id']; ?>" />
                    <input type="hidden" class="form-control input-sm" id="zone_id" name="zone_id" value="<?php echo $res[1]['Zone_Id']; ?>" />
                  	<input type="button" class="btn btn-primary btn-sm" id="edit" value="Update">
                    <input type="reset" class="btn btn-default btn-sm" value="Reset" id="reset">
                  </div>
                </div>
            </div>
        </form>
    </div>
</div>