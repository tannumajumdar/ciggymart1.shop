<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get Branch
$res=$db->ExecuteQuery("SELECT Branch_Code, Branch_Name, Franchise_Name, Franchise_Logo, Contact_Person, PAN_No, GSTIN, Service_Tax_No, B.Destination_Id, Destination_Code, Destination_Name, Address, Contact_No, Email, Password FROM tbl_branchs B
INNER JOIN tbl_destinations D ON D.Destination_Id = B.Destination_Id
WHERE Branch_Id=".$_GET['id']);

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
	<div class="pull-right BtnLeftPadd">
        <a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-list"></i> <strong>Branch List</strong></a>
    </div>
    <div class="pull-right">
        <a class="btn btn-success btn-sm" href="add_branch.php"><i class="glyphicon glyphicon-plus"></i> <strong>Add New Branch</strong></a>
    </div>
	<h1 class="pull-left">Edit Branch</h1>
    <div class="clearfix"></div>
</div>

<div class="main">
	<div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="editBranch" method="post">
      <div>
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="branch_code">Branch Code <span>*</span>:</label>
          <div class="col-sm-2">
            <input type="text" class="form-control input-sm" id="branch_code" name="branch_code" placeholder="Branch Code" value="<?php echo $res[1]['Branch_Code']; ?>"  />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="branch_name">Company/ Branch Name <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="branch_name" name="branch_name" placeholder="Company or Branch Name" value="<?php echo $res[1]['Branch_Name']; ?>" />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="franchise_name">Frenchise Name :</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="franchise_name" name="franchise_name" placeholder="Ex: GMS Worldwide Express Pvt. Ltd." value="<?php echo $res[1]['Franchise_Name']; ?>" />
          </div>
        </div> 
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="franchise_logo">Frenchise Logo :</label>
          <div class="col-sm-3">
            <input type="file" class="form-control input-sm" id="franchise_logo" name="franchise_logo" /><br/>
            <strong>Note</strong> : Image size must be less than 300kb
            
          </div>
        </div>
        <?php if(isset($res[1]['Franchise_Logo'])){ ?>
        <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-sm-offset-3">
            <img width="80" src="<?php echo PATH_UPLOAD_IMAGE.'/franchise/thumb/'.$res[1]['Franchise_Logo'];?>" alt="" />
            <input type="hidden" id="frpic" value="<?php echo $res[1]['Franchise_Logo']; ?>" />
          </div>
        </div>
        <?php } ?>
        <hr />
        
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="cont_person">Contact Person <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="cont_person" name="cont_person" placeholder="Contact Person" value="<?php echo $res[1]['Contact_Person']; ?>" />
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3" for="panNo">PAN No :</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="panNo" name="panNo" placeholder="PAN No" value="<?php echo $res[1]['PAN_No']; ?>" />
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3" for="gstNo">GSTIN No :</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="gstNo" name="gstNo" placeholder="GSTIN No" value="<?php echo $res[1]['GSTIN']; ?>" />
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3" for="serv_tax_no">Service Tax No :</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="serv_tax_no" name="serv_tax_no" placeholder="Service Tax No" value="<?php echo $res[1]['Service_Tax_No']; ?>" />
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="dest_code">City / Destination Code &amp; Name  <span>*</span>:</label>
          <div class="col-sm-2">
            <input type="text" class="form-control input-sm" id="dest_code" name="dest_code" placeholder="Ex: RAI, BHI..." value="<?php echo $res[1]['Destination_Code']; ?>" />
          </div>
          
          <div class="col-sm-2" id="destinationName">
          	 <input type='hidden' id='dest_id' name='dest_id' value="<?php echo $res[1]['Destination_Id'] ?>" />
             <input type="text" class="form-control input-sm" id="Destination_Name" name="Destination_Name" placeholder="Name" readonly="readonly" value="<?php echo $res[1]['Destination_Name']; ?>" />
          </div>
        </div>
        
        
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="address">Address <span>*</span>:</label>
          <div class="col-sm-6">
          	<textarea class="form-control input-sm" id="address" name="address" cols="" rows="" placeholder="Type Branch Address"><?php echo $res[1]['Address']; ?></textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="phone_no">Contact No <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="phone_no" name="phone_no" placeholder="Phone No" value="<?php echo $res[1]['Contact_No']; ?>" />
          </div>
        </div>
        
        <hr />
        
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="email">Email <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="text" class="form-control input-sm" id="email" name="email" placeholder="Email" value="<?php echo $res[1]['Email']; ?>" />
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-3 mandatory" for="password">Password <span>*</span>:</label>
          <div class="col-sm-3">
            <input type="password" class="form-control input-sm" id="password" name="password" placeholder="Password" value="<?php echo $res[1]['Password']; ?>" />
          </div>
        </div>
        
        <hr />
        
        <div class="form-group">
          <div class="col-sm-3"></div>
          <div class="col-sm-3">
          	<input type="hidden" id="branch_id" value="<?php echo $_GET['id'] ?>">
            <input type="button" class="btn btn-primary btn-sm" id="edit" value="Update">
            <input type="reset" class="btn btn-default btn-sm" value="Reset" id="reset">
          </div>
        </div>
      </div>
    </form>
    </div>
</div>