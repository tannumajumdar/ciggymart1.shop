<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$sql = "SELECT Client_id, Client_Code, Client_Name, C.Destination_Id, Destination_Code, Destination_Name, Address, Billing_Address, Contact_No, GST_Within_State, GSTIN_No, PAN_No, Insurance_Percent, Fuel_Surcharge, Email, Password 
FROM tbl_clients C
RIGHT JOIN tbl_destinations D ON D.Destination_Id = C.Destination_Id
WHERE Client_Id='".$_GET['id']."'";

$client=$db->ExecuteQuery($sql);

?>
<script type="text/javascript" src="client.js" ></script>

<script>
$(document).ready(function(){ 

	///////////// Get destination code and name list(autocomplete)///////////////////////  
	var destcodelist = [
		<?php // PHP begins here		  
			$menu=$db->ExecuteQuery("SELECT Destination_Code, Destination_Name FROM tbl_destinations");
			foreach($menu as $val) {
				echo '"'.$val['Destination_Code'].'-'.$val['Destination_Name']. '",';
			} // PHP ends here*/
		?>
	];// eof json format
	
	$("#dest_code").autocomplete({
   
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( destcodelist, function(item,index){
				return matcher.test(item);
			});
			responseFn( a );
		}
	}).on( 'autocompleteresponse autocompleteselect', function( e, ui ){
					
			var formdata = new FormData();
			formdata.append('type', "getDestinationName");
			formdata.append('dest_code', $("#dest_code").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "client_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $('#destinationName').html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
	});
	
});// eof ready function
</script>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
		<a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-list"></i> <strong>Client List</strong></a>
  	</div>
	<h1 class="pull-left">Edit Client</h1>
    <div class="clearfix"></div>
</div>

<div class="main">
	
    <div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="editClient" method="post">
        	<div>
            	<div class="form-group">
                	<label class="control-label col-sm-3 mandatory" for="client_name">Client Code<span>*</span>:</label>
                    <div class="col-sm-3 label-value"><input type="text" class="form-control input-sm" id="client_code" name="client_code" value="<?php echo $client[1]['Client_Code']; ?>"  /></div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="client_name">Client / Company Name<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="client_name" name="client_name" value="<?php echo $client[1]['Client_Name']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="address">Address <span>*</span>:</label>
                  <div class="col-sm-3">
                  	<input type="text" class="form-control input-sm" id="address" name="address" value="<?php echo $client[1]['Address']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="billingAdd">Billing Address <span>*</span>:</label>
                  <div class="col-sm-3">
                  	<input type="text" class="form-control input-sm" id="billingAdd" name="billingAdd" value="<?php echo $client[1]['Billing_Address']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="dest_code">City / Destination Code &amp; Name  <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="dest_code" name="dest_code" placeholder="Ex: RAI, BHI..." value="<?php echo $client[1]['Destination_Code']; ?>" />
                  </div>
                  
                  <div class="col-sm-2" id="destinationName">
                  	 <input type="hidden" id="dest_id" value="<?php echo $client[1]['Destination_Id']; ?>">
                     <input type="text" class="form-control input-sm" id="Destination_Name" name="Destination_Name" placeholder="Name" readonly="readonly" value="<?php echo $client[1]['Destination_Name']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="contact_no">Contact No <span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="contact_no" name="contact_no" value="<?php echo $client[1]['Contact_No']; ?>" />
                  </div>
                </div>
                
                <hr />
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="gstwithinstate">GST Under Within State?:</label>
                  <div class="col-sm-3">
                    <input type="checkbox" class="input-sm" id="gstwithinstate" name="gstwithinstate" <?php echo $client[1]['GST_Within_State']==1?'Checked="Checked"':''; ?> />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="gstin">GSTIN No:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="gstin" name="gstin" placeholder="GSTIN No" value="<?php echo $client[1]['GSTIN_No']; ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="panNo">PAN No:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="panNo" name="panNo" placeholder="PAN No" value="<?php echo $client[1]['PAN_No'] ?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="insurance">Insurance in (%):</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="insurance" name="insurance" value="<?php echo $client[1]['Insurance_Percent']; ?>" />
                  </div>
                </div>     
                
                 <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="fuelSurcharge">Fuel Surcharge (%)<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="fuelSurcharge" name="fuelSurcharge" value="<?php echo $client[1]['Fuel_Surcharge']; ?>" />
                  </div>
                </div>  
                
                <hr />   
                
                 <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="email">Email<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="email" name="email" value="<?php echo $client[1]['Email']; ?>" />
                  </div>
                </div>     
                
                 <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="password">Password<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="password" name="password" value="<?php echo $client[1]['Password']; ?>" />
                  </div>
                </div>     
                
                <hr />  
                
                <div class="form-group">
                  <div class="col-sm-3"></div>
                  <div class="col-sm-3">
                    <input type="hidden" id="client_id" value="<?php echo $client[1]['Client_id']; ?>">
                  	<input type="button" class="btn btn-primary btn-xl" id="edit" value="Update">
                    <input type="reset" class="btn btn-default btn-xl" value="Reset" id="reset">
                  </div>
                </div>
            </div>
        </form>
        
       
    </div>
</div>