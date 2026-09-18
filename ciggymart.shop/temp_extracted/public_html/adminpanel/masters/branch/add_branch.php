<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

?>
<script type="text/javascript"  src="branch.js" ></script>

<script>
$(document).ready(function(){ 
	///////////// Get destination code and name list(autocomplete)///////////////////////  
	var destcodelist = [
		<?php // PHP begins here		  
			$menu=$db->ExecuteQuery("SELECT Destination_Code FROM tbl_destinations");
			foreach($menu as $val) {
				echo '"'.$val['Destination_Code']. '",';
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
			   url: "branch_curd.php",
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
  		<a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-list"></i> <strong>Branch List</strong></a>
	</div>
	<h1 class="pull-left">Add New Branch</h1>
    <div class="clearfix"></div>
</div>
<div class="main">  
  <div class="clear formbgstyle">
    <form class="form-horizontal" role="form" id="insertBranch" method="post">
      <div>
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="branch_code">Branch Code <span>*</span>:</label>
          <div class="col-sm-2">
            <input type="text" class="form-control input-sm" id="branch_code" name="branch_code" placeholder="Branch Code"  />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="branch_name">Company/ Branch Name <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="branch_name" name="branch_name" placeholder="Company or Branch Name" />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="franchise_name">Frenchise Name :</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="franchise_name" name="franchise_name" placeholder="Ex: GMS Worldwide Express Pvt. Ltd." />
          </div>
        </div> 
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="franchise_logo">Frenchise Logo :</label>
          <div class="col-sm-3">
            <input type="file" class="form-control input-sm" id="franchise_logo" name="franchise_logo" /><br/>
            <strong>Note</strong> : Image size must be less than 300kb            
          </div>
        </div>        
        
        <hr />
        
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="cont_person">Contact Person <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="cont_person" name="cont_person" placeholder="Contact Person"  />
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3" for="panNo">PAN No :</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="panNo" name="panNo" placeholder="PAN No"  />
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3" for="gstNo">GSTIN No :</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="gstNo" name="gstNo" placeholder="GSTIN No"  />
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3" for="serv_tax_no">Service Tax No :</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="serv_tax_no" name="serv_tax_no" placeholder="Service Tax No"  />
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
          <label class="control-label col-sm-3 mandatory" for="address">Address <span>*</span>:</label>
          <div class="col-sm-6">
          	<textarea class="form-control input-sm" id="address" name="address" cols="" rows="" placeholder="Type Branch Address"></textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="phone_no">Contact No <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="phone_no" name="phone_no" placeholder="Phone No"  />
          </div>
        </div>
        
        <hr />
        
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="email">Email <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="email" name="email" placeholder="Email"  />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="password">Password <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="password" class="form-control input-sm" id="password" name="password" placeholder="Password"  />
          </div>
        </div>
        
        <hr />
        
        <div class="form-group">
          <div class="col-sm-3"></div>
          <div class="col-sm-3">
            <input type="button" class="btn btn-primary btn-sm" id="submit" value="Submit">
            <input type="reset" class="btn btn-default btn-sm" value="Reset" id="reset">
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
