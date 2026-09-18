<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();
?>

<script type="text/javascript" src="client.js"></script>
<script>
$(document).ready(function(){ 

	///////////// Get destination code and name list(autocomplete)///////////////////////  
	var destcodelist = [
		<?php // PHP begins here		  
			$menu=$db->ExecuteQuery("SELECT Destination_Code, Destination_Name FROM tbl_destinations");
			foreach($menu as $val) {
				echo '"'.$val['Destination_Code'].'-'.$val['Destination_Name'].'",';
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
	<h1 class="pull-left">Add New Client</h1>
    <div class="clearfix"></div>
</div>

<div class="main">
	<div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="insertClient" method="post">
        	<div>
            	<div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="client_code">Client Code<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="client_code" name="client_code" placeholder="Ex: 101" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="client_name">Client / Company Name<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="client_name" name="client_name"  />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="address">Address <span>*</span>:</label>
                  <div class="col-sm-3">
                  	<input type="text" class="form-control input-sm" id="address" name="address"  />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="billingAddChckBx"></label>
                  <div class="col-sm-3">
                  	<input type="checkbox" class="pull-left checkboxVerAlign" id="billingAddChckBx" name="billingAddChckBx" />&nbsp;&nbsp;Click If Billing Address Same as Address?
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="billingAdd">Billing Address <span>*</span>:</label>
                  <div class="col-sm-3">
                  	<input type="text" class="form-control input-sm" id="billingAdd" name="billingAdd" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="dest_code">City / Destination Code &amp; Name  <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="dest_code" name="dest_code" placeholder="Ex: RAI, BHI..." />
                  </div>
                  
                  <div class="col-sm-2" id="destinationName">
                     <input type="text" class="form-control input-sm" id="Destination_Name" name="Destination_Name" placeholder="Name" readonly="readonly" value="" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="contact_no">Contact No <span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="contact_no" name="contact_no" />
                  </div>
                </div>
                
                <hr />
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="gstwithinstate">GST Under Within State?:</label>
                  <div class="col-sm-3">
                    <input type="checkbox" class="input-sm" id="gstwithinstate" name="gstwithinstate" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="gstin">GSTIN No:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="gstin" name="gstin" placeholder="GSTIN No" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="panNo">PAN No:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="panNo" name="panNo" placeholder="PAN No" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="insurance">Insurance in (%):</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="insurance" name="insurance" />
                  </div>
                </div>     
                
                 <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="fuelSurcharge">Fuel Surcharge (%)<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="fuelSurcharge" name="fuelSurcharge"  />
                  </div>
                </div>  
                
                <hr />   
                
                 <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="email">Email<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="email" name="email"  />
                  </div>
                </div>     
                
                 <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="password">Password<span>*</span>:</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control input-sm" id="password" name="password" />
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