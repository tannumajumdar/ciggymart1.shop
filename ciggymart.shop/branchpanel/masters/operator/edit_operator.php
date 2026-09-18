<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// Get Operator details
$res=$db->ExecuteQuery("SELECT O.*, S.State_Id, S.State_Code, S.State_Name FROM tbl_operators O
INNER JOIN tbl_states S ON S.State_Id = O.State_Id
WHERE Operator_Id='".$_GET['id']."'");
?>

<script type="text/javascript"  src="operator.js" ></script>

<script>
$(document).ready(function(){ 
	///////////// Get state code and name list(autocomplete)///////////////////////  
	var statecodelist = [
		<?php // PHP begins here
		  
			$menu=$db->ExecuteQuery("SELECT State_Code FROM tbl_states");
			foreach($menu as $val) {
				echo '"'.$val['State_Code']. '",';
			} // PHP ends here*/
		?>
	];// eof json format
	
	$("#state_code").autocomplete({
   
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( statecodelist, function(item,index){
				return matcher.test(item);
			});
			responseFn( a );
		}
	}).on( 'autocompleteresponse autocompleteselect', function( e, ui ){
					
			var formdata = new FormData();
			formdata.append('type', "getStateName");
			formdata.append('state_code', $("#state_code").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "operator_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $('#statename').html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
	});
	
});// eof ready function
</script>

<div class="pageTitle">
	<div class="ef_header_tools pull-right BtnLeftPadd">
        <a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-list"></i> <strong>Operator List</strong></a>
    </div>
    <div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="add_operator.php"><i class="glyphicon glyphicon-plus"></i> <strong>Add New Operator</strong></a>
    </div>
	<h1 class="pull-left">Edit Operator</h1>
    <div class="clearfix"></div>
</div>
<div class="main">
	<div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="editOperator" method="post">
        	<div>
            	<div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="operator_name">Operator Name <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="operator_name" name="operator_name" placeholder="Operator Name" value="<?php echo $res[1]['Operator_Name']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="o_address">Address :</label>
                  <div class="col-sm-3">
                    <textarea class="form-control input-sm" id="o_address" name="o_address" cols="" rows="" placeholder="Type Operator Address"><?php echo $res[1]['Address']; ?></textarea>
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="state_code">State Code <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="state_code" name="state_code" placeholder="Ex: CG, MH..." value="<?php echo $res[1]['State_Code']; ?>" />
                  </div>
                  
                  <div class="col-sm-2" id="statename">
                  	 <input type="hidden" id="state_id" name="state_id" value="<?php echo $res[1]['State_Id']; ?>" />
                  	 <input type="text" class="form-control input-sm" id="state_name" name="state_name" placeholder="State Name" readonly="readonly" value="<?php echo $res[1]['State_Name']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="operator_contact">Contact No. <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="operator_contact" name="operator_contact" placeholder="Operator Contact No." value="<?php echo $res[1]['Contact_No']; ?>" />
                  </div>
                </div>
                
                <hr />
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="email">Email <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="email" name="email" placeholder="Operator Email Id" value="<?php echo $res[1]['Email']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="password">Password <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="password" name="password" placeholder="Password" value="<?php echo $res[1]['Password']; ?>"  />
                  </div>
                </div>                
                
                <hr />
                
                <div class="form-group">
                  <div class="col-sm-3"></div>
                  <div class="col-sm-3">
                  	<input type="hidden" id="operator_id" name="operator_id" value="<?php echo $res[1]['Operator_Id']; ?>" />
                  	<input type="button" class="btn btn-primary btn-sm" id="edit" value="Update">
                    <input type="reset" class="btn btn-default btn-sm" value="Reset" id="reset">
                  </div>
                </div>
            </div>
        </form>
    </div>
</div>